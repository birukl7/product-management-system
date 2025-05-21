@props(['product'])

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="aspect-w-16 aspect-h-9 bg-gray-200">
        @if($product->image)
            <img 
                src="{{ asset('storage/' . $product->image) }}" 
                alt="{{ $product->name }}" 
                class="w-full h-full object-cover"
            >
        @else
            <div class="w-full h-full flex items-center justify-center bg-gray-100">
                <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        @endif
    </div>
    
    <div class="p-4">
        <div class="flex justify-between items-start">
            <h3 class="text-lg font-semibold line-clamp-1" data-product-name>{{ $product->name }}</h3>
            <div class="flex space-x-2">
                <button 
                    type="button" 
                    class="text-gray-400 hover:text-gray-500"
                    data-edit-product="{{ $product->id }}"
                    aria-label="Edit product"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                </button>
                <button 
                    type="button" 
                    class="text-gray-400 hover:text-red-500"
                    data-delete-product="{{ $product->id }}"
                    aria-label="Delete product"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>
        </div>
        
        <div class="mt-2 flex items-center justify-between">
            <p class="text-lg font-bold text-gray-900" data-product-price>${{ number_format($product->price, 2) }}</p>
            <div>
                <span 
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $product->status === 'Active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}"
                    data-product-status
                >
                    {{ $product->status }}
                </span>
            </div>
        </div>
        
        <div class="mt-2">
            <p class="text-sm text-gray-500 line-clamp-2" data-product-description>{{ $product->description }}</p>
        </div>
        
        <div class="mt-3 flex items-center justify-between">
            <div>
                <span class="text-xs text-gray-500">Category:</span>
                <span class="text-sm font-medium ml-1" data-product-category>{{ $product->category->name ?? 'Uncategorized' }}</span>
            </div>
            <div>
                <span class="text-xs text-gray-500">Stock:</span>
                <span class="text-sm font-medium ml-1" data-product-stock>{{ $product->stock }}</span>
            </div>
        </div>
    </div>
</div>