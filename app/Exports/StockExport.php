<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class StockExport implements WithMultipleSheets
{
    public function __construct(private array $report) {}

    public function sheets(): array
    {
        $r = $this->report;

        return [
            new ArraySheet('Ringkasan', ['Keterangan', 'Nilai'], [
                ['Periode mutasi', $r['period']['from'] . ' s/d ' . $r['period']['to']],
                ['Jumlah bahan', $r['summary']['ingredients']],
                ['Bahan stok menipis', $r['summary']['low']],
                ['Nilai persediaan saat ini (Rp)', $r['summary']['stock_value']],
            ]),
            new ArraySheet('Stok', [
                'Bahan',
                'Satuan',
                'Stok saat ini',
                'Stok minimum',
                'Status',
                'Harga beli terakhir (Rp)',
                'Nilai (Rp)',
                'Masuk',
                'Keluar',
                'Terjual',
                'Selisih opname',
            ], array_map(fn($x) => [
                $x['name'],
                $x['unit'],
                $x['stock'],
                $x['min_stock'],
                $x['low'] ? 'Menipis' : 'Aman',
                $x['last_cost'],
                $x['value'],
                $x['in'],
                $x['out'],
                $x['sold'],
                $x['opname'],
            ], $r['rows'])),
        ];
    }
}
