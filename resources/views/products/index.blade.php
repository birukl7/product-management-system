<x-layout>
  <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div class="w-full sm:w-auto">
          <div class="relative">
              <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                  <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
              </div>
              <input 
                  type="search" 
                  id="product-search" 
                  class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-gray-500 focus:border-gray-500 sm:text-sm"
                  placeholder="Search products..."
              >
          </div>
      </div>
      
      <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
          <div class="w-full sm:w-48">
              <select 
                  id="category-filter" 
                  class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-gray-500 focus:border-gray-500 sm:text-sm rounded-md"
              >
                  <option value="">All Categories</option>
                  @foreach($categories as $category)
                      <option value="{{ $category->id }}">{{ $category->name }}</option>
                  @endforeach
              </select>
          </div>
          
          <div class="w-full sm:w-48">
              <select 
                  id="sort-products" 
                  class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-gray-500 focus:border-gray-500 sm:text-sm rounded-md"
              >
                  <option value="name_asc">Name (A-Z)</option>
                  <option value="name_desc">Name (Z-A)</option>
                  <option value="price_asc">Price (Low to High)</option>
                  <option value="price_desc">Price (High to Low)</option>
              </select>
          </div>
          
          <x-button 
              id="add-product-button"
              variant="primary"
          >
              Add Product
          </x-button>
      </div>
  </div>
  
  <div id="products-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      @foreach($products as $product)
          <x-product-card :product="$product" />
      @endforeach
  </div>
  
  <div id="pagination-container" class="mt-6">
      {{ $products->links() }}
  </div>
  
  <!-- Add/Edit Product Dialog -->
  <x-dialog id="product-dialog" title="Add Product" maxWidth="lg">
      <form id="product-form" enctype="multipart/form-data">
          @csrf
          <input type="hidden" id="product-id" name="id">
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="md:col-span-2">
                  <x-form-field>
                      <x-form-label for="product-image" required>Product Image</x-form-label>
                      <div class="mt-1 flex items-center">
                          <div id="image-preview" class="w-32 h-32 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center bg-gray-50 mb-2">
                              <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                              </svg>
                          </div>
                          <input 
                              type="file" 
                              id="product-image" 
                              name="image" 
                              class="ml-4 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200"
                          >
                      </div>
                      <div id="image-error" class="mt-1 text-sm text-red-600 hidden"></div>
                  </x-form-field>
              </div>
              
              <x-form-field>
                  <x-form-label for="product-name" required>Name</x-form-label>
                  <x-form-input 
                      type="text" 
                      id="product-name" 
                      name="name" 
                      required
                  />
                  <div id="name-error" class="mt-1 text-sm text-red-600 hidden"></div>
              </x-form-field>
              
              <x-form-field>
                  <x-form-label for="product-price" required>Price</x-form-label>
                  <div class="relative">
                      <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                          <span class="text-gray-500 sm:text-sm">$</span>
                      </div>
                      <x-form-input 
                          type="number" 
                          id="product-price" 
                          name="price" 
                          step="0.01" 
                          min="0" 
                          required
                          class="pl-7"
                      />
                  </div>
                  <div id="price-error" class="mt-1 text-sm text-red-600 hidden"></div>
              </x-form-field>
              
              <x-form-field>
                  <x-form-label for="product-category" required>Category</x-form-label>
                  <select 
                      id="product-category" 
                      name="category_id" 
                      class="block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 sm:text-sm"
                      required
                  >
                      <option value="">Select Category</option>
                      @foreach($categories as $category)
                          <option value="{{ $category->id }}">{{ $category->name }}</option>
                      @endforeach
                  </select>
                  <div id="category-error" class="mt-1 text-sm text-red-600 hidden"></div>
              </x-form-field>
              
              <x-form-field>
                  <x-form-label for="product-stock" required>Stock</x-form-label>
                  <x-form-input 
                      type="number" 
                      id="product-stock" 
                      name="stock" 
                      min="0" 
                      required
                  />
                  <div id="stock-error" class="mt-1 text-sm text-red-600 hidden"></div>
              </x-form-field>
              
              <x-form-field>
                  <x-form-label for="product-status" required>Status</x-form-label>
                  <select 
                      id="product-status" 
                      name="status" 
                      class="block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 sm:text-sm"
                      required
                  >
                      <option value="Active">Active</option>
                      <option value="Inactive">Inactive</option>
                  </select>
                  <div id="status-error" class="mt-1 text-sm text-red-600 hidden"></div>
              </x-form-field>
              
              <x-form-field class="md:col-span-2">
                  <x-form-label for="product-description" required>Description</x-form-label>
                  <textarea 
                      id="product-description" 
                      name="description" 
                      rows="3" 
                      class="block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 sm:text-sm"
                      required
                  ></textarea>
                  <div id="description-error" class="mt-1 text-sm text-red-600 hidden"></div>
              </x-form-field>
          </div>
          
          <div class="mt-5 flex justify-end space-x-3">
              <x-button 
                  type="button" 
                  variant="secondary" 
                  data-close-dialog
              >
                  Cancel
              </x-button>
              
              <x-button 
                  type="submit" 
                  variant="primary" 
                  id="save-product-button"
              >
                  Save Product
              </x-button>
          </div>
      </form>
  </x-dialog>
  
  <!-- Delete Confirmation Dialog -->
  <x-confirmation-dialog id="delete-confirmation" title="Delete Product">
      <p class="text-sm text-gray-500">
          Are you sure you want to delete this product? This action cannot be undone.
      </p>
      
      <x-slot name="actions">
          <x-button 
              variant="destructive" 
              id="confirm-delete-button"
          >
              Delete
          </x-button>
      </x-slot>
  </x-confirmation-dialog>
  
</x-layout>