@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'disabled' => false,
    'fullWidth' => false,
])

@php
    $baseClasses = 'inline-flex items-center justify-center rounded-md font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-gray-950 disabled:pointer-events-none disabled:opacity-50';
    
    $variantClasses = [
        'primary' => 'bg-gray-900 text-white hover:bg-gray-800 active:bg-gray-950',
        'secondary' => 'bg-gray-100 text-gray-900 hover:bg-gray-200 active:bg-gray-300',
        'destructive' => 'bg-red-600 text-white hover:bg-red-700 active:bg-red-800',
        'outline' => 'border border-gray-200 bg-white hover:bg-gray-100 hover:text-gray-900',
        'ghost' => 'hover:bg-gray-100 hover:text-gray-900',
        'link' => 'text-gray-900 underline-offset-4 hover:underline',
    ];
    
    $sizeClasses = [
        'sm' => 'h-8 px-3 text-xs',
        'md' => 'h-9 px-4 py-2',
        'lg' => 'h-10 px-8',
        'icon' => 'h-9 w-9',
    ];
    
    $classes = $baseClasses . ' ' . 
               ($variantClasses[$variant] ?? $variantClasses['primary']) . ' ' . 
               ($sizeClasses[$size] ?? $sizeClasses['md']) . ' ' .
               ($fullWidth ? 'w-full' : '');
@endphp

<button 
    type="{{ $type }}" 
    {{ $disabled ? 'disabled' : '' }}
    {{ $attributes->merge(['class' => $classes]) }}
>
    {{ $slot }}
</button>