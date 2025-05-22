<div 
    id="product-detail-dialog" 
    class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center p-4 overflow-y-auto" 
    aria-modal="true" 
    role="dialog"
>
    <div 
        class="fixed inset-0 z-50 cursor-pointer"
        data-close-detail-dialog
    ></div>
    
    <div 
        class="bg-white rounded-xl shadow-xl w-full max-w-4xl z-50 overflow-hidden cursor-default my-8"
        role="dialog"
        aria-labelledby="product-detail-title"
    >
        <div class="flex items-center justify-between p-5 border-b">
            <h2 id="product-detail-title" class="text-xl font-semibold text-gray-900">Product Details</h2>
            <div class="flex items-center gap-2">
                <button 
                    type="button" 
                    class="text-gray-400 hover:text-red-500 focus:outline-none focus:text-red-500 transition-colors"
                    id="detail-delete-button"
                    aria-label="Delete product"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
                <button 
                    type="button" 
                    class="text-gray-400 hover:text-gray-500 focus:outline-none focus:text-gray-500 transition-colors"
                    data-close-detail-dialog
                    aria-label="Close"
                >
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
        
        <div class="p-0">
            <div class="grid md:grid-cols-2 gap-0">
                <div class="bg-gray-50 p-6 flex items-center justify-center relative group">
                    <div id="detail-image-container" class="w-full max-w-md rounded-lg overflow-hidden">
                        <!-- Image will be inserted here -->
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                        <label for="detail-image-input" class="bg-white/90 rounded-full p-3 cursor-pointer hover:bg-white transition-colors shadow-sm">
                            <svg class="h-6 w-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </label>
                        <input type="file" id="detail-image-input" class="hidden" accept="image/jpeg,image/png,image/gif">
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="mb-6 relative group">
                        <div class="editable-field-container">
                            <label class="block text-sm font-medium text-gray-500 mb-1">Product Name</label>
                            <h3 id="detail-name" class="text-2xl font-bold editable-field" data-field="name"></h3>
                            <div class="absolute top-0 right-0 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button type="button" class="edit-button p-1 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-6 relative group">
                        <div class="editable-field-container">
                            <label class="block text-sm font-medium text-gray-500 mb-1">Price</label>
                            <div class="flex items-baseline">
                                <span class="text-gray-500 mr-1">ETB</span>
                                <p id="detail-price" class="text-xl font-bold text-gray-900 editable-field" data-field="price"></p>
                            </div>
                            <div class="absolute top-0 right-0 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button type="button" class="edit-button p-1 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="relative group">
                            <div class="editable-field-container">
                                <label class="block text-sm font-medium text-gray-500 mb-1">Category</label>
                                <p id="detail-category" class="editable-field" data-field="category_id"></p>
                                <div class="absolute top-0 right-0 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button type="button" class="edit-button p-1 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="relative group">
                            <div class="editable-field-container">
                                <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                                <p id="detail-status" class="editable-field" data-field="status"></p>
                                <div class="absolute top-0 right-0 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button type="button" class="edit-button p-1 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-6 relative group">
                        <div class="editable-field-container">
                            <label class="block text-sm font-medium text-gray-500 mb-1">Stock</label>
                            <p id="detail-stock" class="editable-field" data-field="stock"></p>
                            <div class="absolute top-0 right-0 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button type="button" class="edit-button p-1 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="relative group">
                        <div class="editable-field-container">
                            <label class="block text-sm font-medium text-gray-500 mb-1">Description</label>
                            <p id="detail-description" class="text-gray-700 whitespace-pre-line editable-field" data-field="description"></p>
                            <div class="absolute top-0 right-0 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button type="button" class="edit-button p-1 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="p-5 border-t flex justify-end">
            <x-button 
                type="button" 
                variant="secondary" 
                data-close-detail-dialog
            >
                Close
            </x-button>
        </div>
    </div>
</div>