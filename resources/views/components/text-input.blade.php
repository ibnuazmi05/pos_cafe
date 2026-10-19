@props(['disabled' => false])

<input @disabled($disabled) {!! $attributes->merge([
    'class' => 'block w-full rounded-xl border border-stone-300 bg-white px-3.5 py-2.5 text-sm text-stone-900
                    placeholder-stone-400 shadow-xs transition duration-150
                    hover:border-stone-400
                    focus:border-amber-600 focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:outline-none
                    disabled:cursor-not-allowed disabled:bg-stone-100 disabled:text-stone-400',
]) !!}>
