<?php

namespace App\Http\Controllers;

use App\Enums\PurchaseOrderStatus;
use App\Exports\PurchaseExport;
use App\Exports\SalesExport;
use App\Exports\StockExport;
use App\Http\Requests\Reports\ReportFilterRequest;
use App\Models\Setting;
use App\Models\Supplier;
use App\Services\ActivityLogger;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Dipakai bersama oleh route /superadmin/reports dan /admin/reports.
 *  - Superadmin: data lengkap (per kasir, pengeluaran, laba kotor) + export PDF/Excel.
 *  - Admin: hanya tampilan, tanpa data pegawai dan laba kotor.
 */
class ReportController extends Controller
{
    private const FILE_LABELS = [
        'sales' => 'penjualan',
        'purchases' => 'pembelian-bahan',
        'stock' => 'stok',
    ];

    public function __construct(private ReportService $reports)
    {
    }

    public function sales(ReportFilterRequest $request): View
    {
        $owner = $request->user()->isSuperadmin();

        return view('reports.sales', $this->base($request, 'sales') + [
            'report' => $this->reports->salesReport($request->from(), $request->to(), $owner),
        ]);
    }

    public function purchases(ReportFilterRequest $request): View
    {
        return view('reports.purchases', $this->base($request, 'purchases') + [
            'report' => $this->reports->purchaseReport($request->from(), $request->to(), $request->status(), $request->supplierId()),
            'suppliers' => Supplier::orderBy('name')->pluck('name', 'id'),
            'statuses' => ['all' => 'Semua status'] + PurchaseOrderStatus::options(),
            'selectedStatus' => $request->status() ?? PurchaseOrderStatus::Received->value,
            'selectedSupplier' => $request->supplierId(),
        ]);
    }

    public function stock(ReportFilterRequest $request): View
    {
        return view('reports.stock', $this->base($request, 'stock') + [
            'report' => $this->reports->stockReport($request->from(), $request->to()),
        ]);
    }

    /** Hanya Superadmin (gate export-reports pada route). */
    public function export(ReportFilterRequest $request, string $type, string $format): Response|BinaryFileResponse
    {
        $from = $request->from();
        $to = $request->to();

        $report = match ($type) {
            'sales' => $this->reports->salesReport($from, $to, true),
            'purchases' => $this->reports->purchaseReport($from, $to, $request->status(), $request->supplierId()),
            'stock' => $this->reports->stockReport($from, $to),
        };

        ActivityLogger::log('report.exported', null, [
            'type' => $type,
            'format' => $format,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ]);

        $filename = sprintf('laporan-%s-%s_%s.%s', self::FILE_LABELS[$type], $from->format('Ymd'), $to->format('Ymd'), $format);

        if ($format === 'xlsx') {
            $export = match ($type) {
                'sales' => new SalesExport($report),
                'purchases' => new PurchaseExport($report),
                'stock' => new StockExport($report),
            };

            return Excel::download($export, $filename);
        }

        return Pdf::loadView('pdf.'.$type, [
            'report' => $report,
            'cafeName' => Setting::get('cafe_name', 'Cafe'),
            'generatedAt' => now()->translatedFormat('d M Y H:i'),
            'generatedBy' => $request->user()->name,
        ])->setPaper('a4', 'portrait')->download($filename);
    }

    private function base(ReportFilterRequest $request, string $type): array
    {
        $owner = $request->user()->isSuperadmin();

        return [
            'prefix' => $owner ? 'superadmin.reports' : 'admin.reports',
            'isOwner' => $owner,
            'type' => $type,
            'from' => $request->from()->toDateString(),
            'to' => $request->to()->toDateString(),
        ];
    }
}
