<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'classes/JsonDatabase.php';
require_once 'classes/Website.php';
require_once 'classes/ShoppingCart.php';
require_once 'classes/Payment.php';
require_once 'classes/ShippingInfo.php';
require_once 'classes/Orders.php';

// Check if user is logged in
if (!isLoggedIn()) {
    // Store current page as redirect after login
    $_SESSION['redirect_after_login'] = 'checkout.php';
    header('Location: login.php?checkout=true');
    exit;
}

// Initialize site
$site = new Website();
$db = new JsonDatabase();
$cart = new ShoppingCart();
$payment = new Payment();
$shipping = new ShippingInfo();

// Get cart items
$cartItems = $cart->viewCartDetails();

// If cart is empty, redirect to cart page
if (empty($cartItems)) {
    header('Location: cart.php');
    exit;
}

// Calculate totals
$cartSubtotal = 0;
$itemCount = 0;
foreach ($cartItems as $item) {
    $cartSubtotal += $item->unitCost * $item->quantity;
    $itemCount += $item->quantity;
}

// Get shipping options
$shippingOptions = $db->getShippingOptions();
$selectedShipping = isset($_SESSION['selected_shipping']) ? $_SESSION['selected_shipping'] : 'standard';
$shippingCost = 9.99; // Default
foreach ($shippingOptions as $option) {
    if ($option->id === $selectedShipping) {
        $shippingCost = $option->cost;
        break;
    }
}

// Calculate final total
$orderTotal = $cartSubtotal + $shippingCost;

// Get user information
$user = $db->getUserById($_SESSION['user_id']);

// Page title
$pageTitle = "Checkout | Nox Apparel";
include 'includes/header.php';
?>

