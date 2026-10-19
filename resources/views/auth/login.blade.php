<x-guest-layout>
    <div class="flex justify-center mb-6">
        <x-application-logo />
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4 text-emerald-700 font-medium text-sm" :status="session('status')" />

    <div class="mb-6 text-center">
        <h2 class="text-xl font-bold text-stone-900">
            Masuk ke Akun Anda
        </h2>
        <p class="text-xs text-stone-500 mt-1">
            Gunakan email dan password terdaftar untuk memulai
        </p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email"
                class="block mt-1 w-full"
                type="email" name="email" :value="old('email')" required autofocus autocomplete="username"
                placeholder="nama@cafe.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <div x-data="{ show: false }">
            <x-input-label for="password" :value="__('Password')" />

            <div class="relative mt-1">
                <x-text-input id="password"
                    class="block w-full pr-11"
                    x-bind:type="show ? 'text' : 'password'" name="password" required autocomplete="current-password"
                    placeholder="Masukkan password" />

                <button type="button" @click="show = !show"
                    class="absolute inset-y-0 right-0 flex items-center px-3 text-stone-400 hover:text-stone-700 focus:outline-none"
                    x-bind:aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'">

                    <!-- Mata terbuka: password sedang terlihat -->
                    <svg x-show="show" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>

                    <!-- Mata dicoret: password tersembunyi -->
                    <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                </button>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full justify-center h-11 text-base">
                {{ __('Masuk ke Aplikasi') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
