<x-app-layout>
    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-flash />

            <form method="POST" action="{{ route('profile.update') }}"
                class="bg-white border border-stone-200/90 rounded-2xl shadow-xs p-6 space-y-4">
                @csrf
                @method('PATCH')
                <h3 class="font-semibold text-stone-900">Data akun</h3>
                <x-field name="name" label="Nama" :value="$user->name" required />
                <x-field name="email" label="Email" type="email" :value="$user->email" required />
                <div class="text-xs text-stone-500">Peran: {{ $user->role->label() }}</div>
                <x-primary-button>Simpan</x-primary-button>
            </form>

            <form method="POST" action="{{ route('password.update') }}"
                class="bg-white border border-stone-200/90 rounded-2xl shadow-xs p-6 space-y-4">
                @csrf
                @method('PUT')
                <h3 class="font-semibold text-stone-900">Ganti password</h3>

                @if (session('status') === 'password-updated')
                    <p class="text-sm font-medium text-emerald-700">Password diperbarui.</p>
                @endif

                <div>
                    <x-input-label for="current_password" value="Password saat ini" />
                    <x-text-input id="current_password" name="current_password" type="password"
                        class="mt-1 block w-full" autocomplete="current-password" />
                    <x-input-error :messages="$errors->updatePassword->get('current_password')" />
                </div>
                <div>
                    <x-input-label for="password" value="Password baru" />
                    <x-text-input id="password" name="password" type="password" class="mt-1 block w-full"
                        autocomplete="new-password" />
                    <x-input-error :messages="$errors->updatePassword->get('password')" />
                </div>
                <div>
                    <x-input-label for="password_confirmation" value="Ulangi password baru" />
                    <x-text-input id="password_confirmation" name="password_confirmation" type="password"
                        class="mt-1 block w-full" autocomplete="new-password" />
                </div>
                <x-primary-button>Ganti Password</x-primary-button>
            </form>
        </div>
    </div>
</x-app-layout>
