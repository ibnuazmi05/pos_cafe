<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-white border border-stone-300 rounded-xl font-medium text-sm text-stone-700 shadow-xs hover:bg-stone-50 hover:text-stone-900 focus:outline-none focus:ring-2 focus:ring-amber-500/20 disabled:opacity-40 disabled:cursor-not-allowed transition duration-150']) }}>
    {{ $slot }}
</button>
