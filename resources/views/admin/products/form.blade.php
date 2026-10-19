@php $editing = isset($product); @endphp
<x-app-layout>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <form method="POST"
                action="{{ $editing ? route('admin.products.update', $product) : route('admin.products.store') }}"
                class="bg-white shadow-sm sm:rounded-lg p-6 space-y-5">
                @csrf
                @if ($editing)
                    @method('PUT')
                @endif

                <x-select name="category_id" label="Kategori" :options="$categories" :selected="$product->category_id ?? null" blank="-- pilih --"
                    required />
                <x-field name="sku" label="SKU" :value="$product->sku ?? null" required />
                <x-field name="name" label="Nama menu" :value="$product->name ?? null" required />
                <x-field name="price" label="Harga (Rp)" type="number" step="500" min="0"
                    :value="isset($product) ? (int) $product->price : null" required
                    hint="Harga pada transaksi lama tidak berubah (tersimpan sebagai snapshot)." />

                <div>
                    <x-input-label for="description" value="Deskripsi" />
                    <textarea id="description" name="description" rows="3"
                        class="block w-full rounded-xl border border-stone-300 bg-white px-3.5 py-2.5 text-sm text-stone-900
                    placeholder-stone-400 shadow-xs transition duration-150
                    hover:border-stone-400
                    focus:border-amber-600 focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:outline-none
                    disabled:cursor-not-allowed disabled:bg-stone-100 disabled:text-stone-400">{{ old('description', $product->description ?? '') }}</textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>

                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300"
                        @checked(old('is_active', $product->is_active ?? true))>
                    Tampil di POS
                </label>

                <div class="flex gap-3">
                    <x-primary-button>Simpan</x-primary-button>
                    <a href="{{ route('admin.products.index') }}"><x-secondary-button
                            type="button">Batal</x-secondary-button></a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
