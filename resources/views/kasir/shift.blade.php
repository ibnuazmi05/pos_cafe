<x-app-layout>
    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-flash />
            <x-errors-box />

            @if ($shift)
                <div class="bg-white border border-stone-200/90 rounded-2xl p-6 space-y-5 shadow-xs">
                    <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                        <h3 class="font-bold text-base text-stone-900">Shift Sedang Berjalan</h3>
                        <p class="mt-0.5 text-xs text-stone-500">Dibuka pada
                            {{ $shift->opened_at->translatedFormat('d M Y H:i') }}</p>
                    </div>

                    <dl
                        class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm bg-stone-50/70 p-4 rounded-xl border border-stone-100">
                        <div>
                            <dt class="text-xs text-stone-500">Modal awal</dt>
                            <dd class="font-bold text-stone-900 mt-0.5">@rupiah($shift->opening_cash)</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-stone-500">Transaksi</dt>
                            <dd class="font-bold text-stone-900 mt-0.5">{{ $summary['transactions'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-stone-500">Total penjualan</dt>
                            <dd class="font-bold text-amber-800 mt-0.5">@rupiah($summary['sales'])</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-stone-500">Kas tunai diharapkan</dt>
                            <dd class="font-bold text-stone-900 mt-0.5">@rupiah($summary['expected_cash'])</dd>
                        </div>
                    </dl>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="text-left text-xs uppercase text-stone-400 border-b border-stone-100">
                                <tr>
                                    <th class="py-2 font-semibold">Metode Pembayaran</th>
                                    <th class="py-2 text-right font-semibold">Jumlah Transaksi</th>
                                    <th class="py-2 text-right font-semibold">Total Nilai</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100">
                                @foreach ($summary['by_method'] as $m)
                                    <tr>
                                        <td class="py-2.5 font-medium text-stone-800">{{ $m['label'] }}</td>
                                        <td class="py-2.5 text-right font-medium text-stone-600">{{ $m['count'] }}
                                        </td>
                                        <td class="py-2.5 text-right font-bold text-stone-900">@rupiah($m['total'])</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <form method="POST" action="{{ route('kasir.shift.close') }}"
                    class="bg-white border border-stone-200/90 rounded-2xl p-6 space-y-4 shadow-xs"
                    onsubmit="return confirm('Tutup shift sekarang? Setelah ditutup Anda tidak dapat bertransaksi sampai membuka shift baru.')">
                    @csrf
                    <div>
                        <h3 class="font-bold text-base text-stone-900">Tutup Shift Kasir</h3>
                        <p class="text-xs text-stone-500 mt-0.5">Pastikan menghitung uang fisik di laci secara teliti
                            sebelum menutup shift.</p>
                    </div>

                    <x-field name="closing_cash" label="Uang tunai di laci (hitung fisik, Rp)" type="number"
                        step="500" min="0" required hint="Hitung seluruh uang tunai, termasuk modal awal." />
                    <x-field name="note" label="Catatan (opsional)" />

                    <div class="pt-2">
                        <x-danger-button>Tutup Shift Sekarang</x-danger-button>
                    </div>
                </form>
            @else
                <form method="POST" action="{{ route('kasir.shift.open') }}"
                    class="bg-white border border-stone-200/90 rounded-2xl p-8 space-y-5 shadow-xs">
                    @csrf
                    <div class="space-y-1">
                        <h3 class="font-bold text-lg text-stone-900">Buka Shift Kasir Baru</h3>
                        <p class="text-xs text-stone-500">Anda belum memiliki shift aktif. Masukkan modal awal (uang
                            kembalian di laci) untuk memulai transaksi.</p>
                    </div>

                    <x-field name="opening_cash" label="Modal Awal Kasir (Rp)" type="number" step="500"
                        min="0" value="0" required />

                    <div class="pt-2">
                        <x-primary-button>Buka Shift & Mulai Transaksi</x-primary-button>
                    </div>
                </form>
            @endif

            @if ($recent->isNotEmpty())
                <div class="bg-white border border-stone-200/90 rounded-2xl p-6 shadow-xs">
                    <h3 class="font-bold text-stone-900 mb-3">Riwayat Shift Terakhir Anda</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="text-left text-xs uppercase text-stone-400 border-b border-stone-100">
                                <tr>
                                    <th class="py-2 font-semibold">Dibuka</th>
                                    <th class="py-2 font-semibold">Ditutup</th>
                                    <th class="py-2 text-right font-semibold">Diharapkan</th>
                                    <th class="py-2 text-right font-semibold">Dihitung</th>
                                    <th class="py-2 text-right font-semibold">Selisih</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100">
                                @foreach ($recent as $s)
                                    <tr>
                                        <td class="py-2.5 text-stone-700">
                                            {{ $s->opened_at->translatedFormat('d M H:i') }}</td>
                                        <td class="py-2.5 text-stone-700">
                                            {{ $s->closed_at?->translatedFormat('d M H:i') }}</td>
                                        <td class="py-2.5 text-right font-medium text-stone-800">@rupiah($s->expected_cash)</td>
                                        <td class="py-2.5 text-right font-medium text-stone-800">@rupiah($s->closing_cash)</td>
                                        <td
                                            class="py-2.5 text-right font-bold {{ (float) $s->difference == 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                                            @rupiah($s->difference)</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
