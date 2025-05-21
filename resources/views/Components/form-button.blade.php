@props([
    'type' => 'submit',
    'variant' => 'primary',
    'size' => 'md',
    'disabled' => false,
    'fullWidth' => false,
])

<x-button 
    :type="$type" 
    :variant="$variant" 
    :size="$size" 
    :disabled="$disabled" 
    :fullWidth="$fullWidth"
    {{ $attributes }}
>
    {{ $slot }}
</x-button>