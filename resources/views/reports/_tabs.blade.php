<div class="flex gap-2 text-sm">
    @foreach (['sales' => 'Penjualan', 'purchases' => 'Pembelian Bahan', 'stock' => 'Stok'] as $key => $label)
        <a href="{{ route($prefix.'.'.$key) }}"
           class="rounded-xl px-4 py-2 border text-xs sm:text-sm font-semibold transition {{ $type === $key ? 'bg-amber-700 text-white border-amber-700 shadow-xs' : 'bg-white text-stone-600 border-stone-200/90 hover:bg-stone-50 hover:text-stone-900' }}">{{ $label }}</a>
    @endforeach
</div>
