document.addEventListener('DOMContentLoaded', function() {
    // Loading screen animation
    const loadingScreen = document.querySelector('.loading-screen');
    
    setTimeout(() => {
        loadingScreen.classList.add('hide-loader');
        document.body.style.overflow = 'auto';
        
        // Start animations after loading screen disappears
        startAnimations();
    }, 1500);
    
    // Mobile menu toggle
    const mobileToggle = document.querySelector('.mobile-toggle');
    const navLinks = document.querySelector('.nav-links');
    
    if (mobileToggle) {
        mobileToggle.addEventListener('click', function() {
            navLinks.classList.toggle('active');
        });
    }
    
    // Product image slider/gallery
    const mainImage = document.querySelector('.product-main-image');
    const thumbnails = document.querySelectorAll('.product-thumbnail');
    
    if (thumbnails.length > 0) {
        thumbnails.forEach(thumbnail => {
            thumbnail.addEventListener('click', function() {
                // Update main image
                mainImage.src = this.src;
                
                // Update active state
                thumbnails.forEach(thumb => thumb.classList.remove('active'));
                this.classList.add('active');
            });
        });
    }
    
    // Product tabs
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    if (tabBtns.length > 0) {
        tabBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const target = this.getAttribute('data-tab');
                
                // Update active button
                tabBtns.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                // Show target content
                tabContents.forEach(content => {
                    content.classList.remove('active');
                    if (content.getAttribute('id') === `${target}-tab`) {
                        content.classList.add('active');
                    }
                });
            });
        });
    }
    
    // Quantity inputs
    function setupQuantityButtons() {
        const quantityBtns = document.querySelectorAll('.quantity-btn');
        
        if (quantityBtns.length > 0) {
            quantityBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const input = this.parentElement.querySelector('input');
                    const currentValue = parseInt(input.value);
                    const maxValue = parseInt(input.getAttribute('max')) || 99;
                    
                    if (this.classList.contains('minus') && currentValue > 1) {
                        input.value = currentValue - 1;
                    } else if (this.classList.contains('plus') && currentValue < maxValue) {
                        input.value = currentValue + 1;
                    }
                    
                    // Trigger change event to update cart if needed
                    const event = new Event('change');
                    input.dispatchEvent(event);
                });
            });
        }
    }
    
    setupQuantityButtons();
    
    // Cart functionality
    function setupCartFunctionality() {
        const addToCartBtns = document.querySelectorAll('.add-to-cart-btn');
        const cartCountElement = document.querySelector('.cart-count');
        
        if (addToCartBtns.length > 0) {
            addToCartBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Get product info
                    const productId = this.getAttribute('data-product-id');
                    const productName = this.getAttribute('data-name');
                    const productPrice = this.getAttribute('data-price');
                    const productImage = this.getAttribute('data-image');
                    
                    // Get selected options if available
                    let selectedSize = null;
                    let selectedColor = null;
                    
                    const sizeOptions = document.querySelectorAll('.size-option input[type="radio"]');
                    if (sizeOptions.length > 0) {
                        const activeSize = document.querySelector('.size-option input[type="radio"]:checked');
                        if (activeSize) {
                            selectedSize = activeSize.value;
                        } else {
                            showNotification('Please select a size', 'error');
                            return;
                        }
                    }
                    
                    const colorOptions = document.querySelectorAll('.color-option input[type="radio"]');
                    if (colorOptions.length > 0) {
                        const activeColor = document.querySelector('.color-option input[type="radio"]:checked');
                        if (activeColor) {
                            selectedColor = activeColor.value;
                        } else {
                            showNotification('Please select a color', 'error');
                            return;
                        }
                    }
                    
                    // Get quantity
                    const quantityInput = document.querySelector('#product-quantity');
                    const quantity = quantityInput ? parseInt(quantityInput.value) : 1;
                    
                    // Add to cart via AJAX
                    fetch('cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=add&product_id=${productId}&quantity=${quantity}&size=${selectedSize}&color=${selectedColor}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update cart count
                            if (cartCountElement) {
                                cartCountElement.textContent = data.cart_count;
                            }
                            
                            // Show success message
                            showNotification('Product added to cart', 'success');
                            
                            // Animation effect
                            animateCartButton();
                        } else {
                            showNotification(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('Something went wrong. Please try again.', 'error');
                    });
                });
            });
        }
    }
    
    setupCartFunctionality();
    
    // Product option selection
    function setupProductOptions() {
        const sizeOptions = document.querySelectorAll('.size-option input[type="radio"]');
        const colorOptions = document.querySelectorAll('.color-option input[type="radio"]');
        
        if (sizeOptions.length > 0) {
            sizeOptions.forEach(option => {
                option.addEventListener('change', function() {
                    const parent = this.closest('.size-option');
                    document.querySelectorAll('.size-option').forEach(opt => opt.classList.remove('active'));
                    parent.classList.add('active');
                });
            });
        }
        
        if (colorOptions.length > 0) {
            colorOptions.forEach(option => {
                option.addEventListener('change', function() {
                    const parent = this.closest('.color-option');
                    document.querySelectorAll('.color-option').forEach(opt => opt.classList.remove('active'));
                    parent.classList.add('active');
                });
            });
        }
    }
    
    setupProductOptions();
    
    // Notifications
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);
        
        // Animate out after 3 seconds
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }
    
    // Cart button animation
    function animateCartButton() {
        const cartIcon = document.querySelector('.cart-icon');
        if (cartIcon) {
            cartIcon.classList.add('bounce');
            setTimeout(() => {
                cartIcon.classList.remove('bounce');
            }, 1000);
        }
    }
    
    // QR Code Scanner functionality
    const scanButton = document.querySelector('.scan-button');
    const scanResult = document.querySelector('.scan-result');
    
    if (scanButton && scanResult) {
        scanButton.addEventListener('click', function() {
            // In a real application, this would trigger the camera for QR scanning
            // For this demo, we'll simulate a scan result
            simulateScan();
        });
    }
    
    function simulateScan() {
        // Show loading state
        scanButton.textContent = 'Scanning...';
        scanButton.disabled = true;
        
        // Simulate API call to verify QR code
        setTimeout(() => {
            // Reset button
            scanButton.textContent = 'Scan QR Code';
            scanButton.disabled = false;
            
            // For demo purposes, randomly show authentic or counterfeit
            const isAuthentic = Math.random() > 0.3;
            
            // Show result
            scanResult.style.display = 'block';
            scanResult.className = 'scan-result ' + (isAuthentic ? 'result-authentic' : 'result-counterfeit');
            
            scanResult.innerHTML = `
                <div class="scan-result-icon">
                    <i class="fas ${isAuthentic ? 'fa-check-circle' : 'fa-times-circle'}"></i>
                </div>
                <div class="scan-result-title">
                    ${isAuthentic ? 'Authentic Product' : 'Counterfeit Product'}
                </div>
                <div class="scan-result-details">
                    <div class="result-detail">
                        <span>Product Name:</span>
                        <span>Nox Apparel Signature Hoodie</span>
                    </div>
                    <div class="result-detail">
                        <span>Serial Number:</span>
                        <span>NOX-${Math.floor(Math.random() * 1000000)}</span>
                    </div>
                    <div class="result-detail">
                        <span>Manufacturing Date:</span>
                        <span>${new Date().toLocaleDateString()}</span>
                    </div>
                </div>
            `;
        }, 2000);
    }
    
    // Initialize animations
    function startAnimations() {
        // Animate elements with animation classes
        const animatedElements = document.querySelectorAll('.animate-on-scroll');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                }
            });
        }, {
            threshold: 0.1
        });
        
        animatedElements.forEach(element => {
            observer.observe(element);
        });
    }
    
    // Cart page functionality
    function setupCartPage() {
        const quantityInputs = document.querySelectorAll('.cart-quantity-input');
        const removeButtons = document.querySelectorAll('.cart-remove');
        
        if (quantityInputs.length > 0) {
            quantityInputs.forEach(input => {
                input.addEventListener('change', function() {
                    updateCartItem(this);
                });
            });
        }
        
        if (removeButtons.length > 0) {
            removeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    removeCartItem(this);
                });
            });
        }
    }
    
    function updateCartItem(input) {
        const itemId = input.getAttribute('data-item-id');
        const quantity = input.value;
        
        fetch('cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=update&item_id=${itemId}&quantity=${quantity}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update cart totals
                updateCartTotals(data.totals);
                showNotification('Cart updated', 'success');
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Failed to update cart', 'error');
        });
    }
    
    function removeCartItem(button) {
        const itemId = button.getAttribute('data-item-id');
        
        fetch('cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=remove&item_id=${itemId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove item from DOM
                const item = button.closest('tr');
                item.style.opacity = '0';
                setTimeout(() => {
                    item.remove();
                    // Update cart totals
                    updateCartTotals(data.totals);
                    // Check if cart is empty
                    if (data.cart_count === 0) {
                        location.reload();
                    }
                }, 300);
                showNotification('Item removed from cart', 'success');
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Failed to remove item', 'error');
        });
    }
    
    function updateCartTotals(totals) {
        // Update subtotal, shipping, and total
        document.querySelector('.subtotal-amount').textContent = `$${totals.subtotal.toFixed(2)}`;
        document.querySelector('.shipping-amount').textContent = `$${totals.shipping.toFixed(2)}`;
        document.querySelector('.total-amount').textContent = `$${totals.total.toFixed(2)}`;
    }
    
    // Initialize cart page if on cart page
    if (document.querySelector('.cart-page')) {
        setupCartPage();
    }
    
    // Checkout form validation
    function setupCheckoutForm() {
        const checkoutForm = document.querySelector('#checkout-form');
        
        if (checkoutForm) {
            checkoutForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Validate form
                if (validateCheckoutForm()) {
                    // Submit form
                    this.submit();
                }
            });
        }
    }
    
    function validateCheckoutForm() {
        const requiredFields = document.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add('error');
                
                // Add error message
                const errorMessage = document.createElement('div');
                errorMessage.className = 'error-message';
                errorMessage.textContent = 'This field is required';
                
                const existingError = field.parentElement.querySelector('.error-message');
                if (!existingError) {
                    field.parentElement.appendChild(errorMessage);
                }
            } else {
                field.classList.remove('error');
                const errorMessage = field.parentElement.querySelector('.error-message');
                if (errorMessage) {
                    errorMessage.remove();
                }
            }
        });
        
        return isValid;
    }
    
    // Initialize checkout form if on checkout page
    if (document.querySelector('.checkout-page')) {
        setupCheckoutForm();
    }
}); 