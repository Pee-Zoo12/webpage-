<?php
require_once 'includes/config.php';
require_once 'includes/JsonDatabase.php';
require_once 'includes/functions.php';

$pageTitle = 'Checkout - ' . SITE_NAME;

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

// If cart is empty, redirect to cart page
if (empty($cartItems)) {
    redirect('cart.php');
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

// Calculate shipping cost (example: free shipping over $100)
$shippingCost = $total >= 100 ? 0 : 10;
$finalTotal = $total + $shippingCost;

include 'includes/header.php';
?>

<div class="checkout-header">
    <div class="container">
        <h1>Checkout</h1>
        <?php echo generateBreadcrumbs(['Home' => 'index.php', 'Cart' => 'cart.php', 'Checkout' => 'checkout.php']); ?>
    </div>
</div>

<div class="checkout-content">
    <div class="container">
        <div class="checkout-grid">
            <!-- Checkout Form -->
            <div class="checkout-form">
                <form id="payment-form" action="process/order.php" method="post">
                    <!-- Shipping Information -->
                    <div class="form-section">
                        <h2>Shipping Information</h2>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="firstName">First Name</label>
                                <input type="text" id="firstName" name="firstName" required>
                            </div>
                            <div class="form-group">
                                <label for="lastName">Last Name</label>
                                <input type="text" id="lastName" name="lastName" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" required>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="tel" id="phone" name="phone" required>
                            </div>
                            <div class="form-group full-width">
                                <label for="address">Address</label>
                                <input type="text" id="address" name="address" required>
                            </div>
                            <div class="form-group">
                                <label for="city">City</label>
                                <input type="text" id="city" name="city" required>
                            </div>
                            <div class="form-group">
                                <label for="state">State</label>
                                <input type="text" id="state" name="state" required>
                            </div>
                            <div class="form-group">
                                <label for="zipCode">ZIP Code</label>
                                <input type="text" id="zipCode" name="zipCode" required>
                            </div>
                            <div class="form-group">
                                <label for="country">Country</label>
                                <select id="country" name="country" required>
                                    <option value="US">United States</option>
                                    <option value="CA">Canada</option>
                                    <option value="UK">United Kingdom</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    <div class="form-section">
                        <h2>Payment Information</h2>
                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label for="cardNumber">Card Number</label>
                                <div class="card-input">
                                    <input type="text" id="cardNumber" name="cardNumber" required 
                                           pattern="[0-9]{16}" maxlength="16" placeholder="1234 5678 9012 3456">
                                    <div class="card-icons">
                                        <i class="fab fa-cc-visa"></i>
                                        <i class="fab fa-cc-mastercard"></i>
                                        <i class="fab fa-cc-amex"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="expiryDate">Expiry Date</label>
                                <input type="text" id="expiryDate" name="expiryDate" required 
                                       pattern="[0-9]{2}/[0-9]{2}" maxlength="5" placeholder="MM/YY">
                            </div>
                            <div class="form-group">
                                <label for="cvv">CVV</label>
                                <input type="text" id="cvv" name="cvv" required 
                                       pattern="[0-9]{3,4}" maxlength="4" placeholder="123">
                            </div>
                            <div class="form-group full-width">
                                <label for="cardName">Name on Card</label>
                                <input type="text" id="cardName" name="cardName" required>
                            </div>
                        </div>
                    </div>

                    <!-- Order Notes -->
                    <div class="form-section">
                        <h2>Order Notes (Optional)</h2>
                        <div class="form-group">
                            <textarea id="orderNotes" name="orderNotes" rows="3" 
                                      placeholder="Add any special instructions or notes for your order..."></textarea>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary place-order-btn">
                        Place Order
                    </button>
                </form>
            </div>

            <!-- Order Summary -->
            <div class="order-summary">
                <div class="summary-card">
                    <h2>Order Summary</h2>
                    
                    <div class="order-items">
                        <?php foreach ($cartItems as $item): ?>
                        <div class="order-item">
                            <div class="item-image">
                                <img src="<?php echo $item['product']['images'][0]; ?>" 
                                     alt="<?php echo $item['product']['productName']; ?>">
                            </div>
                            <div class="item-details">
                                <h3><?php echo $item['product']['productName']; ?></h3>
                                <p class="item-meta">
                                    Size: <?php echo $item['size']; ?><br>
                                    Color: <?php echo $item['color']; ?><br>
                                    Quantity: <?php echo $item['quantity']; ?>
                                </p>
                                <p class="item-price"><?php echo formatPrice($item['subtotal']); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="summary-totals">
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span><?php echo formatPrice($total); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping</span>
                            <span><?php echo $shippingCost === 0 ? 'Free' : formatPrice($shippingCost); ?></span>
                        </div>
                        <div class="summary-row total">
                            <span>Total</span>
                            <span><?php echo formatPrice($finalTotal); ?></span>
                        </div>
                    </div>

                    <div class="secure-checkout">
                        <i class="fas fa-lock"></i>
                        <span>Secure Checkout</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Format card number input
document.getElementById('cardNumber').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 16) value = value.slice(0, 16);
    e.target.value = value;
});

// Format expiry date input
document.getElementById('expiryDate').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 2) {
        value = value.slice(0, 2) + '/' + value.slice(2, 4);
    }
    e.target.value = value;
});

// Format CVV input
document.getElementById('cvv').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 4) value = value.slice(0, 4);
    e.target.value = value;
});

// Form submission
document.getElementById('payment-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Show loading state
    const submitBtn = this.querySelector('.place-order-btn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    
    // Submit form
    this.submit();
});
</script>

<?php include 'includes/footer.php'; ?>
