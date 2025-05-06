<?php
require_once 'includes/config.php';
require_once 'includes/JsonDatabase.php';
require_once 'includes/functions.php';

// Get order ID from URL
$orderId = isset($_GET['id']) ? sanitize($_GET['id']) : null;

if (!$orderId) {
    redirect('shop.php');
}

// Initialize database
$db = new JsonDatabase();

// Get order details
$order = $db->getOrder($orderId);

if (!$order) {
    redirect('shop.php');
}

$pageTitle = 'Order Confirmation - ' . SITE_NAME;

include 'includes/header.php';
?>

<div class="confirmation-header">
    <div class="container">
        <h1>Order Confirmation</h1>
        <?php echo generateBreadcrumbs(['Home' => 'index.php', 'Order Confirmation' => 'order-confirmation.php?id=' . $orderId]); ?>
    </div>
</div>

<div class="confirmation-content">
    <div class="container">
        <div class="confirmation-grid">
            <!-- Order Status -->
            <div class="confirmation-status">
                <div class="status-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2>Thank You for Your Order!</h2>
                <p>Your order has been received and is being processed.</p>
                <div class="order-number">
                    Order #<?php echo $order['orderID']; ?>
                </div>
            </div>

            <!-- Order Details -->
            <div class="order-details">
                <div class="details-section">
                    <h3>Order Information</h3>
                    <div class="details-grid">
                        <div class="detail-item">
                            <span class="label">Order Date:</span>
                            <span class="value"><?php echo formatDate($order['orderDate']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Order Status:</span>
                            <span class="value status-<?php echo $order['status']; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Payment Method:</span>
                            <span class="value">
                                <i class="fab fa-cc-<?php echo $order['payment']['cardType']; ?>"></i>
                                **** **** **** <?php echo $order['payment']['cardLast4']; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="details-section">
                    <h3>Shipping Information</h3>
                    <div class="shipping-address">
                        <p>
                            <?php echo $order['shipping']['firstName'] . ' ' . $order['shipping']['lastName']; ?><br>
                            <?php echo $order['shipping']['address']; ?><br>
                            <?php echo $order['shipping']['city'] . ', ' . $order['shipping']['state'] . ' ' . $order['shipping']['zipCode']; ?><br>
                            <?php echo $order['shipping']['country']; ?>
                        </p>
                        <p>
                            Email: <?php echo $order['shipping']['email']; ?><br>
                            Phone: <?php echo $order['shipping']['phone']; ?>
                        </p>
                    </div>
                </div>

                <div class="details-section">
                    <h3>Order Items</h3>
                    <div class="order-items">
                        <?php foreach ($order['items'] as $item): ?>
                        <div class="order-item">
                            <div class="item-image">
                                <img src="<?php echo $item['product']['images'][0]; ?>" 
                                     alt="<?php echo $item['product']['productName']; ?>">
                            </div>
                            <div class="item-details">
                                <h4><?php echo $item['product']['productName']; ?></h4>
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
                </div>

                <div class="details-section">
                    <h3>Order Summary</h3>
                    <div class="order-summary">
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span><?php echo formatPrice($order['totals']['subtotal']); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping</span>
                            <span><?php echo $order['totals']['shipping'] === 0 ? 'Free' : formatPrice($order['totals']['shipping']); ?></span>
                        </div>
                        <div class="summary-row total">
                            <span>Total</span>
                            <span><?php echo formatPrice($order['totals']['total']); ?></span>
                        </div>
                    </div>
                </div>

                <?php if (!empty($order['notes'])): ?>
                <div class="details-section">
                    <h3>Order Notes</h3>
                    <p class="order-notes"><?php echo $order['notes']; ?></p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Next Steps -->
            <div class="next-steps">
                <h3>What's Next?</h3>
                <div class="steps-grid">
                    <div class="step-item">
                        <div class="step-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h4>Order Confirmation</h4>
                        <p>You will receive an email confirmation with your order details.</p>
                    </div>
                    <div class="step-item">
                        <div class="step-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <h4>Order Processing</h4>
                        <p>We will process your order and prepare it for shipping.</p>
                    </div>
                    <div class="step-item">
                        <div class="step-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <h4>Shipping</h4>
                        <p>You will receive a shipping confirmation with tracking information.</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="confirmation-actions">
                <a href="shop.php" class="btn btn-primary">Continue Shopping</a>
                <?php if (isLoggedIn()): ?>
                <a href="account.php?tab=orders" class="btn btn-outline">View Order History</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 