@props([
    'name',
    'id' => null,
    'disabled' => false,
    'error' => false
])

@php
    $id = $id ?? $name;
    $baseClasses = 'block w-full rounded-md border px-3 py-2 pr-8 text-sm transition-colors duration-200 ease-in-out appearance-none bg-no-repeat';
    
    $classes = $error
        ? $baseClasses . ' border-red-500 text-red-900 placeholder-red-300 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/30'
        : $baseClasses . ' border-gray-300 text-gray-900 placeholder-gray-400 focus:border-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900/10';
    
    if ($disabled) {
        $classes .= ' bg-gray-100 cursor-not-allowed opacity-75';
    }
@endphp

<div class="relative">
    <select
        name="{{ $name }}"
        id="{{ $id }}"
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->merge(['class' => $classes]) }}
    >
        {{ $slot }}
    </select>
    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
        <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
            <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z" />
        </svg>
    </div>
</div>