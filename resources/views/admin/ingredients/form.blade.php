@php $editing = isset($ingredient); @endphp
<x-app-layout>

    <div class="py-6">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST"
                action="{{ $editing ? route('admin.ingredients.update', $ingredient) : route('admin.ingredients.store') }}"
                class="bg-white border border-stone-200/90 rounded-2xl p-6 space-y-5 shadow-xs">
                @csrf
                @if ($editing)
                    @method('PUT')
                @endif

                <x-field name="name" label="Nama bahan" :value="$ingredient->name ?? null" required />
                <x-field name="unit" label="Satuan" :value="$ingredient->unit ?? null" required hint="Contoh: gram, ml, pcs, lembar" />
                <x-field name="min_stock" label="Stok minimum (batas notifikasi)" type="number" step="10"
                    min="0" :value="isset($ingredient) ? (float) $ingredient->min_stock : 0" required />
                <x-field name="last_cost" label="Harga beli per satuan (Rp)" type="number" step="5"
                    min="0" :value="isset($ingredient) ? (float) $ingredient->last_cost : 0" required />

                @if ($editing)
                    <p class="text-xs text-stone-500 bg-stone-50 p-3 rounded-xl border border-stone-200">
                        Stok saat ini: <strong class="text-stone-900">@qty($ingredient->stock)
                            {{ $ingredient->unit }}</strong>.
                        Ubah stok lewat menu <a href="{{ route('admin.stock.index') }}"
                            class="font-semibold text-amber-800 hover:text-amber-900 hover:underline">Stok</a> agar
                        tercatat di kartu stok.
                    </p>
                @else
                    <x-field name="initial_stock" label="Stok awal" type="number" step="10" min="0"
                        value="0" />
                @endif

                <div class="flex gap-3 pt-2">
                    <x-primary-button>Simpan Bahan</x-primary-button>
                    <a href="{{ route('admin.ingredients.index') }}"><x-secondary-button
                            type="button">Batal</x-secondary-button></a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
