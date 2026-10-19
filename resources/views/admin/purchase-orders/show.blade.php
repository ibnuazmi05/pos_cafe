<x-app-layout>
    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            <div class="bg-white shadow-sm sm:rounded-lg p-6 grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <p class="text-gray-500">Supplier</p>
                    <p class="font-medium">{{ $order->supplier->name }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Status</p><x-status :value="$order->status" />
                </div>
                <div>
                    <p class="text-gray-500">Dibuat oleh</p>
                    <p class="font-medium">{{ $order->creator->name }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Tanggal pesan</p>
                    <p class="font-medium">{{ $order->ordered_at?->translatedFormat('d M Y') ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Tanggal terima</p>
                    <p class="font-medium">{{ $order->received_at?->translatedFormat('d M Y') ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Diterima oleh</p>
                    <p class="font-medium">{{ $order->receiver?->name ?? '-' }}</p>
                </div>
                @if ($order->note)
                    <div class="col-span-2 md:col-span-3">
                        <p class="text-gray-500">Catatan</p>
                        <p class="font-medium">{{ $order->note }}</p>
                    </div>
                @endif
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Bahan</th>
                            <th class="px-4 py-3 text-right">Jumlah</th>
                            <th class="px-4 py-3 text-right">Harga satuan</th>
                            <th class="px-4 py-3 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($order->items as $item)
                            <tr>
                                <td class="px-4 py-3">{{ $item->ingredient->name }}</td>
                                <td class="px-4 py-3 text-right">@qty($item->quantity) {{ $item->ingredient->unit }}</td>
                                <td class="px-4 py-3 text-right">@rupiah($item->unit_price)</td>
                                <td class="px-4 py-3 text-right">@rupiah($item->subtotal)</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="font-semibold">
                            <td colspan="3" class="px-4 py-3 text-right">Total</td>
                            <td class="px-4 py-3 text-right">@rupiah($order->total)</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="flex flex-wrap gap-3 items-center">
                @can('update', $order)
                    <a href="{{ route('admin.purchase-orders.edit', $order) }}"><x-secondary-button type="button">Edit
                            Draft</x-secondary-button></a>
                @endcan

                @can('order', $order)
                    <form method="POST" action="{{ route('admin.purchase-orders.order', $order) }}"
                        onsubmit="return confirm('Tandai PO ini sebagai sudah dipesan ke supplier?')">
                        @csrf @method('PATCH')
                        <x-primary-button>Tandai Dipesan</x-primary-button>
                    </form>
                @endcan

                @can('receive', $order)
                    <form method="POST" action="{{ route('admin.purchase-orders.receive', $order) }}"
                        class="flex items-center gap-2"
                        onsubmit="return confirm('Terima barang? Stok bahan akan bertambah dan tidak dapat dibatalkan.')">
                        @csrf @method('PATCH')
                        <x-text-input type="date" name="received_at" :value="now()->toDateString()"
                            max="{{ now()->toDateString() }}" />
                        <x-primary-button>Terima Barang</x-primary-button>
                    </form>
                @endcan

                @can('cancel', $order)
                    <form method="POST" action="{{ route('admin.purchase-orders.cancel', $order) }}"
                        onsubmit="return confirm('Batalkan PO ini?')">
                        @csrf @method('PATCH')
                        <x-danger-button>Batalkan PO</x-danger-button>
                    </form>
                @endcan

                @can('delete', $order)
                    <form method="POST" action="{{ route('admin.purchase-orders.destroy', $order) }}"
                        onsubmit="return confirm('Hapus draft PO ini?')">
                        @csrf @method('DELETE')
                        <button class="text-sm text-red-600 hover:underline">Hapus draft</button>
                    </form>
                @endcan
            </div>

            <div>
                <a href=" {{ route('admin.purchase-orders.index') }}">
                    <x-primary-button>Kembali</x-primary-button>
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
