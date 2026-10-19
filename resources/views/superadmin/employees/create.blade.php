<x-app-layout>
    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('superadmin.employees.store') }}"
                class="bg-white border border-stone-200/90 rounded-2xl p-6 shadow-xs space-y-5">
                @csrf

                @include('superadmin.employees._fields')

                <div>
                    <x-input-label for="password" value="Password" />
                    <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required
                        autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
                </div>

                <div>
                    <x-input-label for="password_confirmation" value="Konfirmasi Password" />
                    <x-text-input id="password_confirmation" name="password_confirmation" type="password"
                        class="mt-1 block w-full" required autocomplete="new-password" />
                </div>

                <label class="inline-flex items-center gap-2 text-sm text-stone-700">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1"
                        class="rounded border-stone-300 text-amber-700 focus:ring-amber-500/20"
                        @checked(old('is_active', '1') == '1')>
                    <span>Akun langsung aktif</span>
                </label>

                <div class="flex gap-3 pt-2">
                    <x-primary-button>Simpan Pegawai</x-primary-button>
                    <a href="{{ route('superadmin.employees.index') }}"><x-secondary-button
                            type="button">Batal</x-secondary-button></a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
