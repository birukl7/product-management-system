@props(['product'])

<div 
    class="bg-white rounded-xl shadow-md overflow-hidden transition-all duration-300 hover:shadow-lg hover:translate-y-[-4px] cursor-pointer group"
    data-product-id="{{ $product->id }}"
>
    <div class="aspect-w-16 aspect-h-12 bg-gray-100 overflow-hidden">
        @if($product->image)
            <img 
                src="{{ asset('storage/' . $product->image) }}" 
                alt="{{ $product->name }}" 
                class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
            >
        @else
            <div class="w-full h-full flex items-center justify-center bg-gray-50">
                <svg class="h-16 w-16 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        @endif
        <div class="absolute top-2 right-2">
            <span 
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $product->status === 'Active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}"
                data-product-status
            >
                {{ $product->status }}
            </span>
        </div>
    </div>
    
    <div class="p-4">
        <h3 class="text-lg font-semibold line-clamp-1 group-hover:text-gray-700" data-product-name>{{ $product->name }}</h3>
        
        <div class="mt-2 flex items-center justify-between">
            <p class="text-xl font-bold text-gray-900" data-product-price>ETB {{ number_format($product->price, 2) }}</p>
            <div class="text-sm text-gray-500">
                <span data-product-stock>{{ $product->stock }}</span> in stock
            </div>
        </div>
        
        <div class="mt-3 flex items-center">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-800">
                {{ $product->category->name ?? 'Uncategorized' }}
            </span>
        </div>
    </div>
</div>