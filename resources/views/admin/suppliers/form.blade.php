@php $editing = isset($supplier); @endphp
<x-app-layout>

    <div class="py-8">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <form method="POST"
                action="{{ $editing ? route('admin.suppliers.update', $supplier) : route('admin.suppliers.store') }}"
                class="bg-white shadow-sm sm:rounded-lg p-6 space-y-5">
                @csrf
                @if ($editing)
                    @method('PUT')
                @endif

                <x-field name="name" label="Nama supplier" :value="$supplier->name ?? null" required />
                <x-field name="contact_person" label="Nama kontak" :value="$supplier->contact_person ?? null" />
                <x-field name="phone" label="Telepon" :value="$supplier->phone ?? null" />
                <x-field name="email" label="Email" type="email" :value="$supplier->email ?? null" />

                <div>
                    <x-input-label for="address" value="Alamat" />
                    <textarea id="address" name="address" rows="3"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">{{ old('address', $supplier->address ?? '') }}</textarea>
                    <x-input-error :messages="$errors->get('address')" class="mt-2" />
                </div>

                <div class="flex gap-3">
                    <x-primary-button>Simpan</x-primary-button>
                    <a href="{{ route('admin.suppliers.index') }}"><x-secondary-button
                            type="button">Batal</x-secondary-button></a>
                </div>

            </form>
        </div>
    </div>
</x-app-layout>
