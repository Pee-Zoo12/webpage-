<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'classes/JsonDatabase.php';
require_once 'classes/Website.php';
require_once 'classes/ShoppingCart.php';

// Initialize site
$site = new Website();
$db = new JsonDatabase();
$cart = new ShoppingCart();

// Get cart items
$cartItems = $cart->viewCartDetails();
$cartTotal = 0;
$shippingOptions = $db->getShippingOptions();

// Calculate cart total
foreach ($cartItems as $item) {
    $cartTotal += $item->unitCost * $item->quantity;
}

// Page title
$pageTitle = "Your Cart | Nox Apparel";
include 'includes/header.php';
?>

<main>
    <!-- Cart Header -->
    <section class="cart-header">
        <div class="container">
            <h1 class="animate-text">Your Shopping Cart</h1>
            <div class="cart-steps">
                <div class="step active">
                    <div class="step-number">1</div>
                    <div class="step-name">Shopping Cart</div>
                </div>
                <div class="step-connector"></div>
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-name">Shipping & Payment</div>
                </div>
                <div class="step-connector"></div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-name">Confirmation</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Cart Content -->
    <section class="cart-content">
        <div class="container">
            <?php if (empty($cartItems)): ?>
            <!-- Empty Cart -->
            <div class="empty-cart animate-on-scroll">
                <div class="empty-cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h2>Your cart is empty</h2>
                <p>Looks like you haven't added any products to your cart yet.</p>
                <a href="shop.php" class="btn btn-primary">Continue Shopping</a>
            </div>
            <?php else: ?>
            <div class="cart-grid">
                <!-- Cart Items -->
                <div class="cart-items animate-slide-right">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th colspan="2">Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cartItems as $item): ?>
                            <tr class="cart-item" data-product-id="<?php echo $item->productID; ?>">
                                <td class="product-image">
                                    <a href="product.php?id=<?php echo $item->productID; ?>">
                                        <img src="<?php echo $item->image; ?>" alt="<?php echo $item->productName; ?>">
                                    </a>
                                </td>
                                <td class="product-info">
                                    <h3><a href="product.php?id=<?php echo $item->productID; ?>"><?php echo $item->productName; ?></a></h3>
                                    <?php if (!empty($item->selectedSize)): ?>
                                    <div class="product-size">Size: <?php echo $item->selectedSize; ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($item->selectedColor)): ?>
                                    <div class="product-color">Color: <?php echo ucfirst($item->selectedColor); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="product-price">$<?php echo number_format($item->unitCost, 2); ?></td>
                                <td class="product-quantity">
                                    <div class="quantity-selector">
                                        <button class="quantity-btn minus" data-product-id="<?php echo $item->productID; ?>">-</button>
                                        <input type="number" value="<?php echo $item->quantity; ?>" min="1" max="<?php echo $item->stockQuantity; ?>" 
                                               data-product-id="<?php echo $item->productID; ?>">
                                        <button class="quantity-btn plus" data-product-id="<?php echo $item->productID; ?>">+</button>
                                    </div>
                                </td>
                                <td class="product-subtotal">$<?php echo number_format($item->unitCost * $item->quantity, 2); ?></td>
                                <td class="product-remove">
                                    <button class="remove-item" data-product-id="<?php echo $item->productID; ?>">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="cart-actions">
                        <a href="shop.php" class="btn btn-outline">Continue Shopping</a>
                        <button id="update-cart" class="btn btn-secondary">Update Cart</button>
                    </div>
                </div>

                <!-- Cart Summary -->
                <div class="cart-summary animate-slide-left">
                    <h2>Order Summary</h2>
                    
                    <div class="summary-line">
                        <span>Subtotal</span>
                        <span>$<?php echo number_format($cartTotal, 2); ?></span>
                    </div>
                    
                    <div class="shipping-options">
                        <h3>Shipping</h3>
                        <?php foreach ($shippingOptions as $option): ?>
                        <label class="shipping-option">
                            <input type="radio" name="shipping" value="<?php echo $option->id; ?>" 
                                   <?php echo $option->id === 'standard' ? 'checked' : ''; ?>>
                            <div class="option-details">
                                <div class="option-name"><?php echo $option->name; ?></div>
                                <div class="option-price">$<?php echo number_format($option->cost, 2); ?></div>
                            </div>
                            <div class="option-description"><?php echo $option->description; ?></div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="promo-code">
                        <h3>Promo Code</h3>
                        <div class="promo-input">
                            <input type="text" id="promo-code" placeholder="Enter promo code">
                            <button id="apply-promo" class="btn btn-secondary">Apply</button>
                        </div>
                    </div>
                    
                    <div class="total-line">
                        <span>Total</span>
                        <span id="cart-total">$<?php echo number_format($cartTotal + 9.99, 2); ?></span>
                    </div>
                    
                    <a href="checkout.php" class="btn btn-primary btn-block">Proceed to Checkout</a>
                    
                    <div class="payment-icons">
                        <i class="fab fa-cc-visa"></i>
                        <i class="fab fa-cc-mastercard"></i>
                        <i class="fab fa-cc-amex"></i>
                        <i class="fab fa-cc-paypal"></i>
                        <i class="fab fa-cc-apple-pay"></i>
                    </div>
                    
                    <div class="secure-checkout">
                        <i class="fas fa-lock"></i> Secure Checkout
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