<main>
    <!-- Checkout Header -->
    <section class="checkout-header">
        <div class="container">
            <h1 class="animate-text">Checkout</h1>
            <div class="cart-steps">
                <div class="step completed">
                    <div class="step-number"><i class="fas fa-check"></i></div>
                    <div class="step-name">Shopping Cart</div>
                </div>
                <div class="step-connector completed"></div>
                <div class="step active">
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

    <!-- Checkout Content -->
    <section class="checkout-content">
        <div class="container">
            <div class="checkout-grid">
                <!-- Checkout Form -->
                <div class="checkout-form animate-slide-right">
                    <form id="checkout-form" action="process/process-order.php" method="post">
                        <!-- Shipping Address -->
                        <div class="checkout-section">
                            <h2>Shipping Address</h2>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="firstName">First Name</label>
                                    <input type="text" id="firstName" name="firstName" value="<?php echo $user->firstName ?? ''; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="lastName">Last Name</label>
                                    <input type="text" id="lastName" name="lastName" value="<?php echo $user->lastName ?? ''; ?>" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" value="<?php echo $user->email ?? ''; ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo $user->phone ?? ''; ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="address">Street Address</label>
                                <input type="text" id="address" name="address" value="<?php echo $user->address ?? ''; ?>" required>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="city">City</label>
                                    <input type="text" id="city" name="city" value="<?php echo $user->city ?? ''; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="state">State/Province</label>
                                    <input type="text" id="state" name="state" value="<?php echo $user->state ?? ''; ?>" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="postalCode">Postal Code</label>
                                    <input type="text" id="postalCode" name="postalCode" value="<?php echo $user->postalCode ?? ''; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="country">Country</label>
                                    <select id="country" name="country" required>
                                        <option value="">Select Country</option>
                                        <option value="US" <?php echo ($user->country ?? '') === 'US' ? 'selected' : ''; ?>>United States</option>
                                        <option value="CA" <?php echo ($user->country ?? '') === 'CA' ? 'selected' : ''; ?>>Canada</option>
                                        <option value="UK" <?php echo ($user->country ?? '') === 'UK' ? 'selected' : ''; ?>>United Kingdom</option>
                                        <!-- Add more countries as needed -->
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group checkbox-group">
                                <label>
                                    <input type="checkbox" id="save-address" name="saveAddress" checked>
                                    Save this address for future orders
                                </label>
                            </div>
                        </div>

                        <!-- Shipping Method -->
                        <div class="checkout-section">
                            <h2>Shipping Method</h2>
                            <div class="shipping-options">
                                <?php foreach ($shippingOptions as $option): ?>
                                <label class="shipping-option">
                                    <input type="radio" name="shipping" value="<?php echo $option->id; ?>" 
                                           <?php echo $option->id === $selectedShipping ? 'checked' : ''; ?>>
                                    <div class="option-details">
                                        <div class="option-name"><?php echo $option->name; ?></div>
                                        <div class="option-price">$<?php echo number_format($option->cost, 2); ?></div>
                                    </div>
                                    <div class="option-description"><?php echo $option->description; ?></div>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="checkout-section">
                            <h2>Payment Method</h2>
                            
                            <div class="payment-options">
                                <label class="payment-option">
                                    <input type="radio" name="paymentMethod" value="credit-card" checked>
                                    <span>Credit / Debit Card</span>
                                </label>
                                <label class="payment-option">
                                    <input type="radio" name="paymentMethod" value="paypal">
                                    <span>PayPal</span>
                                </label>
                            </div>
                            
                            <div id="credit-card-form" class="payment-form">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="cardName">Name on Card</label>
                                        <input type="text" id="cardName" name="cardName" required>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="cardNumber">Card Number</label>
                                    <div class="card-input">
                                        <input type="text" id="cardNumber" name="cardNumber" placeholder="1234 5678 9012 3456" required>
                                        <div class="card-icons">
                                            <i class="fab fa-cc-visa"></i>
                                            <i class="fab fa-cc-mastercard"></i>
                                            <i class="fab fa-cc-amex"></i>
                                            <i class="fab fa-cc-discover"></i>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="expiryDate">Expiry Date</label>
                                        <input type="text" id="expiryDate" name="expiryDate" placeholder="MM/YY" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="cvv">CVV</label>
                                        <input type="text" id="cvv" name="cvv" placeholder="123" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="paypal-form" class="payment-form hidden">
                                <p>You will be redirected to PayPal to complete your payment after reviewing your order.</p>
                            </div>
                        </div>

                        <!-- Order Notes -->
                        <div class="checkout-section">
                            <h2>Order Notes (Optional)</h2>
                            <div class="form-group">
                                <textarea id="orderNotes" name="orderNotes" rows="3" placeholder="Special instructions for delivery or other notes"></textarea>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Order Summary -->
                <div class="order-summary animate-slide-left">
                    <h2>Order Summary</h2>
                    
                    <div class="summary-items">
                        <h3>Items (<?php echo $itemCount; ?>)</h3>
                        <?php foreach ($cartItems as $item): ?>
                        <div class="summary-item">
                            <div class="item-image">
                                <img src="<?php echo $item->image; ?>" alt="<?php echo $item->productName; ?>">
                                <span class="item-quantity"><?php echo $item->quantity; ?></span>
                            </div>
                            <div class="item-details">
                                <h4><?php echo $item->productName; ?></h4>
                                <?php if (!empty($item->selectedSize)): ?>
                                <div class="item-size">Size: <?php echo $item->selectedSize; ?></div>
                                <?php endif; ?>
                                <?php if (!empty($item->selectedColor)): ?>
                                <div class="item-color">Color: <?php echo ucfirst($item->selectedColor); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="item-price">$<?php echo number_format($item->unitCost * $item->quantity, 2); ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="summary-totals">
                        <div class="summary-line">
                            <span>Subtotal</span>
                            <span>$<?php echo number_format($cartSubtotal, 2); ?></span>
                        </div>
                        <div class="summary-line">
                            <span>Shipping</span>
                            <span>$<?php echo number_format($shippingCost, 2); ?></span>
                        </div>
                        <div class="total-line">
                            <span>Total</span>
                            <span>$<?php echo number_format($orderTotal, 2); ?></span>
                        </div>
                    </div>
                    
                    <button type="submit" form="checkout-form" class="btn btn-primary btn-block">Place Order</button>
                    
                    <div class="order-security">
                        <div class="secure-checkout">
                            <i class="fas fa-lock"></i> Secure Checkout
                        </div>
                        <div class="payment-policy">
                            <p>Your personal data will be used to process your order, support your experience, and for other purposes described in our <a href="privacy-policy.php">privacy policy</a>.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
