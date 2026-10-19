<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Laporan Stok</title>
    @include('pdf._style')
</head>

<body>
    @include('pdf._header', [
        'title' => 'Laporan Stok Bahan Baku',
        'subtitle' =>
            'Stok per ' .
            now()->translatedFormat('d M Y H:i') .
            ' | Mutasi periode ' .
            \Carbon\Carbon::parse($report['period']['from'])->translatedFormat('d M Y') .
            ' s/d ' .
            \Carbon\Carbon::parse($report['period']['to'])->translatedFormat('d M Y'),
    ])

    <h2>Ringkasan</h2>
    <table>
        <tr>
            <td>Jumlah bahan</td>
            <td class="r">{{ $report['summary']['ingredients'] }}</td>
        </tr>
        <tr>
            <td>Bahan stok menipis</td>
            <td class="r {{ $report['summary']['low'] ? 'red' : '' }}">{{ $report['summary']['low'] }}</td>
        </tr>
        <tr class="total">
            <td>Nilai persediaan saat ini</td>
            <td class="r">@rupiah($report['summary']['stock_value'])</td>
        </tr>
    </table>

    <h2>Rincian</h2>
    <table>
        <tr>
            <th>Bahan</th>
            <th class="r">Stok</th>
            <th class="r">Min</th>
            <th class="r">Nilai</th>
            <th class="r">Masuk</th>
            <th class="r">Keluar</th>
            <th class="r">Terjual</th>
            <th class="r">Kembali</th>
            <th class="r">Opname</th>
        </tr>
        @foreach ($report['rows'] as $r)
            <tr>
                <td>{{ $r['name'] }}@if ($r['low'])
                        <span class="red">(menipis)</span>
                    @endif
                </td>
                <td class="r {{ $r['low'] ? 'red' : '' }}">@qty($r['stock']) {{ $r['unit'] }}</td>
                <td class="r">@qty($r['min_stock'])</td>
                <td class="r">@rupiah($r['value'])</td>
                <td class="r">@qty($r['in'])</td>
                <td class="r">@qty($r['out'])</td>
                <td class="r">@qty($r['sold'])</td>
                <td class="r">{{ $r['opname'] > 0 ? '+' : '' }}@qty($r['opname'])</td>
            </tr>
        @endforeach
    </table>

    @include('pdf._footer')
</body>

</html>
