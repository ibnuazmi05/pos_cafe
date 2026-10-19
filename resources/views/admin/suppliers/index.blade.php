<x-app-layout>

    <div class="py-6">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <x-flash />
            <form method="GET" x-data @change="if ($event.target.tagName === 'SELECT') $el.requestSubmit()"
                @input.debounce.150ms="if ($event.target.name === 'q') $el.requestSubmit()"
                class="bg-white border border-stone-200/90 rounded-2xl p-4 shadow-xs flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-64">
                    <x-input-label for="q" value="Cari Supplier" />
                    <x-text-input id="q" name="q" class="mt-1" placeholder="Ketik nama supplier..."
                        :value="$filters['q'] ?? ''" x-init="if ($el.value) {
                            $el.focus();
                            $el.setSelectionRange($el.value.length, $el.value.length)
                        }" />
                </div>
                <div class="grid justify-items-end">
                    <a href="{{ route('admin.suppliers.create') }}">
                        <x-primary-button type="button">Tambah Supplier</x-primary-button>
                    </a>
                </div>
            </form>

            <div class="bg-white border border-stone-200/90 rounded-2xl shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-stone-50 text-left text-xs uppercase text-stone-400 border-b border-stone-100">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Nama Supplier</th>
                                <th class="px-5 py-3 font-semibold">Kontak Person</th>
                                <th class="px-5 py-3 font-semibold">Nomor Telepon</th>
                                <th class="px-5 py-3 font-semibold">Jumlah PO</th>
                                <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @forelse ($suppliers as $s)
                                <tr class="hover:bg-stone-50/60 transition">
                                    <td class="px-5 py-3.5 font-semibold text-stone-900">
                                        {{ $s->name }}
                                        <span
                                            class="block text-xs font-normal text-stone-400 mt-0.5">{{ $s->address }}</span>
                                    </td>
                                    <td class="px-5 py-3.5 text-stone-700">{{ $s->contact_person ?? '-' }}</td>
                                    <td class="px-5 py-3.5 text-stone-700 font-mono text-xs">{{ $s->phone ?? '-' }}</td>
                                    <td class="px-5 py-3.5 text-stone-600">{{ $s->purchase_orders_count }} pesanan</td>
                                    <td class="px-5 py-3.5">
                                        <div class="flex justify-end gap-3 text-xs">
                                            <a href="{{ route('admin.suppliers.edit', $s) }}"
                                                class="font-semibold text-amber-800 hover:text-amber-900 hover:underline">Edit</a>
                                            <form method="POST" action="{{ route('admin.suppliers.destroy', $s) }}"
                                                onsubmit="return confirm('Hapus supplier {{ e($s->name) }}?')">
                                                @csrf @method('DELETE')
                                                <button
                                                    class="font-semibold text-rose-600 hover:text-rose-700 hover:underline">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-10 text-center text-stone-400">Belum ada data
                                        supplier.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            {{ $suppliers->links() }}
        </div>
    </div>
</x-app-layout>
