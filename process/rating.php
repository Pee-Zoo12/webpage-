<?php
require_once '../includes/config.php';
require_once '../includes/Rating.php';
require_once '../includes/functions.php';

// Check if user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Please log in to rate products'
    ]);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request data'
    ]);
    exit;
}

// Initialize Rating class
$rating = new Rating($db);

// Prepare rating data
$ratingData = [
    'productID' => $data['productID'],
    'customerID' => $_SESSION['user_id'],
    'rating' => $data['rating']
];

// Submit rating
$result = $rating->submitRating($ratingData);

if ($result) {
    // Get updated rating information
    $ratingInfo = $rating->getProductRating($data['productID']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Rating submitted successfully',
        'ratingInfo' => $ratingInfo
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error submitting rating'
    ]);
} 