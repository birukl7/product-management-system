@props([
    'name',
    'id' => null,
    'value' => '',
    'disabled' => false,
    'error' => false,
    'rows' => 3
])

@php
    $id = $id ?? $name;
    $baseClasses = 'block w-full rounded-md border px-3 py-2 text-sm transition-colors duration-200 ease-in-out';
    
    $classes = $error
        ? $baseClasses . ' border-red-500 text-red-900 placeholder-red-300 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/30'
        : $baseClasses . ' border-gray-300 text-gray-900 placeholder-gray-400 focus:border-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900/10';
    
    if ($disabled) {
        $classes .= ' bg-gray-100 cursor-not-allowed opacity-75';
    }
@endphp

<textarea
    name="{{ $name }}"
    id="{{ $id }}"
    rows="{{ $rows }}"
    {{ $disabled ? 'disabled' : '' }}
    {{ $attributes->merge(['class' => $classes]) }}
>{{ $value }}</textarea>