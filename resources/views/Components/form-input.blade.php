@props([
    'type' => 'text',
    'name',
    'id' => null,
    'value' => '',
    'disabled' => false,
    'error' => false
])

@php
    $id = $id ?? $name;
    $classes = 'block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 sm:text-sm';
    
    if ($error) {
        $classes .= ' border-red-500 focus:border-red-500 focus:ring-red-500';
    }
@endphp

<input 
    type="{{ $type }}"
    name="{{ $name }}"
    id="{{ $id }}"
    value="{{ $value }}"
    {{ $disabled ? 'disabled' : '' }}
    {{ $attributes->merge(['class' => $classes]) }}
>