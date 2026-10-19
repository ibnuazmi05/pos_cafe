<x-app-layout>
    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <div
                class="bg-white border border-stone-200/90 rounded-2xl shadow-xs p-6 grid grid-cols-2 md:grid-cols-3 gap-5 text-sm">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Supplier</p>
                    <p class="mt-1 font-semibold text-stone-800">{{ $order->supplier->name }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Status</p>
                    <div class="mt-1"><x-status :value="$order->status" /></div>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Dibuat oleh</p>
                    <p class="mt-1 font-semibold text-stone-800">{{ $order->creator->name }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Tanggal pesan</p>
                    <p class="mt-1 font-semibold text-stone-800">
                        {{ $order->ordered_at?->translatedFormat('d M Y') ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Tanggal terima</p>
                    <p class="mt-1 font-semibold text-stone-800">
                        {{ $order->received_at?->translatedFormat('d M Y') ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Diterima oleh</p>
                    <p class="mt-1 font-semibold text-stone-800">{{ $order->receiver?->name ?? '-' }}</p>
                </div>
                @if ($order->note)
                    <div class="col-span-2 md:col-span-3 pt-3 border-t border-stone-100">
                        <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Catatan</p>
                        <p class="mt-1 text-stone-700">{{ $order->note }}</p>
                    </div>
                @endif
            </div>

            <div class="bg-white border border-stone-200/90 rounded-2xl shadow-xs overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead
                        class="bg-stone-50 text-left text-xs uppercase text-stone-400 border-b border-stone-100 font-semibold">
                        <tr>
                            <th class="px-5 py-3.5">Bahan</th>
                            <th class="px-5 py-3.5 text-right">Jumlah</th>
                            <th class="px-5 py-3.5 text-right">Harga satuan</th>
                            <th class="px-5 py-3.5 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($order->items as $item)
                            <tr class="hover:bg-stone-50/60 transition">
                                <td class="px-5 py-3.5 font-medium text-stone-800">{{ $item->ingredient->name }}</td>
                                <td class="px-5 py-3.5 text-right text-stone-600">
                                    {{ rtrim(rtrim($item->quantity, '0'), '.') }} {{ $item->ingredient->unit }}</td>
                                <td class="px-5 py-3.5 text-right text-stone-600">@rupiah($item->unit_price)</td>
                                <td class="px-5 py-3.5 text-right font-medium text-stone-800">@rupiah($item->subtotal)</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="font-bold border-t border-stone-100 bg-stone-50/50">
                            <td colspan="3" class="px-5 py-3.5 text-right text-stone-700">Total</td>
                            <td class="px-5 py-3.5 text-right text-stone-900">@rupiah($order->total)</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
