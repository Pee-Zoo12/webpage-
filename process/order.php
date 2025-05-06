<?php
require_once '../includes/config.php';
require_once '../includes/JsonDatabase.php';
require_once '../includes/functions.php';

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('checkout.php');
}

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

// Get product details and calculate totals
foreach ($cartItems as &$item) {
    $product = $db->getProduct($item['productId']);
    if ($product) {
        $item['product'] = $product;
        $item['subtotal'] = $product['unitCost'] * $item['quantity'];
        $total += $item['subtotal'];
    }
}

// Calculate shipping cost
$shippingCost = $total >= 100 ? 0 : 10;
$finalTotal = $total + $shippingCost;

// Validate form data
$requiredFields = ['firstName', 'lastName', 'email', 'phone', 'address', 'city', 'state', 'zipCode', 'country',
                  'cardNumber', 'expiryDate', 'cvv', 'cardName'];

foreach ($requiredFields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        setFlashMessage('error', 'Please fill in all required fields.');
        redirect('checkout.php');
    }
}

// Validate email
if (!isValidEmail($_POST['email'])) {
    setFlashMessage('error', 'Please enter a valid email address.');
    redirect('checkout.php');
}

// Validate card number (simple validation)
if (!preg_match('/^[0-9]{16}$/', $_POST['cardNumber'])) {
    setFlashMessage('error', 'Please enter a valid card number.');
    redirect('checkout.php');
}

// Validate expiry date
if (!preg_match('/^[0-9]{2}\/[0-9]{2}$/', $_POST['expiryDate'])) {
    setFlashMessage('error', 'Please enter a valid expiry date (MM/YY).');
    redirect('checkout.php');
}

// Validate CVV
if (!preg_match('/^[0-9]{3,4}$/', $_POST['cvv'])) {
    setFlashMessage('error', 'Please enter a valid CVV.');
    redirect('checkout.php');
}

// Create order
$order = [
    'orderID' => generateRandomString(),
    'userID' => isLoggedIn() ? $_SESSION['user_id'] : null,
    'orderDate' => date('Y-m-d H:i:s'),
    'status' => 'pending',
    'items' => $cartItems,
    'shipping' => [
        'firstName' => $_POST['firstName'],
        'lastName' => $_POST['lastName'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'],
        'address' => $_POST['address'],
        'city' => $_POST['city'],
        'state' => $_POST['state'],
        'zipCode' => $_POST['zipCode'],
        'country' => $_POST['country']
    ],
    'payment' => [
        'method' => 'credit_card',
        'cardLast4' => substr($_POST['cardNumber'], -4),
        'cardType' => getCardType($_POST['cardNumber'])
    ],
    'totals' => [
        'subtotal' => $total,
        'shipping' => $shippingCost,
        'total' => $finalTotal
    ],
    'notes' => $_POST['orderNotes'] ?? ''
];

// Process payment (simulated)
try {
    // In a real application, you would integrate with a payment gateway here
    // For this example, we'll simulate a successful payment
    
    // Add order to database
    $db->addOrder($order);
    
    // Clear cart
    if (isLoggedIn()) {
        $db->clearCart($_SESSION['user_id']);
    } else {
        unset($_SESSION['cart']);
    }
    
    // Set success message
    setFlashMessage('success', 'Your order has been placed successfully!');
    
    // Redirect to order confirmation page
    redirect('order-confirmation.php?id=' . $order['orderID']);
    
} catch (Exception $e) {
    setFlashMessage('error', 'There was an error processing your order. Please try again.');
    redirect('checkout.php');
}

// Helper function to determine card type
function getCardType($number) {
    $number = preg_replace('/\D/', '', $number);
    
    if (preg_match('/^4/', $number)) {
        return 'visa';
    } elseif (preg_match('/^5[1-5]/', $number)) {
        return 'mastercard';
    } elseif (preg_match('/^3[47]/', $number)) {
        return 'amex';
    } elseif (preg_match('/^6(?:011|5)/', $number)) {
        return 'discover';
    }
    
    return 'unknown';
} 