<x-app-layout>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6" x-data="{ openStock: @js($errors->any()) }">
            <x-flash />

            @php
                $options = $ingredients->mapWithKeys(
                    fn($i) => [
                        $i->id => $i->name . ' (stok: ' . \App\Support\Format::qty($i->stock) . ' ' . $i->unit . ')',
                    ],
                );
            @endphp

            <form method="GET" x-data @change="if ($event.target.tagName === 'SELECT') $el.requestSubmit()"
                @input.debounce.300ms="if (['date_from', 'date_to'].includes($event.target.name)) $el.requestSubmit()"
                class="bg-white shadow-sm sm:rounded-lg p-4 flex flex-wrap gap-3 items-end">
                <x-select name="ingredient_id" label="Bahan" :options="$ingredients->pluck('name', 'id')" :selected="$filters['ingredient_id'] ?? null" blank="Semua" />
                <x-select name="type" label="Jenis" :options="$types" :selected="$filters['type'] ?? null" blank="Semua" />
                <div>
                    <x-input-label for="date_from" value="Dari" />
                    <x-text-input id="date_from" name="date_from" type="date" class="mt-1" :value="$filters['date_from'] ?? ''" />
                </div>
                <div>
                    <x-input-label for="date_to" value="Sampai" />
                    <x-text-input id="date_to" name="date_to" type="date" class="mt-1" :value="$filters['date_to'] ?? ''" />
                </div>

                <div class="ml-auto">
                    <x-primary-button type="button" @click="openStock = true">Update Stok</x-primary-button>
                </div>
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Waktu</th>
                            <th class="px-4 py-3">Bahan</th>
                            <th class="px-4 py-3">Jenis</th>
                            <th class="px-4 py-3 text-right">Jumlah</th>
                            <th class="px-4 py-3 text-right">Stok akhir</th>
                            <th class="px-4 py-3">Oleh</th>
                            <th class="px-4 py-3">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($movements as $m)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    {{ $m->created_at->translatedFormat('d M Y H:i') }}</td>
                                <td class="px-4 py-3">{{ $m->ingredient->name }}</td>
                                <td class="px-4 py-3">
                                    <p>{{ $m->type->label() }}</p>
                                </td>
                                <td
                                    class="px-4 py-3 text-right font-medium {{ (float) $m->quantity < 0 ? 'text-red-600' : 'text-green-700' }}">
                                    {{ (float) $m->quantity > 0 ? '+' : '' }}@qty($m->quantity)
                                    {{ $m->ingredient->unit }}
                                </td>
                                <td class="px-4 py-3 text-right">@qty($m->stock_after)</td>
                                <td class="px-4 py-3">{{ $m->user?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $m->note }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-400">Belum ada mutasi stok.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $movements->links() }}

            <template x-teleport="body">
                <div x-show="openStock" x-cloak x-effect="document.body.classList.toggle('overflow-hidden', openStock)"
                    @keydown.escape.window="openStock = false"
                    class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/50 p-4 sm:py-10"
                    role="dialog" aria-modal="true" aria-labelledby="stock-modal-title">
                    <div @click.outside="openStock = false" class="w-full max-w-6xl rounded-lg bg-gray-100 shadow-xl">
                        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-3">
                            <h3 id="stock-modal-title" class="text-lg font-semibold text-gray-800">Update Stok</h3>
                            <button type="button" @click="openStock = false"
                                class="rounded p-1 text-gray-500 hover:bg-gray-200 hover:text-gray-700"
                                aria-label="Tutup">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="space-y-4 p-5">
                            <x-errors-box />

                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                                <form method="POST" action="{{ route('admin.stock.in') }}"
                                    class="bg-white shadow-sm sm:rounded-lg p-5 space-y-3">
                                    @csrf
                                    <h3 class="font-medium text-gray-900">Stok masuk</h3>
                                    <x-select name="ingredient_id" label="Bahan" :options="$options" blank="-- pilih --"
                                        required />
                                    <x-field name="quantity" label="Jumlah" type="number" step="0.001" min="0.001"
                                        required />
                                    <x-field name="unit_cost" label="Harga beli / satuan (opsional)" type="number"
                                        step="0.01" min="0" />
                                    <x-field name="note" label="Catatan" />
                                    <x-primary-button>Tambah Stok</x-primary-button>
                                </form>

                                <form method="POST" action="{{ route('admin.stock.out') }}"
                                    class="bg-white shadow-sm sm:rounded-lg p-5 space-y-3">
                                    @csrf
                                    <h3 class="font-medium text-gray-900">Stok keluar (rusak/kedaluwarsa/lainnya)</h3>
                                    <x-select name="ingredient_id" label="Bahan" :options="$options"
                                        blank="-- pilih --" required />
                                    <x-field name="quantity" label="Jumlah" type="number" step="0.001"
                                        min="0.001" required />
                                    <x-field name="note" label="Alasan" required />
                                    <x-danger-button>Kurangi Stok</x-danger-button>
                                </form>

                                <form method="POST" action="{{ route('admin.stock.opname') }}"
                                    class="bg-white shadow-sm sm:rounded-lg p-5 space-y-3">
                                    @csrf
                                    <h3 class="font-medium text-gray-900">Stok opname</h3>
                                    <x-select name="ingredient_id" label="Bahan" :options="$options"
                                        blank="-- pilih --" required />
                                    <x-field name="counted_stock" label="Hasil hitung fisik" type="number"
                                        step="0.001" min="0" required />
                                    <x-field name="note" label="Catatan" />
                                    <x-secondary-button type="submit">Simpan Opname</x-secondary-button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</x-app-layout>
