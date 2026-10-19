<?php

namespace App\Services;

use App\Enums\PurchaseOrderStatus;
use App\Enums\StockMovementType;
use App\Models\Ingredient;
use App\Models\PurchaseOrder;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

/** Alur status: draft -> ordered -> received | cancelled (draft/ordered bisa dibatalkan). */
class PurchaseOrderService
{
    public function __construct(private StockService $stock)
    {
    }

    /** @param array{supplier_id:int, note?:?string, items:array<int, array>} $data */
    public function create(array $data, User $creator): PurchaseOrder
    {
        return DB::transaction(function () use ($data, $creator) {
            $po = PurchaseOrder::create([
                'po_no' => $this->nextNumber(),
                'supplier_id' => $data['supplier_id'],
                'created_by' => $creator->id,
                'status' => PurchaseOrderStatus::Draft,
                'note' => $data['note'] ?? null,
                'total' => 0,
            ]);

            $this->syncItems($po, $data['items']);

            ActivityLogger::log('purchase_order.created', $po, ['po_no' => $po->po_no, 'total' => $po->total], $creator);

            return $po;
        });
    }

    public function update(PurchaseOrder $po, array $data, User $user): PurchaseOrder
    {
        return DB::transaction(function () use ($po, $data, $user) {
            $locked = $this->lock($po, [PurchaseOrderStatus::Draft], 'Hanya PO berstatus draft yang dapat diubah.');

            $locked->update([
                'supplier_id' => $data['supplier_id'],
                'note' => $data['note'] ?? null,
            ]);
            $this->syncItems($locked, $data['items']);

            ActivityLogger::log('purchase_order.updated', $locked, ['po_no' => $locked->po_no, 'total' => $locked->total], $user);

            return $locked;
        });
    }

    public function delete(PurchaseOrder $po, User $user): void
    {
        DB::transaction(function () use ($po, $user) {
            $locked = $this->lock($po, [PurchaseOrderStatus::Draft], 'Hanya PO berstatus draft yang dapat dihapus.');

            $locked->items()->delete();
            $locked->delete();

            ActivityLogger::log('purchase_order.deleted', $locked, ['po_no' => $locked->po_no], $user);
        });
    }

    public function markOrdered(PurchaseOrder $po, User $user): PurchaseOrder
    {
        return DB::transaction(function () use ($po, $user) {
            $locked = $this->lock($po, [PurchaseOrderStatus::Draft], 'Hanya PO berstatus draft yang dapat dipesan.');

            if (! $locked->items()->exists()) {
                throw new DomainException('PO tidak memiliki item.');
            }

            $locked->update(['status' => PurchaseOrderStatus::Ordered, 'ordered_at' => now()->toDateString()]);

            ActivityLogger::log('purchase_order.ordered', $locked, ['po_no' => $locked->po_no], $user);

            return $locked;
        });
    }

    /** Terima barang: stok bahan bertambah + harga beli terakhir diperbarui. */
    public function receive(PurchaseOrder $po, User $user, ?string $receivedAt = null): PurchaseOrder
    {
        return DB::transaction(function () use ($po, $user, $receivedAt) {
            $locked = $this->lock($po, [PurchaseOrderStatus::Ordered], 'Hanya PO berstatus "Dipesan" yang dapat diterima.');

            $locked->load('items');

            foreach ($locked->items->sortBy('ingredient_id') as $item) {
                $this->stock->adjust(
                    $item->ingredient_id,
                    StockMovementType::In,
                    (float) $item->quantity,
                    $user,
                    $locked,
                    "Penerimaan {$locked->po_no}",
                );

                Ingredient::whereKey($item->ingredient_id)->update(['last_cost' => $item->unit_price]);
            }

            $locked->update([
                'status' => PurchaseOrderStatus::Received,
                'received_at' => $receivedAt ?? now()->toDateString(),
                'received_by' => $user->id,
            ]);

            ActivityLogger::log('purchase_order.received', $locked, [
                'po_no' => $locked->po_no, 'total' => $locked->total, 'items' => $locked->items->count(),
            ], $user);

            return $locked;
        });
    }

    public function cancel(PurchaseOrder $po, User $user): PurchaseOrder
    {
        return DB::transaction(function () use ($po, $user) {
            $locked = $this->lock(
                $po,
                [PurchaseOrderStatus::Draft, PurchaseOrderStatus::Ordered],
                'PO ini tidak dapat dibatalkan.'
            );

            $locked->update(['status' => PurchaseOrderStatus::Cancelled]);

            ActivityLogger::log('purchase_order.cancelled', $locked, ['po_no' => $locked->po_no], $user);

            return $locked;
        });
    }

    /** @param array<int, PurchaseOrderStatus> $allowed */
    private function lock(PurchaseOrder $po, array $allowed, string $message): PurchaseOrder
    {
        $locked = PurchaseOrder::whereKey($po->getKey())->lockForUpdate()->firstOrFail();

        if (! in_array($locked->status, $allowed, true)) {
            throw new DomainException($message);
        }

        return $locked;
    }

    private function syncItems(PurchaseOrder $po, array $items): void
    {
        $po->items()->delete();

        $total = 0.0;

        foreach ($items as $row) {
            $quantity = round((float) $row['quantity'], 3);
            $price = round((float) $row['unit_price'], 2);
            $subtotal = round($quantity * $price, 2);

            $po->items()->create([
                'ingredient_id' => $row['ingredient_id'],
                'quantity' => $quantity,
                'unit_price' => $price,
                'subtotal' => $subtotal,
            ]);

            $total += $subtotal;
        }

        $po->update(['total' => round($total, 2)]);
    }

    /** Format PO-YYYYMMDD-0001. Dipanggil di dalam transaksi; kolom po_no unik sebagai pengaman akhir. */
    private function nextNumber(): string
    {
        $prefix = 'PO-'.now()->format('Ymd').'-';

        $last = PurchaseOrder::withTrashed()
            ->where('po_no', 'like', $prefix.'%')
            ->orderByDesc('po_no')
            ->lockForUpdate()
            ->value('po_no');

        $next = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
