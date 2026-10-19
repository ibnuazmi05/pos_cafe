@props(['value'])

<label {{ $attributes->merge(['class' => 'block mb-1.5 ml-1 text-sm font-semibold text-gray-700']) }}>
    {{ $value ?? $slot }}
</label>
