<x-app-layout>
    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <div
                class="bg-white border border-stone-200/90 rounded-2xl shadow-xs p-6 grid grid-cols-2 md:grid-cols-3 gap-5 text-sm">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Waktu</p>
                    <p class="mt-1 font-semibold text-stone-800">
                        {{ $transaction->created_at->translatedFormat('d M Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Kasir</p>
                    <p class="mt-1 font-semibold text-stone-800">{{ $transaction->cashier->name }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Shift</p>
                    <p class="mt-1 font-semibold text-stone-800">#{{ $transaction->shift_id }}
                        ({{ $transaction->shift->opened_at->translatedFormat('d M H:i') }})</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Metode bayar</p>
                    <p class="mt-1 font-semibold text-stone-800">{{ $transaction->payment_method->label() }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Verifikasi</p>
                    <div class="mt-1"><x-status :value="$transaction->verification_status" /></div>
                </div>
                @if ($transaction->verifier)
                    <div class="col-span-2 md:col-span-3 pt-3 border-t border-stone-100">
                        <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Diverifikasi oleh</p>
                        <p class="mt-1 text-stone-700">{{ $transaction->verifier->name }} &middot;
                            {{ $transaction->verified_at?->translatedFormat('d M Y H:i') }}</p>
                    </div>
                @endif
                @if ($transaction->note)
                    <div class="col-span-2 md:col-span-3 pt-3 border-t border-stone-100">
                        <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Catatan admin</p>
                        <p class="mt-1 text-stone-700">{{ $transaction->note }}</p>
                    </div>
                @endif
            </div>

            <div class="bg-white border border-stone-200/90 rounded-2xl shadow-xs overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead
                        class="bg-stone-50 text-left text-xs uppercase text-stone-400 border-b border-stone-100 font-semibold">
                        <tr>
                            <th class="px-5 py-3.5">Menu</th>
                            <th class="px-5 py-3.5 text-right">Harga</th>
                            <th class="px-5 py-3.5 text-right">Qty</th>
                            <th class="px-5 py-3.5 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($transaction->items as $item)
                            <tr class="hover:bg-stone-50/60 transition">
                                <td class="px-5 py-3.5">
                                    <span class="font-medium text-stone-800">{{ $item->product_name }}</span>
                                    @if ($item->note)
                                        <span class="block text-xs text-stone-400">{{ $item->note }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right text-stone-600">@rupiah($item->price)</td>
                                <td class="px-5 py-3.5 text-right font-medium text-stone-800">{{ $item->quantity }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-semibold text-stone-800">@rupiah($item->subtotal)</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="text-sm divide-y divide-stone-100 border-t border-stone-100 bg-stone-50/40">
                        <tr>
                            <td colspan="3" class="px-5 py-2 text-right text-stone-500">Subtotal</td>
                            <td class="px-5 py-2 text-right text-stone-800">@rupiah($transaction->subtotal)</td>
                        </tr>
                        @if ((float) $transaction->discount > 0)
                            <tr>
                                <td colspan="3" class="px-5 py-2 text-right text-stone-500">Diskon</td>
                                <td class="px-5 py-2 text-right text-rose-600">-@rupiah($transaction->discount)</td>
                            </tr>
                        @endif
                        @if ((float) $transaction->tax > 0)
                            <tr>
                                <td colspan="3" class="px-5 py-2 text-right text-stone-500">Pajak
                                    ({{ rtrim(rtrim($transaction->tax_rate, '0'), '.') }}%)</td>
                                <td class="px-5 py-2 text-right text-stone-800">@rupiah($transaction->tax)</td>
                            </tr>
                        @endif
                        <tr class="font-bold bg-stone-50/80">
                            <td colspan="3" class="px-5 py-2.5 text-right text-stone-800">Total</td>
                            <td class="px-5 py-2.5 text-right text-stone-900">@rupiah($transaction->total)</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="px-5 py-2 text-right text-stone-500">Dibayar</td>
                            <td class="px-5 py-2 text-right text-stone-800">@rupiah($transaction->paid_amount)</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="px-5 py-2 text-right text-stone-500">Kembalian</td>
                            <td class="px-5 py-2 text-right text-stone-800">@rupiah($transaction->change_amount)</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
