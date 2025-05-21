@props(['id', 'title', 'maxWidth' => 'md'])

@php
    $maxWidthClass = [
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
    ][$maxWidth];
@endphp

<div 
    id="{{ $id }}" 
    class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center p-4" 
    aria-modal="true" 
    role="dialog"
>
    <div 
        class="fixed inset-0 z-50 cursor-pointer"
        data-close-dialog
    ></div>
    
    <div 
        class="bg-white rounded-lg shadow-xl w-full {{ $maxWidthClass }} z-50 overflow-hidden cursor-default"
        role="dialog"
        aria-labelledby="{{ $id }}-title"
    >
        <div class="flex items-center justify-between p-4 border-b">
            <h2 id="{{ $id }}-title" class="text-lg font-semibold">{{ $title }}</h2>
            <button 
                type="button" 
                class="text-gray-400 hover:text-gray-500 focus:outline-none focus:text-gray-500"
                data-close-dialog
                aria-label="Close"
            >
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <div class="p-4">
            {{ $slot }}
        </div>
    </div>
</div>