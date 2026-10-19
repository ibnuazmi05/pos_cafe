@php
    // [label, nama route, pola route aktif]. Link hanya tampil jika route-nya sudah ada.
    $menus = [
        'superadmin' => [
            ['Dashboard', 'superadmin.dashboard', 'superadmin.dashboard'],
            ['Pegawai', 'superadmin.employees.index', 'superadmin.employees.*'],
            ['Transaksi', 'superadmin.transactions.index', 'superadmin.transactions.*'],
            ['Pesanan Bahan', 'superadmin.purchase-orders.index', 'superadmin.purchase-orders.*'],
            ['Log Aktivitas', 'superadmin.activity-logs.index', 'superadmin.activity-logs.*'],
            ['Laporan', 'superadmin.reports.sales', 'superadmin.reports.*'],
            ['Pengaturan', 'superadmin.settings.edit', 'superadmin.settings.*'],
        ],
        'admin' => [
            ['Dashboard', 'admin.dashboard', 'admin.dashboard'],
            ['Kategori', 'admin.categories.index', 'admin.categories.*'],
            ['Menu', 'admin.products.index', 'admin.products.*'],
            ['Bahan', 'admin.ingredients.index', 'admin.ingredients.*'],
            ['Stok', 'admin.stock.index', 'admin.stock.*'],
            ['Supplier', 'admin.suppliers.index', 'admin.suppliers.*'],
            ['Pesanan Bahan', 'admin.purchase-orders.index', 'admin.purchase-orders.*'],
            ['Verifikasi', 'admin.verifications.index', 'admin.verifications.*'],
            ['Shift', 'admin.shifts.index', 'admin.shifts.*'],
            ['Laporan', 'admin.reports.sales', 'admin.reports.*'],
        ],
        'kasir' => [
            ['Shift', 'kasir.shift.index', 'kasir.shift.*'],
            ['Transaksi', 'kasir.pos', 'kasir.pos'],
            ['Riwayat', 'kasir.transactions.index', 'kasir.transactions.*'],
        ],
    ];

    $items = collect($menus[auth()->user()->role->value] ?? [])->filter(fn($m) => Route::has($m[1]));
@endphp

<nav x-data="{ open: false }" class="sticky top-0 z-40 bg-white border-b border-stone-200/90 shadow-xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid w-full h-16 grid-cols-[1fr_auto_1fr] items-center">
            <div class="flex justify-start">
                <a href="{{ route('dashboard') }}" class="focus:outline-none">
                    <x-application-logo />
                </a>
            </div>

            <div class="hidden space-x-1 lg:flex">
                @foreach ($items as [$label, $route, $pattern])
                    <x-nav-link :href="route($route)" :active="request()->routeIs($pattern)">
                        {{ $label }}
                    </x-nav-link>
                @endforeach
            </div>

            <div class="col-start-3 flex items-center justify-end gap-3">
                <a href="{{ route('profile.edit') }}" title="Profil saya" class="text-right hover:opacity-80">
                    <div class="text-xs font-semibold text-stone-800">{{ Auth::user()->name }}</div>
                    <div class="text-[11px] text-stone-500 font-medium">{{ Auth::user()->role->label() }}</div>
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Keluar" aria-label="Keluar"
                        class="inline-flex items-center justify-center p-2 rounded-xl text-stone-400 hover:text-rose-600 hover:bg-stone-100 focus:outline-none transition duration-150">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                            <path fill="currentColor"
                                d="M5 21q-.825 0-1.412-.587T3 19V5q0-.825.588-1.412T5 3h6q.425 0 .713.288T12 4t-.288.713T11 5H5v14h6q.425 0 .713.288T12 20t-.288.713T11 21zm12.175-8H10q-.425 0-.712-.288T9 12t.288-.712T10 11h7.175L15.3 9.125q-.275-.275-.275-.675t.275-.7t.7-.313t.725.288L20.3 11.3q.3.3.3.7t-.3.7l-3.575 3.575q-.3.3-.712.288t-.713-.313q-.275-.3-.262-.712t.287-.688z" />
                        </svg>
                    </button>
                </form>

                <div class="-me-2 flex items-center lg:hidden">
                    <button @click="open = ! open"
                        class="inline-flex items-center justify-center p-2 rounded-xl text-stone-500 hover:text-stone-700 hover:bg-stone-100 focus:outline-none transition duration-150">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Mobile Menu --}}
    <div x-show="open" x-transition @click.outside="open = false" style="display: none;"
        class="absolute right-4 sm:right-6 top-full mt-2 w-72 z-50 rounded-2xl lg:hidden bg-white border border-stone-200 shadow-xl max-h-[80vh] overflow-y-auto p-2">

        <div class="space-y-0.5">
            @foreach ($items as [$label, $route, $pattern])
                <x-responsive-nav-link :href="route($route)" :active="request()->routeIs($pattern)">
                    {{ $label }}
                </x-responsive-nav-link>
            @endforeach
        </div>

        <div class="mt-2 pt-3 border-t border-stone-200 px-3 pb-2 flex items-center justify-between">
            <div>
                <div class="font-semibold text-sm text-stone-800">{{ Auth::user()->name }}</div>
                <div class="text-xs text-amber-800 font-medium">{{ Auth::user()->role->label() }}</div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-rose-600 bg-rose-50 hover:bg-rose-100 transition">
                    Keluar
                </button>
            </form>
        </div>
    </div>
</nav>
