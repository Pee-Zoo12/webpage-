<?php
/**
 * Helper functions for Nox Apparel website
 */

/**
 * Clean and sanitize input data
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Format price with currency symbol
 */
function formatPrice($price) {
    return '$' . number_format($price, 2);
}

/**
 * Generate URL with existing query parameters plus new ones
 */
function buildUrl($newParams = []) {
    $params = $_GET;
    
    foreach ($newParams as $key => $value) {
        $params[$key] = $value;
    }
    
    $queryString = http_build_query($params);
    $currentPath = strtok($_SERVER['REQUEST_URI'], '?');
    
    return $currentPath . ($queryString ? '?' . $queryString : '');
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['userID']);
}

/**
 * Get current user data
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = new JsonDatabase();
    return $db->getUserById($_SESSION['userID']);
}

/**
 * Redirect to a page
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Flash messages for success/errors
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Generate a unique token (for CSRF protection)
 */
function generateToken() {
    if (!isset($_SESSION['token'])) {
        $_SESSION['token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['token'];
}

/**
 * Verify token
 */
function verifyToken($token) {
    if (!isset($_SESSION['token']) || $token !== $_SESSION['token']) {
        return false;
    }
    return true;
}

/**
 * Truncate text to a certain length
 */
function truncateText($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    $text = substr($text, 0, $length);
    $text = substr($text, 0, strrpos($text, ' '));
    return $text . '...';
}

/**
 * Get cart item count
 */
function getCartItemCount() {
    if (!isset($_SESSION['cart'])) {
        return 0;
    }
    
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $count += $item['quantity'];
    }
    
    return $count;
}

/**
 * Generate breadcrumbs
 */
function generateBreadcrumbs($items) {
    $html = '<nav class="breadcrumb">';
    $i = 0;
    
    foreach ($items as $label => $url) {
        if ($i > 0) {
            $html .= ' &gt; ';
        }
        
        if ($url) {
            $html .= '<a href="' . $url . '">' . $label . '</a>';
        } else {
            $html .= '<span>' . $label . '</span>';
        }
        
        $i++;
    }
    
    $html .= '</nav>';
    return $html;
}
