@props(['value'])
@php
    $raw = $value instanceof \BackedEnum ? $value->value : (string) $value;
    $label = $value instanceof \BackedEnum && method_exists($value, 'label') ? $value->label() : ucfirst($raw);
@endphp
<span {{ $attributes->merge(['class' => 'text-sm font-medium text-stone-700']) }}>{{ $label }}</span>
