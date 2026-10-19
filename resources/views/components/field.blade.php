@props([
    'name',
    'label',
    'type' => 'text',
    'rows' => 3,
    'value' => null,
    'required' => false,
    'step' => null,
    'min' => null,
    'hint' => null,
])
<div>
    <x-input-label :for="$name" :value="$label" />
    @if ($type === 'textarea')
        <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}"
            {{ $attributes->merge([
                'class' => 'mt-1 block w-full rounded-xl border border-stone-300 bg-white px-3.5 py-2.5 text-sm text-stone-900 
                            placeholder-stone-400 shadow-xs transition duration-150
                            hover:border-stone-400
                            focus:border-amber-600 focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:outline-none
                            disabled:cursor-not-allowed disabled:bg-stone-100 disabled:text-stone-400',
            ]) }}>{{ old($name, $value) }}</textarea>
    @else
        <x-text-input :id="$name" :name="$name" :type="$type" :step="$step" :min="$min"
            :required="$required" class="mt-1 block w-full" :value="old($name, $value)" {{ $attributes }} />
    @endif

    @if ($hint)
        <p class="mt-1 text-xs text-stone-500">{{ $hint }}</p>
    @endif
    <x-input-error :messages="$errors->get($name)" class="mt-1.5" />
</div>
