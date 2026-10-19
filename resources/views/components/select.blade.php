@props(['name', 'label', 'options' => [], 'selected' => null, 'blank' => null, 'required' => false])
<div>
    <x-input-label :for="$name" :value="$label" />
    <select id="{{ $name }}" name="{{ $name }}" @required($required)
        class="block w-full rounded-xl border border-stone-300 bg-white px-3.5 py-2.5 text-sm text-stone-900
                    shadow-xs transition duration-150
                    hover:border-stone-400
                    focus:border-amber-600 focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:outline-none
                    disabled:cursor-not-allowed disabled:bg-stone-100 disabled:text-stone-400">
        @if ($blank !== null)
            <option value="">{{ $blank }}</option>
        @endif
        @foreach ($options as $value => $text)
            <option value="{{ $value }}" @selected((string) old($name, $selected) === (string) $value)>{{ $text }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get($name)" class="mt-1.5" />
</div>
