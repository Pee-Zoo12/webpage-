<?php
/**
 * Helper functions for Nox Apparel website
 */

/**
 * Sanitize user input
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate a random string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

/**
 * Format price
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
    return isset($_SESSION['user_id']);
}

/**
 * Get current user
 */
function getCurrentUser() {
    if (isLoggedIn()) {
        $db = new JsonDatabase();
        return $db->getUser($_SESSION['user_id']);
    }
    return null;
}

/**
 * Redirect to a URL
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Display flash message
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Validate email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Generate pagination links
 */
function generatePagination($currentPage, $totalPages, $url) {
    $links = '';
    
    if ($totalPages > 1) {
        $links .= '<div class="pagination">';
        
        // Previous button
        if ($currentPage > 1) {
            $links .= '<a href="' . $url . '?page=' . ($currentPage - 1) . '" class="page-link">&laquo; Previous</a>';
        }
        
        // Page numbers
        for ($i = 1; $i <= $totalPages; $i++) {
            if ($i == $currentPage) {
                $links .= '<span class="page-link active">' . $i . '</span>';
            } else {
                $links .= '<a href="' . $url . '?page=' . $i . '" class="page-link">' . $i . '</a>';
            }
        }
        
        // Next button
        if ($currentPage < $totalPages) {
            $links .= '<a href="' . $url . '?page=' . ($currentPage + 1) . '" class="page-link">Next &raquo;</a>';
        }
        
        $links .= '</div>';
    }
    
    return $links;
}

/**
 * Generate breadcrumbs
 */
function generateBreadcrumbs($items) {
    $breadcrumbs = '<div class="breadcrumbs">';
    $lastItem = end($items);
    
    foreach ($items as $label => $url) {
        if ($url === $lastItem) {
            $breadcrumbs .= '<span class="current">' . $label . '</span>';
        } else {
            $breadcrumbs .= '<a href="' . $url . '">' . $label . '</a> &raquo; ';
        }
    }
    
    $breadcrumbs .= '</div>';
    return $breadcrumbs;
}

/**
 * Format date
 */
function formatDate($date, $format = 'F j, Y') {
    return date($format, strtotime($date));
}

/**
 * Truncate text
 */
function truncateText($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

/**
 * Check if request is AJAX
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

/**
 * Send JSON response
 */
function sendJsonResponse($data, $status = 200) {
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode($data);
    exit();
}

/**
 * Validate file upload
 */
function validateFileUpload($file, $allowedTypes, $maxSize) {
    $errors = [];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'File upload failed';
        return $errors;
    }
    
    if (!in_array($file['type'], $allowedTypes)) {
        $errors[] = 'Invalid file type';
    }
    
    if ($file['size'] > $maxSize) {
        $errors[] = 'File size exceeds limit';
    }
    
    return $errors;
}

/**
 * Generate QR code data
 */
function generateQRCodeData($productId, $serialNumber) {
    return json_encode([
        'product_id' => $productId,
        'serial_number' => $serialNumber,
        'timestamp' => time()
    ]);
}

/**
 * Verify QR code data
 */
function verifyQRCodeData($data) {
    $decoded = json_decode($data, true);
    if (!$decoded) {
        return false;
    }
    
    // Add your verification logic here
    return true;
}
