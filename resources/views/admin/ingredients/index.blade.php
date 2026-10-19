<x-app-layout>
    <div class="py-6">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <x-flash />
            <form method="GET" x-data @change="if ($event.target.tagName === 'SELECT') $el.requestSubmit()"
                @input.debounce.150ms="if ($event.target.name === 'q') $el.requestSubmit()"
                class="bg-white border border-stone-200/90 rounded-2xl p-4 shadow-xs flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-64">
                    <x-input-label for="q" value="Cari Bahan Baku" />
                    <x-text-input id="q" name="q" class="mt-1" placeholder="Ketik nama bahan..."
                        :value="$filters['q'] ?? ''" x-init="if ($el.value) {
                            $el.focus();
                            $el.setSelectionRange($el.value.length, $el.value.length)
                        }" />
                </div>
                <div class="ml-auto">
                    <a href="{{ route('admin.ingredients.create') }}">
                        <x-primary-button type="button">Tambah Bahan</x-primary-button>
                    </a>
                </div>
            </form>

            <div class="bg-white border border-stone-200/90 rounded-2xl shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-stone-50 text-left text-xs uppercase text-stone-400 border-b border-stone-100">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Bahan Baku</th>
                                <th class="px-5 py-3 text-right font-semibold">Stok Saat Ini</th>
                                <th class="px-5 py-3 text-right font-semibold">Batas Minimum</th>
                                <th class="px-5 py-3 text-right font-semibold">Harga Beli Terakhir</th>
                                <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @forelse ($ingredients as $i)
                                <tr class="hover:bg-stone-50/60 transition">
                                    <td class="px-5 py-3.5 font-semibold text-stone-900">{{ $i->name }}</td>
                                    <td class="px-5 py-3.5 text-right font-semibold text-stone-800">
                                        @qty($i->stock) {{ $i->unit }}
                                        @if ($i->is_low)
                                            <span class="text-xs font-normal text-rose-600 ms-1">(Menipis)</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-right text-stone-500">@qty($i->min_stock)
                                        {{ $i->unit }}</td>
                                    <td class="px-5 py-3.5 text-right font-medium text-stone-800">@rupiah($i->last_cost) /
                                        {{ $i->unit }}</td>
                                    <td class="px-5 py-3.5">
                                        <div class="flex justify-end gap-3 text-xs">
                                            <a href="{{ route('admin.stock.index', ['ingredient_id' => $i->id]) }}"
                                                class="font-medium text-stone-600 hover:text-stone-900 hover:underline">Mutasi</a>
                                            <a href="{{ route('admin.ingredients.edit', $i) }}"
                                                class="font-semibold text-amber-800 hover:text-amber-900 hover:underline">Edit</a>
                                            <form method="POST" action="{{ route('admin.ingredients.destroy', $i) }}"
                                                onsubmit="return confirm('Hapus bahan {{ e($i->name) }}?')">
                                                @csrf @method('DELETE')
                                                <button
                                                    class="font-semibold text-rose-600 hover:text-rose-700 hover:underline">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-10 text-center text-stone-400">Belum ada bahan
                                        baku.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            {{ $ingredients->links() }}
        </div>
    </div>
</x-app-layout>
