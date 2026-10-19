<x-app-layout>
    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <div class="grid justify-items-end">
                <a href="{{ route('admin.categories.create') }}">
                    <x-primary-button type="button">Tambah Kategori</x-primary-button>
                </a>
            </div>
            <x-flash />
            <div class="bg-white border border-stone-200/90 rounded-2xl shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-stone-50 text-left text-xs uppercase text-stone-400 border-b border-stone-100">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Nama</th>
                                <th class="px-5 py-3 font-semibold">Jumlah Menu</th>
                                <th class="px-5 py-3 font-semibold">Status</th>
                                <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @forelse ($categories as $c)
                                <tr class="hover:bg-stone-50/60 transition">
                                    <td class="px-5 py-3.5 font-semibold text-stone-900">{{ $c->name }}</td>
                                    <td class="px-5 py-3.5 text-stone-600">{{ $c->products_count }} produk</td>
                                    <td class="px-5 py-3.5 text-stone-700">
                                        {{ $c->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <div class="flex justify-end gap-3 text-xs">
                                            <a href="{{ route('admin.categories.edit', $c) }}"
                                                class="font-semibold text-amber-800 hover:text-amber-900 hover:underline">Edit</a>
                                            <form method="POST" action="{{ route('admin.categories.destroy', $c) }}"
                                                onsubmit="return confirm('Hapus kategori {{ e($c->name) }}?')">
                                                @csrf @method('DELETE')
                                                <button
                                                    class="font-semibold text-rose-600 hover:text-rose-700 hover:underline">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-8 text-center text-stone-400">Belum ada kategori.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            {{ $categories->links() }}
        </div>
    </div>
</x-app-layout>
