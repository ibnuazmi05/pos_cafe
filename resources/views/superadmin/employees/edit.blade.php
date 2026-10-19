<x-app-layout>
    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-flash />

            <form method="POST" action="{{ route('superadmin.employees.update', $employee) }}"
                class="bg-white border border-stone-200/90 rounded-2xl p-6 space-y-5 shadow-xs">
                @csrf @method('PUT')

                @include('superadmin.employees._fields', ['employee' => $employee])

                <div class="flex gap-3 pt-2">
                    <x-primary-button>Simpan Perubahan</x-primary-button>
                    <a href="{{ route('superadmin.employees.index') }}"><x-secondary-button
                            type="button">Kembali</x-secondary-button></a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
