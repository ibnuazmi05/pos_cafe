@php $editing = isset($category); @endphp
<x-app-layout>
    <div class="py-8">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <form method="POST"
                action="{{ $editing ? route('admin.categories.update', $category) : route('admin.categories.store') }}"
                class="bg-white shadow-sm sm:rounded-lg p-6 space-y-5">
                @csrf
                @if ($editing)
                    @method('PUT')
                @endif

                <x-field name="name" label="Nama kategori" :value="$category->name ?? null" required />

                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300"
                        @checked(old('is_active', $category->is_active ?? true))>
                    Aktif
                </label>

                <div class="flex gap-3">
                    <x-primary-button>Simpan</x-primary-button>
                    <a href="{{ route('admin.categories.index') }}"><x-secondary-button
                            type="button">Batal</x-secondary-button></a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
