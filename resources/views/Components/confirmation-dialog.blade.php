@props(['id', 'title' => 'Confirm Action'])

<div 
    id="{{ $id }}" 
    class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center p-4" 
    aria-modal="true" 
    role="dialog"
>
    <div 
        class="fixed inset-0 z-50 cursor-pointer"
        data-close-confirmation
    ></div>
    
    <div 
        class="bg-white rounded-lg shadow-xl w-full sm:max-w-md z-50 overflow-hidden cursor-default"
        role="dialog"
        aria-labelledby="{{ $id }}-title"
    >
        <div class="p-4 sm:p-6">
            <h2 id="{{ $id }}-title" class="text-lg font-semibold">{{ $title }}</h2>
            
            <div class="mt-3">
                {{ $slot }}
            </div>
            
            <div class="mt-5 sm:mt-6 flex justify-end space-x-3">
                <x-button 
                    variant="secondary" 
                    data-close-confirmation
                >
                    Cancel
                </x-button>
                
                {{ $actions }}
            </div>
        </div>
    </div>
</div>