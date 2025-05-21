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
  
  @slot('scripts')
  <script>
      document.addEventListener('DOMContentLoaded', function() {
          // State management
          const state = {
              search: '',
              category: '',
              sort: 'name_asc',
              page: 1,
              productIdToDelete: null,
              isEditing: false,
              debounceTimeout: null
          };
          
          // DOM Elements
          const searchInput = document.getElementById('product-search');
          const categoryFilter = document.getElementById('category-filter');
          const sortSelect = document.getElementById('sort-products');
          const productsContainer = document.getElementById('products-container');
          const paginationContainer = document.getElementById('pagination-container');
          const addProductButton = document.getElementById('add-product-button');
          const productDialog = document.getElementById('product-dialog');
          const productForm = document.getElementById('product-form');
          const productIdInput = document.getElementById('product-id');
          const productImageInput = document.getElementById('product-image');
          const imagePreview = document.getElementById('image-preview');
          const deleteConfirmation = document.getElementById('delete-confirmation');
          const confirmDeleteButton = document.getElementById('confirm-delete-button');
          const dialogTitle = productDialog.querySelector('h2');
          
          // CSRF Token
          const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
          
          // Event Listeners
          searchInput.addEventListener('input', debounceSearch);
          categoryFilter.addEventListener('change', updateProducts);
          sortSelect.addEventListener('change', updateProducts);
          addProductButton.addEventListener('click', openAddProductDialog);
          productForm.addEventListener('submit', saveProduct);
          productImageInput.addEventListener('change', previewImage);
          confirmDeleteButton.addEventListener('click', deleteProduct);
          
          // Delegate event listeners for dynamic elements
          document.addEventListener('click', function(e) {
              // Edit product button
              if (e.target.closest('[data-edit-product]')) {
                  const productId = e.target.closest('[data-edit-product]').getAttribute('data-edit-product');
                  openEditProductDialog(productId);
              }
              
              // Delete product button
              if (e.target.closest('[data-delete-product]')) {
                  const productId = e.target.closest('[data-delete-product]').getAttribute('data-delete-product');
                  openDeleteConfirmation(productId);
              }
              
              // Close dialog buttons
              if (e.target.closest('[data-close-dialog]')) {
                  closeDialog(productDialog);
              }
              
              // Close confirmation buttons
              if (e.target.closest('[data-close-confirmation]')) {
                  closeDialog(deleteConfirmation);
              }
              
              // Pagination links
              if (e.target.closest('.pagination a')) {
                  e.preventDefault();
                  const href = e.target.closest('.pagination a').getAttribute('href');
                  const url = new URL(href);
                  state.page = url.searchParams.get('page') || 1;
                  updateProducts();
              }
          });
          
          // Functions
          function debounceSearch() {
              clearTimeout(state.debounceTimeout);
              state.debounceTimeout = setTimeout(() => {
                  state.search = searchInput.value;
                  state.page = 1; // Reset to first page on search
                  updateProducts();
              }, 300);
          }
          
          function updateProducts() {
              state.category = categoryFilter.value;
              state.sort = sortSelect.value;
              
              // Show loading state
              productsContainer.innerHTML = '<div class="col-span-full flex justify-center py-12"><svg class="animate-spin h-8 w-8 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></div>';
              
              // Build query string
              const params = new URLSearchParams();
              if (state.search) params.append('search', state.search);
              if (state.category) params.append('category', state.category);
              if (state.sort) params.append('sort', state.sort);
              if (state.page) params.append('page', state.page);
              
              // Fetch products
              fetch(`/products/list?${params.toString()}`, {
                  headers: {
                      'X-Requested-With': 'XMLHttpRequest'
                  }
              })
              .then(response => response.json())
              .then(data => {
                  productsContainer.innerHTML = data.products;
                  paginationContainer.innerHTML = data.pagination;
                  
                  // Save state to localStorage
                  localStorage.setItem('productFilters', JSON.stringify({
                      search: state.search,
                      category: state.category,
                      sort: state.sort
                  }));
              })
              .catch(error => {
                  console.error('Error fetching products:', error);
                  showToast('Failed to load products. Please try again.', 'error');
                  productsContainer.innerHTML = '<div class="col-span-full text-center py-12 text-gray-500">Failed to load products. Please try again.</div>';
              });
          }
          
          function openAddProductDialog() {
              state.isEditing = false;
              dialogTitle.textContent = 'Add Product';
              productForm.reset();
              productIdInput.value = '';
              resetFormErrors();
              resetImagePreview();
              openDialog(productDialog);
          }
          
          function openEditProductDialog(productId) {
              state.isEditing = true;
              dialogTitle.textContent = 'Edit Product';
              resetFormErrors();
              
              // Show loading state
              const formFields = productForm.querySelectorAll('input, select, textarea');
              formFields.forEach(field => field.disabled = true);
              
              // Fetch product data
              fetch(`/products/${productId}/edit`, {
                  headers: {
                      'X-Requested-With': 'XMLHttpRequest'
                  }
              })
              .then(response => response.json())
              .then(product => {
                  productIdInput.value = product.id;
                  document.getElementById('product-name').value = product.name;
                  document.getElementById('product-price').value = product.price;
                  document.getElementById('product-description').value = product.description;
                  document.getElementById('product-category').value = product.category_id;
                  document.getElementById('product-stock').value = product.stock;
                  document.getElementById('product-status').value = product.status;
                  
                  // Update image preview if exists
                  if (product.image) {
                      imagePreview.innerHTML = `<img src="/storage/${product.image}" class="w-full h-full object-cover">`;
                  } else {
                      resetImagePreview();
                  }
                  
                  // Enable form fields
                  formFields.forEach(field => field.disabled = false);
                  
                  openDialog(productDialog);
              })
              .catch(error => {
                  console.error('Error fetching product:', error);
                  showToast('Failed to load product data. Please try again.', 'error');
              });
          }
          
          function saveProduct(e) {
              e.preventDefault();
              
              // Disable submit button to prevent double submission
              const saveButton = document.getElementById('save-product-button');
              saveButton.disabled = true;
              saveButton.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Saving...';
              
              // Reset previous errors
              resetFormErrors();
              
              // Create FormData object
              const formData = new FormData(productForm);
              
              // Determine URL based on whether we're adding or editing
              const url = state.isEditing 
                  ? `/products/${productIdInput.value}` 
                  : '/products';
              
              // Add method override for PUT if editing
              if (state.isEditing) {
                  formData.append('_method', 'PUT');
              }
              
              // Send request
              fetch(url, {
                  method: 'POST',
                  body: formData,
                  headers: {
                      'X-CSRF-TOKEN': csrfToken,
                      'X-Requested-With': 'XMLHttpRequest'
                  }
              })
              .then(response => response.json())
              .then(data => {
                  if (data.success) {
                      closeDialog(productDialog);
                      updateProducts();
                      showToast(data.message, 'success');
                  } else {
                      // Display validation errors
                      if (data.errors) {
                          Object.keys(data.errors).forEach(field => {
                              const errorElement = document.getElementById(`${field}-error`);
                              if (errorElement) {
                                  errorElement.textContent = data.errors[field][0];
                                  errorElement.classList.remove('hidden');
                              }
                          });
                      }
                      showToast('Please correct the errors in the form.', 'error');
                  }
              })
              .catch(error => {
                  console.error('Error saving product:', error);
                  showToast('Failed to save product. Please try again.', 'error');
              })
              .finally(() => {
                  // Re-enable submit button
                  saveButton.disabled = false;
                  saveButton.textContent = 'Save Product';
              });
          }
          
          function openDeleteConfirmation(productId) {
              state.productIdToDelete = productId;
              openDialog(deleteConfirmation);
          }
          
          function deleteProduct() {
              if (!state.productIdToDelete) return;
              
              // Disable delete button
              confirmDeleteButton.disabled = true;
              confirmDeleteButton.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Deleting...';
              
              fetch(`/products/${state.productIdToDelete}`, {
                  method: 'DELETE',
                  headers: {
                      'X-CSRF-TOKEN': csrfToken,
                      'X-Requested-With': 'XMLHttpRequest'
                  }
              })
              .then(response => response.json())
              .then(data => {
                  closeDialog(deleteConfirmation);
                  if (data.success) {
                      updateProducts();
                      showToast(data.message, 'success');
                  } else {
                      showToast(data.message || 'Failed to delete product.', 'error');
                  }
              })
              .catch(error => {
                  console.error('Error deleting product:', error);
                  showToast('Failed to delete product. Please try again.', 'error');
              })
              .finally(() => {
                  // Reset delete button
                  confirmDeleteButton.disabled = false;
                  confirmDeleteButton.textContent = 'Delete';
                  state.productIdToDelete = null;
              });
          }
          
          function previewImage(e) {
              const file = e.target.files[0];
              if (!file) {
                  resetImagePreview();
                  return;
              }
              
              // Validate file type
              const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
              if (!validTypes.includes(file.type)) {
                  document.getElementById('image-error').textContent = 'Please select a valid image file (JPEG, PNG, GIF).';
                  document.getElementById('image-error').classList.remove('hidden');
                  resetImagePreview();
                  return;
              }
              
              // Validate file size (max 2MB)
              if (file.size > 2 * 1024 * 1024) {
                  document.getElementById('image-error').textContent = 'Image size should not exceed 2MB.';
                  document.getElementById('image-error').classList.remove('hidden');
                  resetImagePreview();
                  return;
              }
              
              // Preview image
              const reader = new FileReader();
              reader.onload = function(e) {
                  imagePreview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
              };
              reader.readAsDataURL(file);
          }
          
          function resetImagePreview() {
              imagePreview.innerHTML = `
                  <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
              `;
          }
          
          function resetFormErrors() {
              const errorElements = document.querySelectorAll('[id$="-error"]');
              errorElements.forEach(el => {
                  el.textContent = '';
                  el.classList.add('hidden');
              });
          }
          
          function openDialog(dialog) {
              dialog.classList.remove('hidden');
              dialog.classList.add('flex');
              
              // Trap focus inside dialog
              const focusableElements = dialog.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
              const firstElement = focusableElements[0];
              const lastElement = focusableElements[focusableElements.length - 1];
              
              // Focus first element
              setTimeout(() => {
                  firstElement.focus();
              }, 100);
              
              // Add keydown event for tab trap and escape key
              dialog.addEventListener('keydown', function(e) {
                  // Close on escape
                  if (e.key === 'Escape') {
                      closeDialog(dialog);
                      return;
                  }
                  
                  // Trap focus
                  if (e.key === 'Tab') {
                      if (e.shiftKey && document.activeElement === firstElement) {
                          e.preventDefault();
                          lastElement.focus();
                      } else if (!e.shiftKey && document.activeElement === lastElement) {
                          e.preventDefault();
                          firstElement.focus();
                      }
                  }
              });
              
              // Prevent body scroll
              document.body.style.overflow = 'hidden';
          }
          
          function closeDialog(dialog) {
              dialog.classList.add('hidden');
              dialog.classList.remove('flex');
              
              // Re-enable body scroll
              document.body.style.overflow = '';
          }
          
          function showToast(message, type = 'success') {
              const toastContainer = document.getElementById('toast-container');
              const toast = document.createElement('div');
              
              toast.className = `px-4 py-3 rounded-lg shadow-md flex items-center justify-between ${
                  type === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : 
                  type === 'error' ? 'bg-red-50 text-red-800 border border-red-200' : 
                  'bg-blue-50 text-blue-800 border border-blue-200'
              }`;
              
              toast.innerHTML = `
                  <div class="flex items-center">
                      <svg class="w-5 h-5 mr-2 ${
                          type === 'success' ? 'text-green-500' : 
                          type === 'error' ? 'text-red-500' : 
                          'text-blue-500'
                      }" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                          ${
                              type === 'success' ? 
                              '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>' : 
                              type === 'error' ? 
                              '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>' : 
                              '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v4a1 1 0 102 0V7zm0 6a1 1 0 10-2 0 1 1 0 102 0z" clip-rule="evenodd"></path>'
                          }
                      </svg>
                      <span>${message}</span>
                  </div>
                  <button type="button" class="ml-4 text-gray-400 hover:text-gray-600 focus:outline-none" onclick="this.parentElement.remove()">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                      </svg>
                  </button>
              `;
              
              toastContainer.appendChild(toast);
              
              // Auto remove after 5 seconds
              setTimeout(() => {
                  toast.classList.add('opacity-0', 'transition-opacity', 'duration-300');
                  setTimeout(() => {
                      toast.remove();
                  }, 300);
              }, 5000);
          }
          
          // Load saved filters from localStorage
          function loadSavedFilters() {
              const savedFilters = localStorage.getItem('productFilters');
              if (savedFilters) {
                  try {
                      const filters = JSON.parse(savedFilters);
                      
                      if (filters.search) {
                          searchInput.value = filters.search;
                          state.search = filters.search;
                      }
                      
                      if (filters.category) {
                          categoryFilter.value = filters.category;
                          state.category = filters.category;
                      }
                      
                      if (filters.sort) {
                          sortSelect.value = filters.sort;
                          state.sort = filters.sort;
                      }
                      
                      // Only update if we have saved filters
                      if (filters.search || filters.category || filters.sort) {
                          updateProducts();
                      }
                  } catch (e) {
                      console.error('Error parsing saved filters:', e);
                  }
              }
          }
          
          // Initialize
          loadSavedFilters();
      });
  </script>
  @endslot
</x-layout>