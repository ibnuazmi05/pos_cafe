<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan</title>
    @include('pdf._style')
</head>

<body>
    @php $s = $report['summary']; @endphp
    @include('pdf._header', [
        'title' => 'Laporan Penjualan',
        'subtitle' =>
            'Periode ' .
            \Carbon\Carbon::parse($report['period']['from'])->translatedFormat('d M Y') .
            ' s/d ' .
            \Carbon\Carbon::parse($report['period']['to'])->translatedFormat('d M Y'),
    ])

    <h2>Ringkasan</h2>
    <table>
        <tr>
            <td>Jumlah transaksi</td>
            <td class="r">{{ $s['transactions'] }}</td>
        </tr>
        <tr>
            <td>Omzet bersih (sebelum pajak)</td>
            <td class="r">@rupiah($s['net_sales'])</td>
        </tr>
        <tr>
            <td>Diskon diberikan</td>
            <td class="r">@rupiah($s['discount'])</td>
        </tr>
        <tr>
            <td>Pajak dipungut</td>
            <td class="r">@rupiah($s['tax'])</td>
        </tr>
        <tr>
            <td>Total diterima (termasuk pajak)</td>
            <td class="r">@rupiah($s['gross_total'])</td>
        </tr>
        <tr>
            <td>Rata-rata per transaksi</td>
            <td class="r">@rupiah($s['average'])</td>
        </tr>
        <tr>
            <td>Pengeluaran bahan (PO diterima)</td>
            <td class="r">@rupiah($s['purchases'])</td>
        </tr>
        <tr class="total">
            <td>Laba kotor</td>
            <td class="r {{ $s['gross_profit'] < 0 ? 'red' : 'green' }}">@rupiah($s['gross_profit'])</td>
        </tr>
    </table>

    <h2>Per metode pembayaran</h2>
    <table>
        <tr>
            <th>Metode</th>
            <th class="r">Transaksi</th>
            <th class="r">Total</th>
        </tr>
        @foreach ($report['by_method'] as $m)
            <tr>
                <td>{{ $m['label'] }}</td>
                <td class="r">{{ $m['transactions'] }}</td>
                <td class="r">@rupiah($m['total'])</td>
            </tr>
        @endforeach
    </table>

    <h2>Per kasir</h2>
    <table>
        <tr>
            <th>Kasir</th>
            <th class="r">Transaksi</th>
            <th class="r">Omzet bersih</th>
            <th class="r">Total</th>
        </tr>
        @forelse ($report['by_cashier'] as $c)
            <tr>
                <td>{{ $c['name'] }}</td>
                <td class="r">{{ $c['transactions'] }}</td>
                <td class="r">@rupiah($c['net_sales'])</td>
                <td class="r">@rupiah($c['gross_total'])</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="muted">Tidak ada data.</td>
            </tr>
        @endforelse
    </table>

    <h2>Menu terlaris</h2>
    <table>
        <tr>
            <th>Menu</th>
            <th class="r">Terjual</th>
            <th class="r">Pendapatan</th>
        </tr>
        @forelse ($report['top_products'] as $p)
            <tr>
                <td>{{ $p['name'] }}</td>
                <td class="r">{{ $p['qty'] }}</td>
                <td class="r">@rupiah($p['revenue'])</td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="muted">Tidak ada data.</td>
            </tr>
        @endforelse
    </table>

    <h2>Rincian harian</h2>
    <table>
        <tr>
            <th>Tanggal</th>
            <th class="r">Trx</th>
            <th class="r">Omzet bersih</th>
            <th class="r">Diskon</th>
            <th class="r">Pajak</th>
            <th class="r">Total</th>
        </tr>
        @forelse ($report['daily'] as $d)
            <tr>
                <td>{{ \Carbon\Carbon::parse($d['date'])->translatedFormat('d M Y') }}</td>
                <td class="r">{{ $d['transactions'] }}</td>
                <td class="r">@rupiah($d['net_sales'])</td>
                <td class="r">@rupiah($d['discount'])</td>
                <td class="r">@rupiah($d['tax'])</td>
                <td class="r">@rupiah($d['gross_total'])</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="muted">Tidak ada penjualan pada periode ini.</td>
            </tr>
        @endforelse
    </table>

    @include('pdf._footer')
</body>

</html>
