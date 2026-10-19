<x-app-layout>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-flash />

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('admin.verifications.index') }}"
                    class="bg-white border border-stone-200/90 rounded-2xl p-4 shadow-xs hover:border-amber-600 transition">
                    <p class="text-xs font-medium text-stone-500">Menunggu verifikasi</p>
                    <p class="mt-1 text-2xl font-bold text-stone-900">{{ $pendingVerification }}</p>
                </a>
                <a href="{{ route('admin.verifications.index', ['verification_status' => 'flagged']) }}"
                    class="bg-white border border-stone-200/90 rounded-2xl p-4 shadow-xs hover:border-amber-600 transition">
                    <p class="text-xs font-medium text-stone-500">Transaksi ditandai</p>
                    <p class="mt-1 text-2xl font-bold {{ $flagged > 0 ? 'text-rose-600' : 'text-stone-900' }}">
                        {{ $flagged }}</p>
                </a>
                <a href="{{ route('admin.purchase-orders.index', ['status' => 'ordered']) }}"
                    class="bg-white border border-stone-200/90 rounded-2xl p-4 shadow-xs hover:border-amber-600 transition">
                    <p class="text-xs font-medium text-stone-500">PO menunggu barang</p>
                    <p class="mt-1 text-2xl font-bold text-stone-900">{{ $orderedPo }}</p>
                </a>
                <a href="{{ route('admin.purchase-orders.index', ['status' => 'draft']) }}"
                    class="bg-white border border-stone-200/90 rounded-2xl p-4 shadow-xs hover:border-amber-600 transition">
                    <p class="text-xs font-medium text-stone-500">PO draft</p>
                    <p class="mt-1 text-2xl font-bold text-stone-900">{{ $draftPo }}</p>
                </a>
            </div>

            <div class="bg-white border border-stone-200/90 rounded-2xl p-5 shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                    <h3 class="font-bold text-stone-900">Stok Bahan Menipis</h3>
                    <a href="{{ route('admin.purchase-orders.create') }}"
                        class="text-xs font-semibold text-amber-800 hover:text-amber-900 hover:underline">
                        + Buat pesanan bahan
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="mt-2 min-w-full text-sm">
                        <thead class="text-left text-xs uppercase text-stone-400">
                            <tr>
                                <th class="py-2 font-semibold">Bahan</th>
                                <th class="py-2 text-right font-semibold">Stok Saat Ini</th>
                                <th class="py-2 text-right font-semibold">Batas Minimum</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @forelse ($lowStock as $ing)
                                <tr>
                                    <td class="py-2.5 font-medium text-stone-800">{{ $ing->name }}</td>
                                    <td
                                        class="py-2.5 text-right font-semibold {{ (float) $ing->stock <= 0 ? 'text-rose-600' : 'text-amber-700' }}">
                                        @qty($ing->stock) {{ $ing->unit }}</td>
                                    <td class="py-2.5 text-right text-stone-500">@qty($ing->min_stock) {{ $ing->unit }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-6 text-center text-stone-400">Semua stok bahan dalam
                                        kondisi aman.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
