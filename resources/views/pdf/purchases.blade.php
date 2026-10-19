<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Pembelian Bahan</title>
    @include('pdf._style')
</head>
<body>
    @include('pdf._header', [
        'title' => 'Laporan Pembelian Bahan Baku',
        'subtitle' => 'Periode '.\Carbon\Carbon::parse($report['period']['from'])->translatedFormat('d M Y').' s/d '.\Carbon\Carbon::parse($report['period']['to'])->translatedFormat('d M Y')
            .' | Status: '.$report['filters']['status'].' | Supplier: '.($report['filters']['supplier'] ?? 'Semua')
            .' | Acuan: '.$report['filters']['basis'],
    ])

    <h2>Ringkasan</h2>
    <table>
        <tr><td>Jumlah PO (tanpa yang dibatalkan)</td><td class="r">{{ $report['summary']['count'] }}</td></tr>
        <tr class="total"><td>Total pembelian</td><td class="r">@rupiah($report['summary']['total'])</td></tr>
    </table>

    <h2>Daftar PO</h2>
    <table>
        <tr><th>No. PO</th><th>Supplier</th><th>Status</th><th>Dibuat</th><th>Diterima</th><th class="r">Item</th><th class="r">Total</th></tr>
        @forelse ($report['orders'] as $o)
            <tr>
                <td>{{ $o['po_no'] }}</td><td>{{ $o['supplier'] }}</td><td>{{ $o['status'] }}</td>
                <td>{{ $o['created_at'] }}</td><td>{{ $o['received_at'] ?? '-' }}</td>
                <td class="r">{{ $o['items'] }}</td><td class="r">@rupiah($o['total'])</td>
            </tr>
        @empty
            <tr><td colspan="7" class="muted">Tidak ada PO pada periode ini.</td></tr>
        @endforelse
    </table>

    <h2>Per supplier</h2>
    <table>
        <tr><th>Supplier</th><th class="r">PO</th><th class="r">Total</th></tr>
        @forelse ($report['by_supplier'] as $x)
            <tr><td>{{ $x['name'] }}</td><td class="r">{{ $x['orders'] }}</td><td class="r">@rupiah($x['total'])</td></tr>
        @empty
            <tr><td colspan="3" class="muted">-</td></tr>
        @endforelse
    </table>

    <h2>Per bahan</h2>
    <table>
        <tr><th>Bahan</th><th class="r">Jumlah</th><th class="r">Total</th></tr>
        @forelse ($report['by_ingredient'] as $x)
            <tr><td>{{ $x['name'] }}</td><td class="r">@qty($x['quantity']) {{ $x['unit'] }}</td><td class="r">@rupiah($x['total'])</td></tr>
        @empty
            <tr><td colspan="3" class="muted">-</td></tr>
        @endforelse
    </table>

    @include('pdf._footer')
</body>
</html>
