<x-app-layout>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-flash />

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                @foreach ($periods as $p)
                    <div class="bg-white border border-stone-200/90 rounded-2xl p-5 shadow-xs">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-stone-500">{{ $p['label'] }}
                        </h3>
                        <p class="mt-3 text-2xl font-bold text-stone-900 tracking-tight">@rupiah($p['net_sales'])</p>
                        <p class="text-xs text-stone-400 mt-0.5">Omzet bersih (sebelum pajak) &middot;
                            {{ $p['transactions'] }} transaksi</p>

                        <dl class="mt-4 pt-3 border-t border-stone-100 space-y-1.5 text-sm">
                            <div class="flex justify-between text-stone-600">
                                <dt>Pengeluaran bahan</dt>
                                <dd class="font-medium text-stone-800">@rupiah($p['purchases'])</dd>
                            </div>
                            <div class="flex justify-between font-semibold">
                                <dt class="text-stone-700">Laba kotor</dt>
                                <dd class="{{ $p['gross_profit'] < 0 ? 'text-rose-600' : 'text-emerald-700' }}">
                                    @rupiah($p['gross_profit'])</dd>
                            </div>
                        </dl>
                    </div>
                @endforeach
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('superadmin.transactions.index', ['verification_status' => 'flagged']) }}"
                    class="bg-white border border-stone-200/90 rounded-2xl p-4 shadow-xs hover:border-amber-600 transition">
                    <p class="text-xs font-medium text-stone-500">Transaksi ditandai</p>
                    <p class="mt-1 text-2xl font-bold {{ $flagged > 0 ? 'text-rose-600' : 'text-stone-900' }}">
                        {{ $flagged }}</p>
                </a>
                <a href="{{ route('superadmin.transactions.index', ['verification_status' => 'pending']) }}"
                    class="bg-white border border-stone-200/90 rounded-2xl p-4 shadow-xs hover:border-amber-600 transition">
                    <p class="text-xs font-medium text-stone-500">Menunggu verifikasi</p>
                    <p class="mt-1 text-2xl font-bold text-stone-900">{{ $pending_verification }}</p>
                </a>
                <div class="bg-white border border-stone-200/90 rounded-2xl p-4 shadow-xs">
                    <p class="text-xs font-medium text-stone-500">Shift berjalan</p>
                    <p class="mt-1 text-2xl font-bold text-stone-900">{{ $open_shifts }}</p>
                </div>
                <div class="bg-white border border-stone-200/90 rounded-2xl p-4 shadow-xs">
                    <p class="text-xs font-medium text-stone-500">Bahan stok menipis</p>
                    <p class="mt-1 text-2xl font-bold {{ $low_stock > 0 ? 'text-amber-700' : 'text-stone-900' }}">
                        {{ $low_stock }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                <div class="bg-white border border-stone-200/90 rounded-2xl p-5 shadow-xs">
                    <div class="flex items-center justify-between">
                        <h3 class="font-bold text-stone-900">Omzet bersih 7 hari terakhir</h3>
                        <span class="text-xs text-stone-400">Total Harian</span>
                    </div>
                    @php $max = max(1, collect($daily)->max('net')); @endphp
                    <div class="mt-6 flex items-end gap-3 h-44 pb-2 border-b border-stone-100">
                        @foreach ($daily as $d)
                            <div class="flex-1 flex flex-col items-center justify-end h-full group">
                                <div class="w-full rounded-t-lg bg-amber-700 group-hover:bg-amber-800 transition duration-150"
                                    style="height: {{ max(4, round(($d['net'] / $max) * 100)) }}%"
                                    title="@rupiah($d['net'])"></div>
                                <span
                                    class="mt-2 text-[11px] font-medium text-stone-500">{{ $d['date']->translatedFormat('D d') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white border border-stone-200/90 rounded-2xl p-5 shadow-xs">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-bold text-stone-900">Produk terlaris bulan ini</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="text-left text-xs uppercase text-stone-400 border-b border-stone-100">
                                <tr>
                                    <th class="py-2 font-semibold">Menu</th>
                                    <th class="py-2 text-right font-semibold">Terjual</th>
                                    <th class="py-2 text-right font-semibold">Pendapatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100">
                                @forelse ($top_products as $row)
                                    <tr>
                                        <td class="py-2.5 font-medium text-stone-800">{{ $row->name }}</td>
                                        <td class="py-2.5 text-right font-semibold text-stone-600">
                                            {{ (int) $row->qty }} porsi</td>
                                        <td class="py-2.5 text-right font-bold text-amber-800">@rupiah($row->revenue)</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-6 text-center text-stone-400">Belum ada penjualan
                                            bulan ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
