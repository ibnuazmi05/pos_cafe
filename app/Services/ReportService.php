<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Enums\PurchaseOrderStatus;
use App\Models\Ingredient;
use App\Models\PurchaseOrder;
use App\Models\Shift;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;


class ReportService
{
    /** @return array{transactions:int, net_sales:float, discount:float, tax:float, gross_total:float} */
    public function sales(CarbonInterface $from, CarbonInterface $to): array
    {
        $row = Transaction::query()
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('COUNT(*) as transactions,
                COALESCE(SUM(subtotal - discount), 0) as net_sales,
                COALESCE(SUM(discount), 0) as discount,
                COALESCE(SUM(tax), 0) as tax,
                COALESCE(SUM(total), 0) as gross_total')
            ->first();

        return [
            'transactions' => (int) $row->transactions,
            'net_sales' => (float) $row->net_sales,
            'discount' => (float) $row->discount,
            'tax' => (float) $row->tax,
            'gross_total' => (float) $row->gross_total,
        ];
    }

    public function purchases(CarbonInterface $from, CarbonInterface $to): float
    {
        return (float) PurchaseOrder::query()
            ->where('status', PurchaseOrderStatus::Received->value)
            ->whereBetween('received_at', [$from->toDateString(), $to->toDateString()])
            ->sum('total');
    }

    public function summary(CarbonInterface $from, CarbonInterface $to): array
    {
        $sales = $this->sales($from, $to);
        $purchases = $this->purchases($from, $to);

        return $sales + [
            'purchases' => $purchases,
            'gross_profit' => $sales['net_sales'] - $purchases,
        ];
    }

    /** Produk terlaris berdasarkan jumlah terjual. */
    public function topProducts(CarbonInterface $from, CarbonInterface $to, int $limit = 5): Collection
    {
        return TransactionItem::query()
            ->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->whereNull('transactions.deleted_at')
            ->whereBetween('transactions.created_at', [$from, $to])
            ->groupBy('transaction_items.product_id')
            ->selectRaw('transaction_items.product_id,
                MAX(transaction_items.product_name) as name,
                SUM(transaction_items.quantity) as qty,
                SUM(transaction_items.subtotal) as revenue')
            ->orderByDesc('qty')
            ->limit($limit)
            ->get();
    }

    /** Omzet bersih per hari untuk $days hari terakhir (termasuk hari tanpa transaksi). */
    public function dailySales(int $days = 7): Collection
    {
        $end = now()->endOfDay();
        $start = now()->subDays($days - 1)->startOfDay();

        $rows = Transaction::query()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as day, SUM(subtotal - discount) as net')
            ->groupBy('day')
            ->pluck('net', 'day');

        return collect(range(0, $days - 1))->map(function (int $i) use ($start, $rows) {
            $date = $start->copy()->addDays($i);

            return [
                'date' => $date,
                'net' => (float) ($rows[$date->toDateString()] ?? 0),
            ];
        });
    }

    public function dashboard(): array
    {
        $now = now();

        $periods = [
            'today' => ['label' => 'Hari ini', 'from' => $now->copy()->startOfDay(), 'to' => $now->copy()->endOfDay()],
            'week' => ['label' => 'Minggu ini', 'from' => $now->copy()->startOfWeek(), 'to' => $now->copy()->endOfWeek()],
            'month' => ['label' => 'Bulan ini', 'from' => $now->copy()->startOfMonth(), 'to' => $now->copy()->endOfMonth()],
        ];

        $summaries = [];
        foreach ($periods as $key => $p) {
            $summaries[$key] = ['label' => $p['label']] + $this->summary($p['from'], $p['to']);
        }

        return [
            'periods' => $summaries,
            'top_products' => $this->topProducts($periods['month']['from'], $periods['month']['to']),
            'daily' => $this->dailySales(7),
            'flagged' => Transaction::where('verification_status', 'flagged')->count(),
            'pending_verification' => Transaction::where('verification_status', 'pending')->count(),
            'open_shifts' => Shift::open()->count(),
            'low_stock' => Ingredient::lowStock()->count(),
        ];
    }

    // ------------------------------------------------------------------
    //  Laporan (dipakai layar, PDF, dan Excel agar angkanya selalu sama)
    // ------------------------------------------------------------------

    /**
     * @param  bool  $owner  true: sertakan data per kasir, pengeluaran bahan, dan laba kotor (hanya Superadmin)
     */
    public function salesReport(CarbonInterface $from, CarbonInterface $to, bool $owner = false): array
    {
        $sales = $this->sales($from, $to);

        $summary = $sales + [
            'average' => $sales['transactions'] > 0 ? round($sales['net_sales'] / $sales['transactions'], 2) : 0.0,
            'purchases' => null,
            'gross_profit' => null,
        ];

        if ($owner) {
            $purchases = $this->purchases($from, $to);
            $summary['purchases'] = $purchases;
            $summary['gross_profit'] = round($sales['net_sales'] - $purchases, 2);
        }

        $completed = fn() => Transaction::query()
            ->whereBetween('created_at', [$from, $to]);

        $daily = $completed()
            ->selectRaw('DATE(created_at) as day, COUNT(*) as transactions,
                SUM(subtotal - discount) as net_sales, SUM(discount) as discount,
                SUM(tax) as tax, SUM(total) as gross_total')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->map(fn($r) => [
                'date' => (string) $r->day,
                'transactions' => (int) $r->transactions,
                'net_sales' => (float) $r->net_sales,
                'discount' => (float) $r->discount,
                'tax' => (float) $r->tax,
                'gross_total' => (float) $r->gross_total,
            ])->all();

        $byMethodRows = $completed()
            ->selectRaw('payment_method, COUNT(*) as transactions, SUM(total) as total')
            ->groupBy('payment_method')
            ->get();

        $byMethod = [];
        foreach (PaymentMethod::cases() as $method) {
            $row = $byMethodRows->first(fn($r) => $r->payment_method === $method);
            $byMethod[] = [
                'label' => $method->label(),
                'transactions' => (int) ($row->transactions ?? 0),
                'total' => (float) ($row->total ?? 0),
            ];
        }

        $byCashier = [];
        if ($owner) {
            $rows = $completed()
                ->selectRaw('cashier_id, COUNT(*) as transactions, SUM(subtotal - discount) as net_sales, SUM(total) as gross_total')
                ->groupBy('cashier_id')
                ->orderByDesc('net_sales')
                ->get();

            $names = User::withTrashed()->whereIn('id', $rows->pluck('cashier_id'))->pluck('name', 'id');

            $byCashier = $rows->map(fn($r) => [
                'name' => $names[$r->cashier_id] ?? '-',
                'transactions' => (int) $r->transactions,
                'net_sales' => (float) $r->net_sales,
                'gross_total' => (float) $r->gross_total,
            ])->all();
        }

        $top = $this->topProducts($from, $to, 10)->map(fn($r) => [
            'name' => $r->name,
            'qty' => (int) $r->qty,
            'revenue' => (float) $r->revenue,
        ])->all();

        return [
            'period' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
            'summary' => $summary,
            'daily' => $daily,
            'by_method' => $byMethod,
            'by_cashier' => $byCashier,
            'top_products' => $top,
        ];
    }

    /**
     * Tanggal acuan: status "received" memakai tanggal diterima; status lain / "all" memakai tanggal dibuat.
     * Total tidak menghitung PO yang dibatalkan.
     */
    public function purchaseReport(CarbonInterface $from, CarbonInterface $to, ?string $status = null, ?int $supplierId = null): array
    {
        $status ??= PurchaseOrderStatus::Received->value;
        $byReceived = $status === PurchaseOrderStatus::Received->value;

        $orders = PurchaseOrder::query()
            ->with(['supplier', 'items.ingredient'])
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->when($supplierId, fn($q, $v) => $q->where('supplier_id', $v))
            ->when(
                $byReceived,
                fn($q) => $q->whereBetween('received_at', [$from->toDateString(), $to->toDateString()]),
                fn($q) => $q->whereBetween('created_at', [$from, $to]),
            )
            ->orderBy($byReceived ? 'received_at' : 'created_at')
            ->orderBy('id')
            ->get();

        $active = $orders->reject(fn($o) => $o->status === PurchaseOrderStatus::Cancelled);

        $rows = $orders->map(fn($o) => [
            'id' => $o->id,
            'po_no' => $o->po_no,
            'supplier' => $o->supplier->name,
            'status' => $o->status->label(),
            'created_at' => $o->created_at->format('Y-m-d'),
            'ordered_at' => $o->ordered_at?->format('Y-m-d'),
            'received_at' => $o->received_at?->format('Y-m-d'),
            'items' => $o->items->count(),
            'total' => (float) $o->total,
        ])->all();

        $bySupplier = $active->groupBy('supplier_id')->map(fn($g) => [
            'name' => $g->first()->supplier->name,
            'orders' => $g->count(),
            'total' => (float) $g->sum('total'),
        ])->sortByDesc('total')->values()->all();

        $byIngredient = $active->flatMap(fn($o) => $o->items)->groupBy('ingredient_id')->map(fn($g) => [
            'name' => $g->first()->ingredient->name,
            'unit' => $g->first()->ingredient->unit,
            'quantity' => (float) $g->sum('quantity'),
            'total' => (float) $g->sum('subtotal'),
        ])->sortByDesc('total')->values()->all();

        return [
            'period' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
            'filters' => [
                'status' => $status,
                'supplier' => $supplierId ? Supplier::whereKey($supplierId)->value('name') : null,
                'basis' => $byReceived ? 'tanggal diterima' : 'tanggal dibuat',
            ],
            'summary' => ['count' => $active->count(), 'total' => round((float) $active->sum('total'), 2)],
            'orders' => $rows,
            'by_supplier' => $bySupplier,
            'by_ingredient' => $byIngredient,
        ];
    }

    /** Stok adalah kondisi SAAT INI; kolom mutasi mengikuti periode yang dipilih. */
    public function stockReport(CarbonInterface $from, CarbonInterface $to): array
    {
        $moves = StockMovement::query()
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('ingredient_id, type, SUM(quantity) as qty')
            ->groupBy('ingredient_id', 'type')
            ->get()
            ->groupBy('ingredient_id');

        $rows = [];
        $value = 0.0;
        $low = 0;

        foreach (Ingredient::orderBy('name')->get() as $ingredient) {
            $m = $moves->get($ingredient->id, collect())
                ->mapWithKeys(fn($r) => [$r->type->value => (float) $r->qty]);

            $isLow = (float) $ingredient->stock <= (float) $ingredient->min_stock;
            $rowValue = round((float) $ingredient->stock * (float) $ingredient->last_cost, 2);

            $value += $rowValue;
            $low += $isLow ? 1 : 0;

            $rows[] = [
                'name' => $ingredient->name,
                'unit' => $ingredient->unit,
                'stock' => (float) $ingredient->stock,
                'min_stock' => (float) $ingredient->min_stock,
                'low' => $isLow,
                'last_cost' => (float) $ingredient->last_cost,
                'value' => $rowValue,
                'in' => abs($m['in'] ?? 0),
                'out' => abs($m['out'] ?? 0),
                'sold' => abs($m['sale'] ?? 0),
                'void_return' => abs($m['void_return'] ?? 0),
                'opname' => $m['opname'] ?? 0.0,
            ];
        }

        return [
            'period' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
            'summary' => ['ingredients' => count($rows), 'low' => $low, 'stock_value' => round($value, 2)],
            'rows' => $rows,
        ];
    }
}
