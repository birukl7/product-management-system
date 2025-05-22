document.addEventListener('DOMContentLoaded', function() {
  // Only initialize if we're on the products page
  if (!document.getElementById('products-container')) {
      return;
  }

  // ProductState class
  class ProductState {
      constructor() {
          this.search = '';
          this.category = '';
          this.sort = 'name_asc';
          this.page = 1;
          this.lastPage = 1;
          this.loadingMore = false;
          this.hasMoreProducts = true;
          this.productIdToDelete = null;
          this.isEditing = false;
          this.debounceTimeout = null;
          this.currentProductDetail = null;
          this.categories = {};
          this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
          
          // Cache categories for inline editing
          const categoryElements = document.querySelectorAll('#category-filter option');
          categoryElements.forEach(option => {
              if (option.value) {
                  this.categories[option.value] = option.textContent;
              }
          });
      }
      
      saveFilters() {
          localStorage.setItem('productFilters', JSON.stringify({
              search: this.search,
              category: this.category,
              sort: this.sort
          }));
      }
      
      loadFilters() {
          const savedFilters = localStorage.getItem('productFilters');
          if (savedFilters) {
              try {
                  return JSON.parse(savedFilters);
              } catch (e) {
                  console.error('Error parsing saved filters:', e);
                  return null;
              }
          }
          return null;
      }
      
      resetPage() {
          this.page = 1;
          this.hasMoreProducts = true;
      }
      
      incrementPage() {
          this.page++;
      }
      
      buildQueryParams() {
          const params = new URLSearchParams();
          if (this.search) params.append('search', this.search);
          if (this.category) params.append('category', this.category);
          if (this.sort) params.append('sort', this.sort);
          if (this.page) params.append('page', this.page);
          return params;
      }
  }

  // UIController class
  class UIController {
      constructor() {
          // DOM Elements
          this.searchInput = document.getElementById('product-search');
          this.categoryFilter = document.getElementById('category-filter');
          this.sortSelect = document.getElementById('sort-products');
          this.productsContainer = document.getElementById('products-container');
          this.paginationLinks = document.getElementById('pagination-links');
          this.loadMoreButton = document.getElementById('load-more-button');
          this.loadingIndicator = document.getElementById('loading-indicator');
          this.noMoreProductsMessage = document.getElementById('no-more-products');
          this.addProductButton = document.getElementById('add-product-button');
          this.productDialog = document.getElementById('product-dialog');
          this.productForm = document.getElementById('product-form');
          this.productIdInput = document.getElementById('product-id');
          this.productImageInput = document.getElementById('product-image');
          this.imagePreview = document.getElementById('image-preview');
          this.deleteConfirmation = document.getElementById('delete-confirmation');
          this.confirmDeleteButton = document.getElementById('confirm-delete-button');
          this.dialogTitle = this.productDialog?.querySelector('h2');
          this.productDetailDialog = document.getElementById('product-detail-dialog');
          this.detailDeleteButton = document.getElementById('detail-delete-button');
          this.toastContainer = document.getElementById('toast-container');
      }
      
      showLoadingProducts() {
          this.productsContainer.innerHTML = '<div class="col-span-full flex justify-center py-12"><svg class="animate-spin h-8 w-8 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></div>';
          this.loadMoreButton.classList.add('hidden');
          this.loadingIndicator.classList.add('hidden');
          this.noMoreProductsMessage.classList.add('hidden');
      }
      
      showLoadingMore(show) {
          if (show) {
              this.loadMoreButton.classList.add('hidden');
              this.loadingIndicator.classList.remove('hidden');
          } else {
              this.loadingIndicator.classList.add('hidden');
          }
      }
      
      updateProducts(html) {
          this.productsContainer.innerHTML = html;
      }
      
      appendProducts(html) {
          const tempDiv = document.createElement('div');
          tempDiv.innerHTML = html;
          const productCards = tempDiv.querySelectorAll('[data-product-id]');
          productCards.forEach(card => {
              this.productsContainer.appendChild(card);
          });
      }
      
      updateLoadMoreButton(hasMoreProducts) {
          if (hasMoreProducts) {
              this.loadMoreButton.classList.remove('hidden');
              this.noMoreProductsMessage.classList.add('hidden');
          } else {
              this.loadMoreButton.classList.add('hidden');
              this.noMoreProductsMessage.classList.remove('hidden');
          }
      }
      
      showProductError() {
          this.productsContainer.innerHTML = '<div class="col-span-full text-center py-12 text-gray-500">Failed to load products. Please try again.</div>';
          this.loadMoreButton.classList.add('hidden');
          this.loadingIndicator.classList.add('hidden');
          this.noMoreProductsMessage.classList.add('hidden');
      }
      
      openDialog(dialog) {
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
                  this.closeDialog(dialog);
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
          }.bind(this));
          
          // Prevent body scroll
          document.body.style.overflow = 'hidden';
      }
      
      closeDialog(dialog) {
          dialog.classList.add('hidden');
          dialog.classList.remove('flex');
          
          // Re-enable body scroll
          document.body.style.overflow = '';
      }
      
      resetImagePreview() {
          this.imagePreview.innerHTML = `
              <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
          `;
      }
      
      resetFormErrors() {
          const errorElements = document.querySelectorAll('[id$="-error"]');
          errorElements.forEach(el => {
              el.textContent = '';
              el.classList.add('hidden');
          });
      }
      
      showFormErrors(errors) {
          Object.keys(errors).forEach(field => {
              const errorElement = document.getElementById(`${field}-error`);
              if (errorElement) {
                  errorElement.textContent = errors[field][0];
                  errorElement.classList.remove('hidden');
              }
          });
      }
      
      setupAddProductDialog() {
          this.dialogTitle.textContent = 'Add Product';
          this.productForm.reset();
          this.productIdInput.value = '';
          this.resetFormErrors();
          this.resetImagePreview();
      }
      
      setupEditProductDialog(product) {
          this.dialogTitle.textContent = 'Edit Product';
          this.productIdInput.value = product.id;
          document.getElementById('product-name').value = product.name;
          document.getElementById('product-price').value = product.price;
          document.getElementById('product-description').value = product.description;
          document.getElementById('product-category').value = product.category_id;
          document.getElementById('product-stock').value = product.stock;
          document.getElementById('product-status').value = product.status;
          
          // Update image preview if exists
          if (product.image) {
              this.imagePreview.innerHTML = `<img src="/storage/${product.image}" class="w-full h-full object-cover">`;
          } else {
              this.resetImagePreview();
          }
      }
      
      disableSaveButton() {
          const saveButton = document.getElementById('save-product-button');
          saveButton.disabled = true;
          saveButton.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Saving...';
      }
      
      enableSaveButton() {
          const saveButton = document.getElementById('save-product-button');
          saveButton.disabled = false;
          saveButton.textContent = 'Save Product';
      }
      
      disableDeleteButton() {
          this.confirmDeleteButton.disabled = true;
          this.confirmDeleteButton.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Deleting...';
      }
      
      enableDeleteButton() {
          this.confirmDeleteButton.disabled = false;
          this.confirmDeleteButton.textContent = 'Delete';
      }
      
      updateDetailDialog(product, categories) {
          // Update detail fields
          document.getElementById('detail-name').textContent = product.name;
          document.getElementById('detail-price').textContent = parseFloat(product.price).toFixed(2);
          document.getElementById('detail-category').textContent = categories[product.category_id] || 'Uncategorized';
          document.getElementById('detail-status').textContent = product.status;
          document.getElementById('detail-stock').textContent = product.stock;
          document.getElementById('detail-description').textContent = product.description;
          
          // Add data attributes for inline editing
          document.getElementById('detail-category').setAttribute('data-value', product.category_id);
          document.getElementById('detail-status').setAttribute('data-value', product.status);
          
          // Update image
          const imageContainer = document.getElementById('detail-image-container');
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
      }
      
      showDetailLoading() {
          const detailFields = this.productDetailDialog.querySelectorAll('.editable-field');
          detailFields.forEach(field => {
              field.textContent = 'Loading...';
          });
          
          const imageContainer = document.getElementById('detail-image-container');
          imageContainer.innerHTML = '<div class="flex items-center justify-center p-12"><svg class="animate-spin h-8 w-8 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></div>';
      }
      
      makeFieldEditable(field) {
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
              
              // Add options from state
              for (const [id, name] of Object.entries(window.productState.categories)) {
                  const option = document.createElement('option');
                  option.value = id;
                  option.textContent = name;
                  if (id == currentValue) option.selected = true;
                  inputElement.appendChild(option);
              }
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
          saveButton.setAttribute('data-save-edit', '');
          
          // Create cancel button
          const cancelButton = document.createElement('button');
          cancelButton.type = 'button';
          cancelButton.className = 'mt-2 ml-2 inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors';
          cancelButton.innerHTML = 'Cancel';
          cancelButton.setAttribute('data-cancel-edit', '');
          
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
          
          return { field, inputElement, buttonContainer, editButton };
      }
      
      restoreField(field, inputElement, buttonContainer, editButton) {
          // Restore field display
          field.style.display = '';
          editButton.style.display = '';
          inputElement.remove();
          buttonContainer.remove();
      }
      
      showSavingInlineEdit(buttonContainer) {
          buttonContainer.innerHTML = '<div class="mt-2 flex items-center"><svg class="animate-spin h-4 w-4 text-gray-500 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Saving...</div>';
      }
      
      updateFieldDisplay(field, fieldName, newValue, categories) {
          if (fieldName === 'category_id') {
              field.textContent = categories[newValue] || 'Uncategorized';
              field.setAttribute('data-value', newValue);
          } else if (fieldName === 'price') {
              field.textContent = parseFloat(newValue).toFixed(2);
          } else {
              field.textContent = newValue;
          }
      }
      
      showImageLoading() {
          const imageContainer = document.getElementById('detail-image-container');
          imageContainer.innerHTML = '<div class="flex items-center justify-center p-12"><svg class="animate-spin h-8 w-8 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></div>';
      }
      
      updateDetailImage(product) {
          const imageContainer = document.getElementById('detail-image-container');
          if (product.image) {
              // Add cache-busting query parameter
              imageContainer.innerHTML = `<img src="/storage/${product.image}?t=${new Date().getTime()}" class="w-full h-auto rounded-lg shadow-sm" alt="${product.name}">`;
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
  }

  // ToastManager class
  class ToastManager {
      constructor(position = 'top-right') {
          this.container = document.getElementById('toast-container');
          this.position = position;
          
          // Update container position based on the position parameter
          if (this.container) {
              this.container.className = `fixed z-50 flex flex-col gap-2 ${this.getPositionClasses()}`;
          }
      }
      
      getPositionClasses() {
          switch (this.position) {
              case 'top-left':
                  return 'top-4 left-4';
              case 'top-center':
                  return 'top-4 left-1/2 transform -translate-x-1/2';
              case 'top-right':
                  return 'top-4 right-4';
              case 'bottom-left':
                  return 'bottom-4 left-4';
              case 'bottom-center':
                  return 'bottom-4 left-1/2 transform -translate-x-1/2';
              case 'bottom-right':
                  return 'bottom-4 right-4';
              default:
                  return 'top-4 right-4';
          }
      }
      
      show(message, type = 'success') {
          if (!this.container) return;
          
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
          
          this.container.appendChild(toast);
          
          // Auto remove after 5 seconds
          setTimeout(() => {
              toast.classList.add('opacity-0', 'transition-opacity', 'duration-300');
              setTimeout(() => {
                  toast.remove();
              }, 300);
          }, 5000);
      }
  }

  // ProductController class
  class ProductController {
      constructor(state, ui, toast) {
          this.state = state;
          this.ui = ui;
          this.toast = toast;
          
          window.productState = state;
      }
      
      async fetchProducts(append = false) {
          try {
              if (!append) {
                  this.ui.showLoadingProducts();
              } else {
                  this.ui.showLoadingMore(true);
                  this.state.loadingMore = true;
              }
              
              const params = this.state.buildQueryParams();
              const response = await fetch(`/products/list?${params.toString()}`, {
                  headers: {
                      'X-Requested-With': 'XMLHttpRequest'
                  }
              });
              
              if (!response.ok) {
                  throw new Error('Network response was not ok');
              }
              
              const data = await response.json();
              
              // Check if there are more products to load
              this.state.hasMoreProducts = data.has_more_pages;
              
              if (append) {
                  this.ui.appendProducts(data.products);
              } else {
                  this.ui.updateProducts(data.products);
              }
              
              this.ui.updateLoadMoreButton(this.state.hasMoreProducts);
              
              // Save state to localStorage
              this.state.saveFilters();
              
              return true;
          } catch (error) {
              console.error('Error fetching products:', error);
              this.toast.show('Failed to load products. Please try again.', 'error');
              
              if (!append) {
                  this.ui.showProductError();
              }
              
              return false;
          } finally {
              if (append) {
                  this.ui.showLoadingMore(false);
                  this.state.loadingMore = false;
              }
          }
      }
      
      async loadMoreProducts() {
          if (this.state.loadingMore || !this.state.hasMoreProducts) {
              return;
          }
          
          this.state.incrementPage();
          await this.fetchProducts(true);
      }
      
      updateProducts() {
          this.state.resetPage();
          this.fetchProducts();
      }
      
      async fetchProductDetails(productId) {
          try {
              this.ui.showDetailLoading();
              
              const response = await fetch(`/products/${productId}/edit`, {
                  headers: {
                      'X-Requested-With': 'XMLHttpRequest'
                  }
              });
              
              if (!response.ok) {
                  throw new Error('Network response was not ok');
              }
              
              const product = await response.json();
              this.state.currentProductDetail = product;
              
              this.ui.updateDetailDialog(product, this.state.categories);
              this.ui.openDialog(this.ui.productDetailDialog);
              
              return product;
          } catch (error) {
              console.error('Error fetching product:', error);
              this.toast.show('Failed to load product details. Please try again.', 'error');
              return null;
          }
      }
      
      async fetchProductForEdit(productId) {
          try {
              const formFields = this.ui.productForm.querySelectorAll('input, select, textarea');
              formFields.forEach(field => field.disabled = true);
              
              const response = await fetch(`/products/${productId}/edit`, {
                  headers: {
                      'X-Requested-With': 'XMLHttpRequest'
                  }
              });
              
              if (!response.ok) {
                  throw new Error('Network response was not ok');
              }
              
              const product = await response.json();
              
              this.ui.setupEditProductDialog(product);
              
              // Enable form fields
              formFields.forEach(field => field.disabled = false);
              
              this.ui.openDialog(this.ui.productDialog);
              
              return product;
          } catch (error) {
              console.error('Error fetching product:', error);
              this.toast.show('Failed to load product data. Please try again.', 'error');
              return null;
          }
      }
      
      async saveProduct(formData) {
          try {
              this.ui.disableSaveButton();
              
              // Determine URL based on whether we're adding or editing
              const productId = this.ui.productIdInput.value;
              const url = productId ? `/products/${productId}` : '/products';
              
              // Add method override for PUT if editing
              if (productId) {
                  formData.append('_method', 'PUT');
              }
              
              const response = await fetch(url, {
                  method: 'POST',
                  body: formData,
                  headers: {
                      'X-CSRF-TOKEN': this.state.csrfToken,
                      'X-Requested-With': 'XMLHttpRequest'
                  }
              });
              
              const data = await response.json();
              
              if (data.success) {
                  this.ui.closeDialog(this.ui.productDialog);
                  this.updateProducts();
                  this.toast.show(data.message, 'success');
                  return true;
              } else {
                  // Display validation errors
                  if (data.errors) {
                      this.ui.showFormErrors(data.errors);
                  }
                  this.toast.show('Please correct the errors in the form.', 'error');
                  return false;
              }
          } catch (error) {
              console.error('Error saving product:', error);
              this.toast.show('Failed to save product. Please try again.', 'error');
              return false;
          } finally {
              this.ui.enableSaveButton();
          }
      }
      
      async deleteProduct(productId) {
          try {
              this.ui.disableDeleteButton();
              
              const response = await fetch(`/products/${productId}`, {
                  method: 'DELETE',
                  headers: {
                      'X-CSRF-TOKEN': this.state.csrfToken,
                      'X-Requested-With': 'XMLHttpRequest'
                  }
              });
              
              const data = await response.json();
              
              this.ui.closeDialog(this.ui.deleteConfirmation);
              
              if (data.success) {
                  this.updateProducts();
                  this.toast.show(data.message, 'success');
                  return true;
              } else {
                  this.toast.show(data.message || 'Failed to delete product.', 'error');
                  return false;
              }
          } catch (error) {
              console.error('Error deleting product:', error);
              this.toast.show('Failed to delete product. Please try again.', 'error');
              return false;
          } finally {
              this.ui.enableDeleteButton();
              this.state.productIdToDelete = null;
          }
      }
      
      async saveInlineEdit(field, inputElement) {
          const fieldName = field.getAttribute('data-field');
          const newValue = inputElement.value;
          const buttonContainer = inputElement.nextElementSibling;
          
          try {
              // Show loading state
              this.ui.showSavingInlineEdit(buttonContainer);
              
              // Prepare data for update
              const data = new FormData();
              data.append('_method', 'PUT');
              data.append(fieldName, newValue);
              
              // Send update request
              const response = await fetch(`/products/${this.state.currentProductDetail.id}`, {
                  method: 'POST',
                  body: data,
                  headers: {
                      'X-CSRF-TOKEN': this.state.csrfToken,
                      'X-Requested-With': 'XMLHttpRequest'
                  }
              });
              
              const responseData = await response.json();
              
              if (responseData.success) {
                  // Update field display
                  this.ui.updateFieldDisplay(field, fieldName, newValue, this.state.categories);
                  
                  // Update state
                  this.state.currentProductDetail[fieldName] = newValue;
                  
                  // Show success message
                  this.toast.show('Product updated successfully!', 'success');
                  
                  // Update products list
                  this.updateProducts();
                  
                  return true;
              } else {
                  this.toast.show('Failed to update product. Please try again.', 'error');
                  return false;
              }
          } catch (error) {
              console.error('Error updating product:', error);
              this.toast.show('Failed to update product. Please try again.', 'error');
              return false;
          } finally {
              // Restore field display
              const container = field.closest('.editable-field-container');
              const editButton = container.querySelector('.edit-button');
              
              field.style.display = '';
              editButton.style.display = '';
              inputElement.remove();
              buttonContainer.remove();
          }
      }
      
      async updateProductImage(file) {
          if (!file || !this.state.currentProductDetail) return false;
          
          try {
              // Validate file type
              const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
              if (!validTypes.includes(file.type)) {
                  this.toast.show('Please select a valid image file (JPEG, PNG, GIF).', 'error');
                  return false;
              }
              
              // Validate file size (max 2MB)
              if (file.size > 2 * 1024 * 1024) {
                  this.toast.show('Image size should not exceed 2MB.', 'error');
                  return false;
              }
              
              // Show loading state
              this.ui.showImageLoading();
              
              // Create form data
              const formData = new FormData();
              formData.append('_method', 'PUT');
              formData.append('image', file);
              
              // Send update request
              const response = await fetch(`/products/${this.state.currentProductDetail.id}`, {
                  method: 'POST',
                  body: formData,
                  headers: {
                      'X-CSRF-TOKEN': this.state.csrfToken,
                      'X-Requested-With': 'XMLHttpRequest'
                  }
              });
              
              const data = await response.json();
              
              if (data.success) {
                  // Update image in detail view
                  this.ui.updateDetailImage(data.product);
                  
                  // Update state
                  this.state.currentProductDetail.image = data.product.image;
                  
                  // Show success message
                  this.toast.show('Product image updated successfully!', 'success');
                  
                  // Update products list
                  this.updateProducts();
                  
                  return true;
              } else {
                  this.toast.show('Failed to update product image. Please try again.', 'error');
                  
                  // Restore previous image
                  this.ui.updateDetailImage(this.state.currentProductDetail);
                  
                  return false;
              }
          } catch (error) {
              console.error('Error updating product image:', error);
              this.toast.show('Failed to update product image. Please try again.', 'error');
              
              // Restore previous image
              this.ui.updateDetailImage(this.state.currentProductDetail);
              
              return false;
          }
      }
      
      validateImageFile(file) {
          // Validate file type
          const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
          if (!validTypes.includes(file.type)) {
              document.getElementById('image-error').textContent = 'Please select a valid image file (JPEG, PNG, GIF).';
              document.getElementById('image-error').classList.remove('hidden');
              this.ui.resetImagePreview();
              return false;
          }
          
          // Validate file size (max 2MB)
          if (file.size > 2 * 1024 * 1024) {
              document.getElementById('image-error').textContent = 'Image size should not exceed 2MB.';
              document.getElementById('image-error').classList.remove('hidden');
              this.ui.resetImagePreview();
              return false;
          }
          
          return true;
      }
      
      previewImage(file) {
          if (!file) {
              this.ui.resetImagePreview();
              return;
          }
          
          if (!this.validateImageFile(file)) {
              return;
          }
          
          // Preview image
          const reader = new FileReader();
          reader.onload = (e) => {
              this.ui.imagePreview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
          };
          reader.readAsDataURL(file);
      }
      
      loadSavedFilters() {
          const savedFilters = this.state.loadFilters();
          if (savedFilters) {
              if (savedFilters.search) {
                  this.ui.searchInput.value = savedFilters.search;
                  this.state.search = savedFilters.search;
              }
              
              if (savedFilters.category) {
                  this.ui.categoryFilter.value = savedFilters.category;
                  this.state.category = savedFilters.category;
              }
              
              if (savedFilters.sort) {
                  this.ui.sortSelect.value = savedFilters.sort;
                  this.state.sort = savedFilters.sort;
              }
              
              // Only update if we have saved filters
              if (savedFilters.search || savedFilters.category || savedFilters.sort) {
                  this.updateProducts();
              }
          }
      }
  }

  // EventHandler class
  class EventHandler {
      constructor(state, ui, productController, toast) {
          this.state = state;
          this.ui = ui;
          this.productController = productController;
          this.toast = toast;
          
          // Bind methods to maintain 'this' context
          this.debounceSearch = this.debounceSearch.bind(this);
          this.handleProductSearch = this.handleProductSearch.bind(this);
          this.handleCategoryFilter = this.handleCategoryFilter.bind(this);
          this.handleSortChange = this.handleSortChange.bind(this);
          this.handleAddProductClick = this.handleAddProductClick.bind(this);
          this.handleProductFormSubmit = this.handleProductFormSubmit.bind(this);
          this.handleImageInputChange = this.handleImageInputChange.bind(this);
          this.handleDeleteConfirmation = this.handleDeleteConfirmation.bind(this);
          this.handleDetailDeleteClick = this.handleDetailDeleteClick.bind(this);
          this.handleDocumentClick = this.handleDocumentClick.bind(this);
          this.handleDocumentChange = this.handleDocumentChange.bind(this);
          this.handleLoadMoreClick = this.handleLoadMoreClick.bind(this);
      }
      
      initEventListeners() {
          // Search, filter, and sort
          this.ui.searchInput.addEventListener('input', this.debounceSearch);
          this.ui.categoryFilter.addEventListener('change', this.handleCategoryFilter);
          this.ui.sortSelect.addEventListener('change', this.handleSortChange);
          
          // Add product
          this.ui.addProductButton.addEventListener('click', this.handleAddProductClick);
          
          // Form submission
          this.ui.productForm.addEventListener('submit', this.handleProductFormSubmit);
          
          // Image preview
          this.ui.productImageInput.addEventListener('change', this.handleImageInputChange);
          
          // Delete confirmation
          this.ui.confirmDeleteButton.addEventListener('click', this.handleDeleteConfirmation);
          
          // Detail delete button
          this.ui.detailDeleteButton.addEventListener('click', this.handleDetailDeleteClick);
          
          // Load more button
          this.ui.loadMoreButton.addEventListener('click', this.handleLoadMoreClick);
          
          // Delegate event listeners for dynamic elements
          document.addEventListener('click', this.handleDocumentClick);
          document.addEventListener('change', this.handleDocumentChange);
      }
      
      debounceSearch() {
          clearTimeout(this.state.debounceTimeout);
          this.state.debounceTimeout = setTimeout(this.handleProductSearch, 300);
      }
      
      handleProductSearch() {
          this.state.search = this.ui.searchInput.value;
          this.state.resetPage();
          this.productController.updateProducts();
      }
      
      handleCategoryFilter() {
          this.state.category = this.ui.categoryFilter.value;
          this.state.resetPage();
          this.productController.updateProducts();
      }
      
      handleSortChange() {
          this.state.sort = this.ui.sortSelect.value;
          this.state.resetPage();
          this.productController.updateProducts();
      }
      
      handleAddProductClick() {
          this.state.isEditing = false;
          this.ui.setupAddProductDialog();
          this.ui.openDialog(this.ui.productDialog);
      }
      
      async handleProductFormSubmit(e) {
          e.preventDefault();
          
          // Create FormData object
          const formData = new FormData(this.ui.productForm);
          
          // Save product
          await this.productController.saveProduct(formData);
      }
      
      handleImageInputChange(e) {
          const file = e.target.files[0];
          this.productController.previewImage(file);
      }
      
      async handleDeleteConfirmation() {
          if (!this.state.productIdToDelete) return;
          await this.productController.deleteProduct(this.state.productIdToDelete);
      }
      
      handleDetailDeleteClick() {
          if (this.state.currentProductDetail) {
              this.state.productIdToDelete = this.state.currentProductDetail.id;
              this.ui.closeDialog(this.ui.productDetailDialog);
              this.ui.openDialog(this.ui.deleteConfirmation);
          }
      }
      
      handleLoadMoreClick() {
          this.productController.loadMoreProducts();
      }
      
      handleDocumentClick(e) {
          // Product card click
          const productCard = e.target.closest('[data-product-id]');
          if (productCard && !e.target.closest('button')) {
              const productId = productCard.getAttribute('data-product-id');
              this.productController.fetchProductDetails(productId);
          }
          
          // Edit product button
          if (e.target.closest('[data-edit-product]')) {
              e.stopPropagation(); // Prevent card click
              const productId = e.target.closest('[data-edit-product]').getAttribute('data-edit-product');
              this.productController.fetchProductForEdit(productId);
          }
          
          // Delete product button
          if (e.target.closest('[data-delete-product]')) {
              e.stopPropagation(); // Prevent card click
              const productId = e.target.closest('[data-delete-product]').getAttribute('data-delete-product');
              this.state.productIdToDelete = productId;
              this.ui.openDialog(this.ui.deleteConfirmation);
          }
          
          // Close dialog buttons
          if (e.target.closest('[data-close-dialog]')) {
              this.ui.closeDialog(this.ui.productDialog);
          }
          
          // Close detail dialog buttons
          if (e.target.closest('[data-close-detail-dialog]')) {
              this.ui.closeDialog(this.ui.productDetailDialog);
          }
          
          // Close confirmation buttons
          if (e.target.closest('[data-close-confirmation]')) {
              this.ui.closeDialog(this.ui.deleteConfirmation);
          }
          
          // Edit field buttons in detail dialog
          if (e.target.closest('.edit-button')) {
              const container = e.target.closest('.editable-field-container');
              const field = container.querySelector('.editable-field');
              const editElements = this.ui.makeFieldEditable(field);
              
              // Add event listeners for save and cancel buttons
              const saveButton = editElements.buttonContainer.querySelector('[data-save-edit]');
              const cancelButton = editElements.buttonContainer.querySelector('[data-cancel-edit]');
              
              if (saveButton) {
                  saveButton.addEventListener('click', () => {
                      this.productController.saveInlineEdit(editElements.field, editElements.inputElement);
                  });
              }
              
              if (cancelButton) {
                  cancelButton.addEventListener('click', () => {
                      this.ui.restoreField(
                          editElements.field, 
                          editElements.inputElement, 
                          editElements.buttonContainer, 
                          editElements.editButton
                      );
                  });
              }
              
              // Handle Enter key for inputs
              editElements.inputElement.addEventListener('keydown', (e) => {
                  if (e.key === 'Enter' && field.getAttribute('data-field') !== 'description') {
                      e.preventDefault();
                      this.productController.saveInlineEdit(editElements.field, editElements.inputElement);
                  }
              });
          }
      }
      
      handleDocumentChange(e) {
          if (e.target.id === 'detail-image-input') {
              this.productController.updateProductImage(e.target.files[0]);
          }
      }
  }

  // Initialize modules
  const state = new ProductState();
  const ui = new UIController();
  const toast = new ToastManager('top-right');
  const productController = new ProductController(state, ui, toast);
  const eventHandler = new EventHandler(state, ui, productController, toast);
  
  // Initialize the application
  eventHandler.initEventListeners();
  
  // Load initial products
  productController.loadSavedFilters();
  
  // Show the load more button initially if there are products
  if (document.querySelectorAll('#products-container [data-product-id]').length > 0) {
      ui.loadMoreButton.classList.remove('hidden');
  }
});