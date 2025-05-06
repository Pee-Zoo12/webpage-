<?php
require_once 'includes/config.php';
require_once 'includes/JsonDatabase.php';
require_once 'includes/functions.php';

$pageTitle = 'Shopping Cart - ' . SITE_NAME;

// Initialize database
$db = new JsonDatabase();

// Get cart items
$cartItems = [];
$total = 0;

if (isLoggedIn()) {
    $cartItems = $db->getCart($_SESSION['user_id']);
} else if (isset($_SESSION['cart'])) {
    $cartItems = $_SESSION['cart'];
}

// Get product details for each cart item
foreach ($cartItems as &$item) {
    $product = $db->getProduct($item['productId']);
    if ($product) {
        $item['product'] = $product;
        $item['subtotal'] = $product['unitCost'] * $item['quantity'];
        $total += $item['subtotal'];
    }
}

include 'includes/header.php';
?>

<div class="cart-header">
    <div class="container">
        <h1>Shopping Cart</h1>
        <?php echo generateBreadcrumbs(['Home' => 'index.php', 'Cart' => 'cart.php']); ?>
    </div>
</div>

<div class="cart-content">
    <div class="container">
        <?php if (empty($cartItems)): ?>
        <div class="empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <h2>Your cart is empty</h2>
            <p>Looks like you haven't added any items to your cart yet.</p>
            <a href="shop.php" class="btn btn-primary">Continue Shopping</a>
        </div>
        <?php else: ?>
        <div class="cart-grid">
            <div class="cart-items">
                <?php foreach ($cartItems as $item): ?>
                <div class="cart-item" data-cart-id="<?php echo $item['cartId']; ?>">
                    <div class="item-image">
                        <img src="<?php echo $item['product']['images'][0]; ?>" 
                             alt="<?php echo $item['product']['productName']; ?>">
                    </div>
                    <div class="item-details">
                        <h3><?php echo $item['product']['productName']; ?></h3>
                        <p class="item-price"><?php echo formatPrice($item['product']['unitCost']); ?></p>
                        <div class="item-quantity">
                            <button class="quantity-btn minus">-</button>
                            <input type="number" value="<?php echo $item['quantity']; ?>" min="1" 
                                   max="<?php echo $item['product']['stockQuantity']; ?>">
                            <button class="quantity-btn plus">+</button>
                        </div>
                    </div>
                    <div class="item-subtotal">
                        <p><?php echo formatPrice($item['subtotal']); ?></p>
                    </div>
                    <button class="remove-item">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary">
                <h2>Order Summary</h2>
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span><?php echo formatPrice($total); ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span>Calculated at checkout</span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span><?php echo formatPrice($total); ?></span>
                </div>
                <a href="checkout.php" class="btn btn-primary checkout-btn">Proceed to Checkout</a>
                <a href="shop.php" class="btn btn-outline continue-shopping">Continue Shopping</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Update quantity
document.querySelectorAll('.quantity-btn').forEach(button => {
    button.addEventListener('click', function() {
        const input = this.parentElement.querySelector('input');
        const currentValue = parseInt(input.value);
        
        if (this.classList.contains('minus')) {
            if (currentValue > 1) {
                input.value = currentValue - 1;
                updateCartItem(input);
            }
        } else {
            if (currentValue < parseInt(input.max)) {
                input.value = currentValue + 1;
                updateCartItem(input);
            }
        }
    });
});

// Handle direct input
document.querySelectorAll('.item-quantity input').forEach(input => {
    input.addEventListener('change', function() {
        updateCartItem(this);
    });
});

// Remove item
document.querySelectorAll('.remove-item').forEach(button => {
    button.addEventListener('click', function() {
        const cartItem = this.closest('.cart-item');
        const cartId = cartItem.dataset.cartId;
        
        fetch('process/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'remove',
                cartId: cartId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                cartItem.remove();
                updateCartSummary();
                showNotification('Item removed from cart', 'success');
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

// Update cart item
function updateCartItem(input) {
    const cartItem = input.closest('.cart-item');
    const cartId = cartItem.dataset.cartId;
    const quantity = parseInt(input.value);
    
    fetch('process/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'update',
            cartId: cartId,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartSummary();
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Something went wrong. Please try again.', 'error');
    });
}

// Update cart summary
function updateCartSummary() {
    const cartItems = document.querySelectorAll('.cart-item');
    let total = 0;
    
    cartItems.forEach(item => {
        const price = parseFloat(item.querySelector('.item-price').textContent.replace('$', ''));
        const quantity = parseInt(item.querySelector('.item-quantity input').value);
        const subtotal = price * quantity;
        
        item.querySelector('.item-subtotal p').textContent = formatPrice(subtotal);
        total += subtotal;
    });
    
    document.querySelector('.summary-row.total span:last-child').textContent = formatPrice(total);
    
    // Update cart count in header
    const cartCount = document.querySelector('.cart-count');
    if (cartCount) {
        const totalItems = Array.from(cartItems).reduce((sum, item) => {
            return sum + parseInt(item.querySelector('.item-quantity input').value);
        }, 0);
        cartCount.textContent = totalItems;
    }
}

// Format price
function formatPrice(price) {
    return '$' + price.toFixed(2);
}
</script>

<?php include 'includes/footer.php'; ?>
