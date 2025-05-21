document.addEventListener('DOMContentLoaded', function() {
  // Only initialize if we're on the products page
  if (!document.getElementById('products-container')) {
      return;
  }

  // State management
  const state = {
      search: '',
      category: '',
      sort: 'name_asc',
      page: 1,
      productIdToDelete: null,
      isEditing: false,
      debounceTimeout: null,
      currentProductDetail: null,
      categories: {}
  };
  
  // Cache categories for inline editing
  const categoryElements = document.querySelectorAll('#category-filter option');
  categoryElements.forEach(option => {
      if (option.value) {
          state.categories[option.value] = option.textContent;
      }
  });
  
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
  const productDetailDialog = document.getElementById('product-detail-dialog');
  const detailDeleteButton = document.getElementById('detail-delete-button');
  
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
  detailDeleteButton.addEventListener('click', openDeleteConfirmationFromDetail);
  
  // Delegate event listeners for dynamic elements
  document.addEventListener('click', function(e) {
      // Product card click
      const productCard = e.target.closest('[data-product-id]');
      if (productCard && !e.target.closest('button')) {
          const productId = productCard.getAttribute('data-product-id');
          openProductDetailDialog(productId);
      }
      
      // Edit product button
      if (e.target.closest('[data-edit-product]')) {
          e.stopPropagation(); // Prevent card click
          const productId = e.target.closest('[data-edit-product]').getAttribute('data-edit-product');
          openEditProductDialog(productId);
      }
      
      // Delete product button
      if (e.target.closest('[data-delete-product]')) {
          e.stopPropagation(); // Prevent card click
          const productId = e.target.closest('[data-delete-product]').getAttribute('data-delete-product');
          openDeleteConfirmation(productId);
      }
      
      // Close dialog buttons
      if (e.target.closest('[data-close-dialog]')) {
          closeDialog(productDialog);
      }
      
      // Close detail dialog buttons
      if (e.target.closest('[data-close-detail-dialog]')) {
          closeDialog(productDetailDialog);
      }
      
      // Close confirmation buttons
      if (e.target.closest('[data-close-confirmation]')) {
          closeDialog(deleteConfirmation);
      }
      
      // Edit field buttons in detail dialog
      if (e.target.closest('.edit-button')) {
          const container = e.target.closest('.editable-field-container');
          const field = container.querySelector('.editable-field');
          makeFieldEditable(field);
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
  
  // Add event listener for image update in detail view
  document.addEventListener('change', function(e) {
      if (e.target.id === 'detail-image-input') {
          updateProductImage(e.target.files[0]);
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
  
  function updateProductImage(file) {
      if (!file || !state.currentProductDetail) return;
      
      // Validate file type
      const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
      if (!validTypes.includes(file.type)) {
          showToast('Please select a valid image file (JPEG, PNG, GIF).', 'error');
          return;
      }
      
      // Validate file size (max 2MB)
      if (file.size > 2 * 1024 * 1024) {
          showToast('Image size should not exceed 2MB.', 'error');
          return;
      }
      
      // Show loading state
      const imageContainer = document.getElementById('detail-image-container');
      imageContainer.innerHTML = '<div class="flex items-center justify-center p-12"><svg class="animate-spin h-8 w-8 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></div>';
      
      // Create form data
      const formData = new FormData();
      formData.append('_method', 'PUT');
      formData.append('image', file);
      
      // Send update request
      fetch(`/products/${state.currentProductDetail.id}`, {
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
              // Update image in detail view with cache-busting query parameter
              imageContainer.innerHTML = `<img src="/storage/${data.product.image}?t=${new Date().getTime()}" class="w-full h-auto rounded-lg shadow-sm" alt="${data.product.name}">`;
              
              // Update state
              state.currentProductDetail.image = data.product.image;
              
              // Show success message
              showToast('Product image updated successfully!', 'success');
              
              // Update products list
              updateProducts();
          } else {
              showToast('Failed to update product image. Please try again.', 'error');
              
              // Restore previous image
              if (state.currentProductDetail.image) {
                  imageContainer.innerHTML = `<img src="/storage/${state.currentProductDetail.image}" class="w-full h-auto rounded-lg shadow-sm" alt="${state.currentProductDetail.name}">`;
              } else {
                  imageContainer.innerHTML = `
                      <div class="w-full aspect-square bg-gray-100 rounded-lg flex items-center justify-center">
                          <svg class="h-24 w-24 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                          </svg>
                      </div>
                  `;
              }
          }
      })
      .catch(error => {
          console.error('Error updating product image:', error);
          showToast('Failed to update product image. Please try again.', 'error');
          
          // Restore previous image
          if (state.currentProductDetail.image) {
              imageContainer.innerHTML = `<img src="/storage/${state.currentProductDetail.image}" class="w-full h-auto rounded-lg shadow-sm" alt="${state.currentProductDetail.name}">`;
          } else {
              imageContainer.innerHTML = `
                  <div class="w-full aspect-square bg-gray-100 rounded-lg flex items-center justify-center">
                      <svg class="h-24 w-24 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                  </div>
              `;
          }
      });
  }
  
  function openProductDetailDialog(productId) {
      // Show loading state
      const detailFields = productDetailDialog.querySelectorAll('.editable-field');
      detailFields.forEach(field => {
          field.textContent = 'Loading...';
      });
      
      const imageContainer = document.getElementById('detail-image-container');
      imageContainer.innerHTML = '<div class="flex items-center justify-center p-12"><svg class="animate-spin h-8 w-8 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></div>';
      
      // Fetch product data
      fetch(`/products/${productId}/edit`, {
          headers: {
              'X-Requested-With': 'XMLHttpRequest'
          }
      })
      .then(response => response.json())
      .then(product => {
          state.currentProductDetail = product;
          
          // Update detail fields
          document.getElementById('detail-name').textContent = product.name;
          document.getElementById('detail-price').textContent = parseFloat(product.price).toFixed(2);
          document.getElementById('detail-category').textContent = state.categories[product.category_id] || 'Uncategorized';
          document.getElementById('detail-status').textContent = product.status;
          document.getElementById('detail-stock').textContent = product.stock;
          document.getElementById('detail-description').textContent = product.description;
          
          // Add data attributes for inline editing
          document.getElementById('detail-category').setAttribute('data-value', product.category_id);
          document.getElementById('detail-status').setAttribute('data-value', product.status);
          
          // Update image
          if (product.image) {
              imageContainer.innerHTML = `<img src="/storage/${product.image}" class="w-full h-auto rounded-lg shadow-sm" alt="${product.name}">`;
          } else {
              imageContainer.innerHTML = `
                  <div class="w-full aspect-square bg-gray-100 rounded-lg flex items-center justify-center">
                      <svg class="h-24 w-24 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                  </div>
              `;
          }
          
          openDialog(productDetailDialog);
      })
      .catch(error => {
          console.error('Error fetching product:', error);
          showToast('Failed to load product details. Please try again.', 'error');
      });
  }
  
  function makeFieldEditable(field) {
      const fieldName = field.getAttribute('data-field');
      const currentValue = fieldName === 'category_id' ? field.getAttribute('data-value') : field.textContent;
      const container = field.closest('.editable-field-container');
      const editButton = container.querySelector('.edit-button');
      
      // Hide edit button
      editButton.style.display = 'none';
      
      // Create input element based on field type
      let inputElement;
      
      if (fieldName === 'description') {
          inputElement = document.createElement('textarea');
          inputElement.rows = 3;
          inputElement.className = 'block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900/10 transition-colors';
          inputElement.value = currentValue;
      } else if (fieldName === 'category_id') {
          inputElement = document.createElement('select');
          inputElement.className = 'block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900/10 transition-colors';
          
          // Add options
          Object.keys(state.categories).forEach(id => {
              const option = document.createElement('option');
              option.value = id;
              option.textContent = state.categories[id];
              if (id == currentValue) option.selected = true;
              inputElement.appendChild(option);
          });
      } else if (fieldName === 'status') {
          inputElement = document.createElement('select');
          inputElement.className = 'block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900/10 transition-colors';
          
          // Add options
          ['Active', 'Inactive'].forEach(status => {
              const option = document.createElement('option');
              option.value = status;
              option.textContent = status;
              if (status === currentValue) option.selected = true;
              inputElement.appendChild(option);
          });
      } else if (fieldName === 'price') {
          inputElement = document.createElement('input');
          inputElement.type = 'number';
          inputElement.step = '0.01';
          inputElement.min = '0';
          inputElement.className = 'block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900/10 transition-colors';
          inputElement.value = currentValue;
      } else if (fieldName === 'stock') {
          inputElement = document.createElement('input');
          inputElement.type = 'number';
          inputElement.min = '0';
          inputElement.className = 'block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900/10 transition-colors';
          inputElement.value = currentValue;
      } else {
          inputElement = document.createElement('input');
          inputElement.type = 'text';
          inputElement.className = 'block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900/10 transition-colors';
          inputElement.value = currentValue;
      }
      
      // Add data attribute to identify field
      inputElement.setAttribute('data-field', fieldName);
      
      // Create save button
      const saveButton = document.createElement('button');
      saveButton.type = 'button';
      saveButton.className = 'mt-2 inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-gray-900 hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 transition-colors';
      saveButton.innerHTML = 'Save';
      
      // Create cancel button
      const cancelButton = document.createElement('button');
      cancelButton.type = 'button';
      cancelButton.className = 'mt-2 ml-2 inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors';
      cancelButton.innerHTML = 'Cancel';
      
      // Create button container
      const buttonContainer = document.createElement('div');
      buttonContainer.className = 'flex';
      buttonContainer.appendChild(saveButton);
      buttonContainer.appendChild(cancelButton);
      
      // Hide original field
      field.style.display = 'none';
      
      // Add input and buttons after field
      field.insertAdjacentElement('afterend', inputElement);
      inputElement.insertAdjacentElement('afterend', buttonContainer);
      
      // Focus input
      inputElement.focus();
      
      // Add event listeners
      saveButton.addEventListener('click', function() {
          saveInlineEdit(field, inputElement);
      });
      
      cancelButton.addEventListener('click', function() {
          cancelInlineEdit(field, inputElement, buttonContainer, editButton);
      });
      
      // Handle Enter key for inputs
      inputElement.addEventListener('keydown', function(e) {
          if (e.key === 'Enter' && fieldName !== 'description') {
              e.preventDefault();
              saveInlineEdit(field, inputElement);
          }
      });
  }
  
  function saveInlineEdit(field, inputElement) {
      const fieldName = field.getAttribute('data-field');
      const newValue = inputElement.value;
      const container = field.closest('.editable-field-container');
      const editButton = container.querySelector('.edit-button');
      const buttonContainer = inputElement.nextElementSibling;
      
      // Show loading state
      buttonContainer.innerHTML = '<div class="mt-2 flex items-center"><svg class="animate-spin h-4 w-4 text-gray-500 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Saving...</div>';
      
      // Prepare data for update
      const data = new FormData();
      data.append('_method', 'PUT');
      data.append(fieldName, newValue);
      
      // Send update request
      fetch(`/products/${state.currentProductDetail.id}`, {
          method: 'POST',
          body: data,
          headers: {
              'X-CSRF-TOKEN': csrfToken,
              'X-Requested-With': 'XMLHttpRequest'
          }
      })
      .then(response => response.json())
      .then(data => {
          if (data.success) {
              // Update field display
              if (fieldName === 'category_id') {
                  field.textContent = state.categories[newValue];
                  field.setAttribute('data-value', newValue);
              } else if (fieldName === 'price') {
                  field.textContent = parseFloat(newValue).toFixed(2);
              } else {
                  field.textContent = newValue;
              }
              
              // Update state
              state.currentProductDetail[fieldName] = newValue;
              
              // Show success message
              showToast('Product updated successfully!', 'success');
              
              // Update products list
              updateProducts();
          } else {
              showToast('Failed to update product. Please try again.', 'error');
          }
      })
      .catch(error => {
          console.error('Error updating product:', error);
          showToast('Failed to update product. Please try again.', 'error');
      })
      .finally(() => {
          // Restore field display
          field.style.display = '';
          editButton.style.display = '';
          inputElement.remove();
          buttonContainer.remove();
      });
  }
  
  function cancelInlineEdit(field, inputElement, buttonContainer, editButton) {
      // Restore field display
      field.style.display = '';
      editButton.style.display = '';
      inputElement.remove();
      buttonContainer.remove();
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
  
  function openDeleteConfirmationFromDetail() {
      if (state.currentProductDetail) {
          state.productIdToDelete = state.currentProductDetail.id;
          closeDialog(productDetailDialog);
          openDialog(deleteConfirmation);
      }
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