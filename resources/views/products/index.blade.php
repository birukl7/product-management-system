<x-layout>
  <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Products</h1>
      <p class="text-gray-600">Manage your product inventory</p>
  </div>

  <div class="mb-8 bg-white rounded-xl shadow-sm p-6">
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div class="w-full sm:w-auto relative">
              <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                  <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
              </div>
              <input 
                  type="search" 
                  id="product-search" 
                  class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-900 transition-colors sm:text-sm"
                  placeholder="Search products..."
              >
          </div>
          
          <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
              <div class="w-full sm:w-48">
                  <x-form-select id="category-filter" name="category">
                      <option value="">All Categories</option>
                      @foreach($categories as $category)
                          <option value="{{ $category->id }}">{{ $category->name }}</option>
                      @endforeach
                  </x-form-select>
              </div>
              
              <div class="w-full sm:w-48">
                  <x-form-select id="sort-products" name="sort">
                      <option value="name_asc">Name (A-Z)</option>
                      <option value="name_desc">Name (Z-A)</option>
                      <option value="price_asc">Price (Low to High)</option>
                      <option value="price_desc">Price (High to Low)</option>
                  </x-form-select>
              </div>
              
              <x-button 
                  id="add-product-button"
                  variant="primary"
                  class="px-6"
              >
                  <svg class="w-5 h-5 mr-2 -ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                  </svg>
                  Add Product
              </x-button>
          </div>
      </div>
  </div>
  
  <div id="products-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      @foreach($products as $product)
          <x-product-card :product="$product" />
      @endforeach
  </div>
  
  <div id="pagination-container" class="mt-8 flex flex-col items-center">
      <div id="pagination-links" class="hidden">
          {{ $products->links() }}
      </div>
      <button 
          id="load-more-button" 
          class="px-6 py-3 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors"
      >
          Load More Products
      </button>
      <div id="loading-indicator" class="hidden py-4">
          <svg class="animate-spin h-6 w-6 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
      </div>
      <p id="no-more-products" class="text-gray-500 mt-4 hidden">No more products to load</p>
  </div>
  
  <!-- Add/Edit Product Dialog -->
  <x-dialog id="product-dialog" title="Add Product" maxWidth="4xl">
      <form id="product-form" enctype="multipart/form-data">
          @csrf
          <input type="hidden" id="product-id" name="id">
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="md:col-span-2">
                  <x-form-field>
                      <x-form-label for="product-image" required>Product Image</x-form-label>
                      <div class="mt-2 flex items-center">
                          <div id="image-preview" class="w-40 h-40 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center bg-gray-50 mb-2">
                              <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                              </svg>
                          </div>
                          <input 
                              type="file" 
                              id="product-image" 
                              name="image" 
                              class="ml-4 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 transition-colors"
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
                          <span class="text-gray-500 sm:text-sm"> </span>
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
                  <x-form-select id="product-category" name="category_id" required>
                      <option value="">Select Category</option>
                      @foreach($categories as $category)
                          <option value="{{ $category->id }}">{{ $category->name }}</option>
                      @endforeach
                  </x-form-select>
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
                  <x-form-select id="product-status" name="status" required>
                      <option value="Active">Active</option>
                      <option value="Inactive">Inactive</option>
                  </x-form-select>
                  <div id="status-error" class="mt-1 text-sm text-red-600 hidden"></div>
              </x-form-field>
              
              <x-form-field class="md:col-span-2">
                  <x-form-label for="product-description" required>Description</x-form-label>
                  <x-form-textarea id="product-description" name="description" rows="4" required />
                  <div id="description-error" class="mt-1 text-sm text-red-600 hidden"></div>
              </x-form-field>
          </div>
          
          <div class="mt-6 flex justify-end space-x-3">
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
  
  <!-- Product Detail Dialog -->
  <x-product-detail-dialog />
  
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
  
  <!-- Toast Container -->
  <div id="toast-container" class="fixed top-4 right-4 z-50 flex flex-col gap-2"></div>
</x-layout>