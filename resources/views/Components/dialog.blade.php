@props(['id', 'title', 'maxWidth' => 'md'])

@php
    $maxWidthClass = [
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
        '3xl' => 'sm:max-w-3xl',
        '4xl' => 'sm:max-w-4xl',
        '5xl' => 'sm:max-w-5xl',
        '6xl' => 'sm:max-w-6xl',
        '7xl' => 'sm:max-w-7xl',
    ][$maxWidth];
@endphp

<div 
    id="{{ $id }}" 
    class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center p-4 overflow-y-auto" 
    aria-modal="true" 
    role="dialog"
>
    <div 
        class="fixed inset-0 z-50 cursor-pointer"
        data-close-dialog
    ></div>
    
    <div 
        class="bg-white rounded-xl shadow-xl w-full {{ $maxWidthClass }} z-50 overflow-hidden cursor-default my-8"
        role="dialog"
        aria-labelledby="{{ $id }}-title"
    >
        <div class="flex items-center justify-between p-5 border-b">
            <h2 id="{{ $id }}-title" class="text-xl font-semibold text-gray-900">{{ $title }}</h2>
            <button 
                type="button" 
                class="text-gray-400 hover:text-gray-500 focus:outline-none focus:text-gray-500 transition-colors"
                data-close-dialog
                aria-label="Close"
            >
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <div class="p-5">
            {{ $slot }}
        </div>
    </div>
</div>