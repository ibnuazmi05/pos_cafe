<button
    {{ $attributes->merge([
        'type' => 'submit',
        'class' => 'inline-flex items-center justify-center gap-2 rounded-xl bg-amber-700
                    px-5 py-2.5 text-sm font-semibold text-white shadow-xs
                    transition duration-150 hover:bg-amber-800 active:bg-amber-900
                    focus:outline-hidden focus:ring-2 focus:ring-amber-500/20
                    disabled:opacity-50 disabled:cursor-not-allowed',
    ]) }}>
    {{ $slot }}
</button>
