<x-app-layout>
    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <x-flash />
            <x-errors-box />

            <form method="POST" action="{{ route('admin.products.recipe.update', $product) }}"
                class="bg-white border border-stone-200/90 rounded-2xl p-6 shadow-xs space-y-5" x-data="{
                    ingredients: @js($ingredientMap),
                    rows: @js($rows),
                    add() { this.rows.push({ ingredient_id: '', quantity: '' }); },
                    remove(i) { this.rows.splice(i, 1); },
                    unit(id) { return this.ingredients[id] ? this.ingredients[id].unit : ''; }
                }">
                @csrf @method('PUT')

                <div class="rounded-xl bg-stone-50 border border-stone-200 p-3.5 text-xs text-stone-600">
                    Jumlah bahan yang terpakai untuk pembuatan <strong>1 porsi</strong>. Stok bahan akan otomatis
                    terpotong saat menu ini dibeli pelanggan di kasir.
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-xs uppercase text-stone-400 border-b border-stone-100">
                            <tr>
                                <th class="py-2.5 font-semibold">Bahan Baku</th>
                                <th class="py-2.5 font-semibold">Takaran / Porsi</th>
                                <th class="py-2.5 text-right font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            <template x-for="(row, i) in rows" :key="i">
                                <tr>
                                    <td class="py-2.5 pe-2">
                                        <select :name="'ingredients[' + i + '][ingredient_id]'"
                                            x-model="row.ingredient_id" required
                                            class="w-full border-stone-300 rounded-xl shadow-xs text-sm focus:border-amber-600 focus:ring-2 focus:ring-amber-500/20">
                                            <option value="">-- Pilih bahan baku --</option>
                                            @foreach ($ingredients as $ing)
                                                <option value="{{ $ing->id }}">{{ $ing->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="py-2.5 pe-2">
                                        <div class="flex items-center gap-2">
                                            <input type="number" step="0.001" min="0.001" required
                                                x-model="row.quantity" :name="'ingredients[' + i + '][quantity]'"
                                                class="w-32 border-stone-300 rounded-xl shadow-xs text-sm focus:border-amber-600 focus:ring-2 focus:ring-amber-500/20">
                                            <span class="text-xs font-semibold text-stone-500"
                                                x-text="unit(row.ingredient_id)"></span>
                                        </div>
                                    </td>
                                    <td class="py-2.5 text-right">
                                        <button type="button" @click="remove(i)"
                                            class="text-rose-600 hover:text-rose-700 font-semibold text-xs hover:underline">Hapus</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <p x-show="rows.length === 0" class="text-xs text-stone-400 py-3 text-center">Belum ada bahan baku. Menu
                    tanpa resep tidak akan mengurangi stok bahan.</p>

                <div>
                    <button type="button" @click="add()"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-800 hover:text-amber-900 hover:underline">
                        + Tambah Bahan Baku
                    </button>
                </div>

                <div class="flex gap-3 pt-3 border-t border-stone-100">
                    <x-primary-button>Simpan Resep</x-primary-button>
                    <a href="{{ route('admin.products.index') }}"><x-secondary-button
                            type="button">Batal</x-secondary-button></a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
