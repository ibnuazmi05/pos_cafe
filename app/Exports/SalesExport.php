<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SalesExport implements WithMultipleSheets
{
    public function __construct(private array $report) {}

    public function sheets(): array
    {
        $r = $this->report;
        $s = $r['summary'];

        return [
            new ArraySheet('Ringkasan', ['Keterangan', 'Nilai'], [
                ['Periode', $r['period']['from'] . ' s/d ' . $r['period']['to']],
                ['Jumlah transaksi', $s['transactions']],
                ['Omzet bersih (Rp)', $s['net_sales']],
                ['Diskon (Rp)', $s['discount']],
                ['Pajak (Rp)', $s['tax']],
                ['Total diterima termasuk pajak (Rp)', $s['gross_total']],
                ['Rata-rata per transaksi (Rp)', $s['average']],
                ['Pengeluaran bahan (Rp)', $s['purchases']],
                ['Laba kotor (Rp)', $s['gross_profit']],
            ]),
            new ArraySheet(
                'Harian',
                ['Tanggal', 'Transaksi', 'Omzet bersih (Rp)', 'Diskon (Rp)', 'Pajak (Rp)', 'Total (Rp)'],
                array_map(fn($d) => [$d['date'], $d['transactions'], $d['net_sales'], $d['discount'], $d['tax'], $d['gross_total']], $r['daily'])
            ),
            new ArraySheet(
                'Per Metode Bayar',
                ['Metode', 'Transaksi', 'Total (Rp)'],
                array_map(fn($m) => [$m['label'], $m['transactions'], $m['total']], $r['by_method'])
            ),
            new ArraySheet(
                'Per Kasir',
                ['Kasir', 'Transaksi', 'Omzet bersih (Rp)', 'Total (Rp)'],
                array_map(fn($c) => [$c['name'], $c['transactions'], $c['net_sales'], $c['gross_total']], $r['by_cashier'])
            ),
            new ArraySheet(
                'Produk Terlaris',
                ['Menu', 'Terjual', 'Pendapatan (Rp)'],
                array_map(fn($p) => [$p['name'], $p['qty'], $p['revenue']], $r['top_products'])
            ),
        ];
    }
}
