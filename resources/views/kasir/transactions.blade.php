<x-app-layout>
    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">
            <x-flash />
            <x-errors-box />

            @if (!$shift)
                <div
                    class="bg-white border border-stone-200/90 rounded-2xl p-10 text-center text-stone-500 shadow-xs space-y-3">
                    <svg class="mx-auto h-10 w-10 text-stone-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-stone-600 font-medium">Tidak ada shift berjalan saat ini.</p>
                    <div>
                        <a href="{{ route('kasir.shift.index') }}"
                            class="inline-flex items-center gap-1.5 text-sm font-semibold text-amber-800 hover:text-amber-900 hover:underline">
                            Buka shift sekarang &rarr;
                        </a>
                    </div>
                </div>
            @else
                <div class="bg-white border border-stone-200/90 rounded-2xl p-4 text-sm flex flex-wrap gap-8 shadow-xs">
                    <div>
                        <span class="text-xs text-stone-500 block">Transaksi Selesai</span>
                        <strong class="text-base text-stone-900">{{ $completedCount }} transaksi</strong>
                    </div>
                    <div>
                        <span class="text-xs text-stone-500 block">Total Omzet Shift Ini</span>
                        <strong class="text-base text-amber-800">@rupiah($completedTotal)</strong>
                    </div>
                </div>

                <div class="bg-white border border-stone-200/90 rounded-2xl shadow-xs overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead
                                class="bg-stone-50 text-left text-xs uppercase text-stone-400 border-b border-stone-100">
                                <tr>
                                    <th class="px-5 py-3 font-semibold">Invoice</th>
                                    <th class="px-5 py-3 font-semibold">Waktu</th>
                                    <th class="px-5 py-3 font-semibold">Metode</th>
                                    <th class="px-5 py-3 text-right font-semibold">Total</th>
                                    <th class="px-5 py-3 font-semibold">Status</th>
                                    <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100">
                                @forelse ($transactions as $t)
                                    <tr class="align-top hover:bg-stone-50/60 transition">
                                        <td class="px-5 py-3.5 font-semibold text-stone-900">{{ $t->invoice_no }}</td>
                                        <td class="px-5 py-3.5 text-stone-600">
                                            {{ $t->created_at->translatedFormat('H:i') }}</td>
                                        <td class="px-5 py-3.5 text-stone-700">{{ $t->payment_method->label() }}</td>
                                        <td class="px-5 py-3.5 text-right font-bold text-stone-900">@rupiah($t->total)
                                        </td>
                                        <td class="px-5 py-3.5">
                                            <x-status :value="$t->status" />
                                        </td>
                                        <td class="px-5 py-3.5 text-right">
                                            <div class="flex flex-col items-end gap-1.5">
                                                <a href="{{ route('kasir.transactions.receipt', $t) }}" target="_blank"
                                                    class="inline-flex items-center gap-1 text-xs font-semibold text-amber-800 hover:text-amber-900 hover:underline">
                                                    Cetak Struk
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-5 py-10 text-center text-stone-400">
                                            Belum ada transaksi pada shift ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                {{ $transactions->links() }}
            @endif
        </div>
    </div>
</x-app-layout>
