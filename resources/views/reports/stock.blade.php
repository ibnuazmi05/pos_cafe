<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">
            @include('reports._tabs')
            <x-report-filters :action="route($prefix . '.stock')" :from="$from" :to="$to" type="stock" />

            <p class="text-xs text-stone-500">Kolom <strong>stok</strong> dan <strong>nilai</strong> adalah kondisi saat
                ini. Kolom mutasi mengikuti periode di atas.</p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 lg:w-2/3">
                <div class="bg-white border border-stone-200/90 rounded-2xl p-5 shadow-xs">
                    <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Jumlah Bahan</p>
                    <p class="text-2xl font-bold text-stone-900 mt-1">{{ $report['summary']['ingredients'] }}</p>
                </div>
                <div class="bg-white border border-stone-200/90 rounded-2xl p-5 shadow-xs">
                    <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Stok Menipis</p>
                    <p
                        class="text-2xl font-bold mt-1 {{ $report['summary']['low'] ? 'text-rose-600' : 'text-stone-900' }}">
                        {{ $report['summary']['low'] }}
                    </p>
                </div>
                <div class="bg-white border border-stone-200/90 rounded-2xl p-5 shadow-xs">
                    <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Nilai Persediaan</p>
                    <p class="text-2xl font-bold text-stone-900 mt-1">@rupiah($report['summary']['stock_value'])</p>
                </div>
            </div>

            <div class="bg-white border border-stone-200/90 rounded-2xl shadow-xs overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead
                        class="bg-stone-50 text-left text-xs uppercase text-stone-400 font-semibold border-b border-stone-100">
                        <tr>
                            <th class="px-4 py-3.5">Bahan</th>
                            <th class="px-4 py-3.5 text-right">Stok</th>
                            <th class="px-4 py-3.5 text-right">Min</th>
                            <th class="px-4 py-3.5 text-right">Nilai</th>
                            <th class="px-4 py-3.5 text-right">Masuk</th>
                            <th class="px-4 py-3.5 text-right">Keluar</th>
                            <th class="px-4 py-3.5 text-right">Terjual</th>
                            <th class="px-4 py-3.5 text-right">Kembali</th>
                            <th class="px-4 py-3.5 text-right">Opname</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse ($report['rows'] as $r)
                            <tr class="hover:bg-stone-50/60 transition">
                                <td class="px-4 py-3.5 font-medium text-stone-800">
                                    {{ $r['name'] }}
                                    @if ($r['low'])
                                        <span class="text-xs text-rose-600 font-semibold ms-1">(Menipis)</span>
                                    @endif
                                </td>
                                <td
                                    class="px-4 py-3.5 text-right {{ $r['low'] ? 'text-rose-600 font-semibold' : 'text-stone-700' }}">
                                    @qty($r['stock']) {{ $r['unit'] }}
                                </td>
                                <td class="px-4 py-3.5 text-right text-stone-500">@qty($r['min_stock'])</td>
                                <td class="px-4 py-3.5 text-right font-medium text-stone-800">@rupiah($r['value'])</td>
                                <td class="px-4 py-3.5 text-right text-emerald-700 font-medium">@qty($r['in'])</td>
                                <td class="px-4 py-3.5 text-right text-stone-600">@qty($r['out'])</td>
                                <td class="px-4 py-3.5 text-right text-stone-600">@qty($r['sold'])</td>
                                <td class="px-4 py-3.5 text-right text-stone-600">@qty($r['returned'] ?? 0)</td>
                                <td
                                    class="px-4 py-3.5 text-right {{ $r['opname'] < 0 ? 'text-rose-600' : 'text-stone-700' }}">
                                    {{ $r['opname'] > 0 ? '+' : '' }}@qty($r['opname'])
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-10 text-center text-stone-400">Belum ada bahan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
