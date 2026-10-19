<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PurchaseExport implements WithMultipleSheets
{
    public function __construct(private array $report)
    {
    }

    public function sheets(): array
    {
        $r = $this->report;

        return [
            new ArraySheet('Ringkasan', ['Keterangan', 'Nilai'], [
                ['Periode', $r['period']['from'].' s/d '.$r['period']['to']],
                ['Status', $r['filters']['status']],
                ['Supplier', $r['filters']['supplier'] ?? 'Semua'],
                ['Tanggal acuan', $r['filters']['basis']],
                ['Jumlah PO (tanpa dibatalkan)', $r['summary']['count']],
                ['Total pembelian (Rp)', $r['summary']['total']],
            ]),
            new ArraySheet('Daftar PO', ['No. PO', 'Supplier', 'Status', 'Dibuat', 'Dipesan', 'Diterima', 'Item', 'Total (Rp)'],
                array_map(fn ($o) => [$o['po_no'], $o['supplier'], $o['status'], $o['created_at'], $o['ordered_at'], $o['received_at'], $o['items'], $o['total']], $r['orders'])),
            new ArraySheet('Per Supplier', ['Supplier', 'Jumlah PO', 'Total (Rp)'],
                array_map(fn ($s) => [$s['name'], $s['orders'], $s['total']], $r['by_supplier'])),
            new ArraySheet('Per Bahan', ['Bahan', 'Satuan', 'Jumlah', 'Total (Rp)'],
                array_map(fn ($i) => [$i['name'], $i['unit'], $i['quantity'], $i['total']], $r['by_ingredient'])),
        ];
    }
}
