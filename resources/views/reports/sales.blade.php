<x-app-layout>
    @php $s = $report['summary']; @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">
            @include('reports._tabs')
            <x-report-filters :action="route($prefix . '.sales')" :from="$from" :to="$to" type="sales" />

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white border border-stone-200/90 rounded-2xl p-5 shadow-xs">
                    <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Omzet Bersih</p>
                    <p class="text-2xl font-bold text-stone-900 mt-1">@rupiah($s['net_sales'])</p>
                    <p class="text-xs text-stone-400 mt-1">Sebelum pajak</p>
                </div>
                <div class="bg-white border border-stone-200/90 rounded-2xl p-5 shadow-xs">
                    <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Jumlah Transaksi</p>
                    <p class="text-2xl font-bold text-stone-900 mt-1">{{ $s['transactions'] }}</p>
                    <p class="text-xs text-stone-400 mt-1">Rata-rata @rupiah($s['average'])</p>
                </div>
                <div class="bg-white border border-stone-200/90 rounded-2xl p-5 shadow-xs">
                    <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Diskon Diberikan</p>
                    <p class="text-2xl font-bold text-stone-900 mt-1">@rupiah($s['discount'])</p>
                </div>
                <div class="bg-white border border-stone-200/90 rounded-2xl p-5 shadow-xs">
                    <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Pajak Dipungut</p>
                    <p class="text-2xl font-bold text-stone-900 mt-1">@rupiah($s['tax'])</p>
                    <p class="text-xs text-stone-400 mt-1">Total diterima @rupiah($s['gross_total'])</p>
                </div>
                @if ($isOwner)
                    <div class="bg-white border border-stone-200/90 rounded-2xl p-5 shadow-xs">
                        <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Pengeluaran Bahan</p>
                        <p class="text-2xl font-bold text-stone-900 mt-1">@rupiah($s['purchases'])</p>
                        <p class="text-xs text-stone-400 mt-1">PO diterima</p>
                    </div>
                    <div class="bg-white border border-stone-200/90 rounded-2xl p-5 shadow-xs">
                        <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Laba Kotor</p>
                        <p
                            class="text-2xl font-bold mt-1 {{ $s['gross_profit'] < 0 ? 'text-rose-600' : 'text-emerald-700' }}">
                            @rupiah($s['gross_profit'])
                        </p>
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                <div class="bg-white border border-stone-200/90 rounded-2xl p-5 shadow-xs">
                    <h3 class="font-bold text-base text-stone-900 pb-3 border-b border-stone-100">Per Metode Pembayaran
                    </h3>
                    <table class="mt-2 min-w-full text-sm">
                        <thead
                            class="text-left text-xs uppercase text-stone-400 font-semibold border-b border-stone-100">
                            <tr>
                                <th class="py-2.5">Metode</th>
                                <th class="py-2.5 text-right">Transaksi</th>
                                <th class="py-2.5 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @foreach ($report['by_method'] as $m)
                                <tr class="hover:bg-stone-50/60 transition">
                                    <td class="py-2.5 font-medium text-stone-800">{{ $m['label'] }}</td>
                                    <td class="py-2.5 text-right text-stone-600">{{ $m['transactions'] }}</td>
                                    <td class="py-2.5 text-right font-semibold text-stone-900">@rupiah($m['total'])</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="bg-white border border-stone-200/90 rounded-2xl p-5 shadow-xs">
                    <h3 class="font-bold text-base text-stone-900 pb-3 border-b border-stone-100">Menu Terlaris</h3>
                    <table class="mt-2 min-w-full text-sm">
                        <thead
                            class="text-left text-xs uppercase text-stone-400 font-semibold border-b border-stone-100">
                            <tr>
                                <th class="py-2.5">Menu</th>
                                <th class="py-2.5 text-right">Terjual</th>
                                <th class="py-2.5 text-right">Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @forelse ($report['top_products'] as $p)
                                <tr class="hover:bg-stone-50/60 transition">
                                    <td class="py-2.5 font-medium text-stone-800">{{ $p['name'] }}</td>
                                    <td class="py-2.5 text-right text-stone-600 font-semibold">{{ $p['qty'] }}</td>
                                    <td class="py-2.5 text-right font-bold text-amber-800">@rupiah($p['revenue'])</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-6 text-center text-stone-400">Belum ada penjualan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($isOwner)
                <div class="bg-white border border-stone-200/90 rounded-2xl p-5 shadow-xs">
                    <h3 class="font-bold text-base text-stone-900 pb-3 border-b border-stone-100">Penjualan Per Kasir
                    </h3>
                    <table class="mt-2 min-w-full text-sm">
                        <thead
                            class="text-left text-xs uppercase text-stone-400 font-semibold border-b border-stone-100">
                            <tr>
                                <th class="py-2.5">Kasir</th>
                                <th class="py-2.5 text-right">Transaksi</th>
                                <th class="py-2.5 text-right">Omzet Bersih</th>
                                <th class="py-2.5 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @forelse ($report['by_cashier'] as $c)
                                <tr class="hover:bg-stone-50/60 transition">
                                    <td class="py-2.5 font-medium text-stone-800">{{ $c['name'] }}</td>
                                    <td class="py-2.5 text-right text-stone-600">{{ $c['transactions'] }}</td>
                                    <td class="py-2.5 text-right font-semibold text-stone-800">@rupiah($c['net_sales'])</td>
                                    <td class="py-2.5 text-right font-bold text-stone-900">@rupiah($c['gross_total'])</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-stone-400">Belum ada penjualan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="bg-white border border-stone-200/90 rounded-2xl p-5 shadow-xs overflow-x-auto">
                <h3 class="font-bold text-base text-stone-900 pb-3 border-b border-stone-100">Rincian Harian</h3>
                <table class="mt-2 min-w-full text-sm">
                    <thead
                        class="bg-stone-50 text-left text-xs uppercase text-stone-400 font-semibold border-b border-stone-100">
                        <tr>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3 text-right">Transaksi</th>
                            <th class="px-4 py-3 text-right">Omzet Bersih</th>
                            <th class="px-4 py-3 text-right">Diskon</th>
                            <th class="px-4 py-3 text-right">Pajak</th>
                            <th class="px-4 py-3 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse ($report['daily'] as $d)
                            <tr class="hover:bg-stone-50/60 transition">
                                <td class="px-4 py-3 font-medium text-stone-800">
                                    {{ \Carbon\Carbon::parse($d['date'])->translatedFormat('D, d M Y') }}</td>
                                <td class="px-4 py-3 text-right text-stone-600">{{ $d['transactions'] }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-stone-800">@rupiah($d['net_sales'])</td>
                                <td class="px-4 py-3 text-right text-rose-600">@rupiah($d['discount'])</td>
                                <td class="px-4 py-3 text-right text-stone-600">@rupiah($d['tax'])</td>
                                <td class="px-4 py-3 text-right font-bold text-stone-900">@rupiah($d['gross_total'])</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-stone-400">Tidak ada penjualan pada
                                    periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
