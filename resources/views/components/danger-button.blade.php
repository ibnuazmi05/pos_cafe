<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-rose-600 border border-transparent rounded-xl font-medium text-sm text-white shadow-xs hover:bg-rose-700 active:bg-rose-800 focus:outline-none focus:ring-2 focus:ring-rose-500/20 disabled:opacity-40 disabled:cursor-not-allowed transition duration-150']) }}>
    {{ $slot }}
</button>
