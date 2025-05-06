<?php
require_once '../includes/config.php';
require_once '../includes/JsonDatabase.php';
require_once '../includes/functions.php';

// Check if request is AJAX
if (!isAjaxRequest()) {
    sendJsonResponse(['success' => false, 'message' => 'Invalid request method'], 400);
    exit;
}

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    sendJsonResponse(['success' => false, 'message' => 'Invalid data format'], 400);
    exit;
}

// Initialize database
$db = new JsonDatabase();

// Handle different cart actions
switch ($data['action']) {
    case 'add':
        if (!isset($data['productId']) || !isset($data['quantity'])) {
            sendJsonResponse(['success' => false, 'message' => 'Missing required fields'], 400);
            exit;
        }

        // Get product
        $product = $db->getProduct($data['productId']);
        if (!$product) {
            sendJsonResponse(['success' => false, 'message' => 'Product not found'], 404);
            exit;
        }

        // Check if user is logged in
        if (!isLoggedIn()) {
            // Store in session cart
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }

            // Check if product already in cart
            $found = false;
            foreach ($_SESSION['cart'] as &$item) {
                if ($item['productId'] === $data['productId']) {
                    $item['quantity'] += $data['quantity'];
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $_SESSION['cart'][] = [
                    'productId' => $data['productId'],
                    'quantity' => $data['quantity'],
                    'price' => $product['unitCost']
                ];
            }

            $cartCount = array_sum(array_column($_SESSION['cart'], 'quantity'));
        } else {
            // Add to database cart
            $cartItem = [
                'cartID' => generateRandomString(),
                'userID' => $_SESSION['user_id'],
                'productID' => $data['productId'],
                'quantity' => $data['quantity'],
                'addedAt' => date('Y-m-d H:i:s')
            ];

            $db->addToCart($cartItem);
            $cartCount = count($db->getCart($_SESSION['user_id']));
        }

        sendJsonResponse([
            'success' => true,
            'message' => 'Product added to cart',
            'cartCount' => $cartCount
        ]);
        break;

    case 'update':
        if (!isset($data['cartId']) || !isset($data['quantity'])) {
            sendJsonResponse(['success' => false, 'message' => 'Missing required fields'], 400);
            exit;
        }

        if (isLoggedIn()) {
            $db->updateCart($data['cartId'], $data['quantity']);
        } else {
            foreach ($_SESSION['cart'] as &$item) {
                if ($item['cartId'] === $data['cartId']) {
                    $item['quantity'] = $data['quantity'];
                    break;
                }
            }
        }

        sendJsonResponse(['success' => true, 'message' => 'Cart updated']);
        break;

    case 'remove':
        if (!isset($data['cartId'])) {
            sendJsonResponse(['success' => false, 'message' => 'Missing cart item ID'], 400);
            exit;
        }

        if (isLoggedIn()) {
            $db->removeFromCart($data['cartId']);
        } else {
            $_SESSION['cart'] = array_filter($_SESSION['cart'], function($item) use ($data) {
                return $item['cartId'] !== $data['cartId'];
            });
        }

        sendJsonResponse(['success' => true, 'message' => 'Item removed from cart']);
        break;

    default:
        sendJsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
        break;
} 