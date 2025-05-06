<?php
// Site configuration
define('SITE_NAME', 'Nox Apparel');
define('SITE_URL', 'http://localhost/webapp');
define('SITE_EMAIL', 'contact@noxapparel.com');
define('SITE_PHONE', '+1 (555) 123-4567');
define('SITE_ADDRESS', '123 Fashion District, New York, NY 10001');

// Theme Colors
define('COLOR_DARK', '#222222');
define('COLOR_GREY', '#4A4A4A');
define('COLOR_LIGHT_GREY', '#6A6A6A');
define('COLOR_OLIVE', '#708238');
define('COLOR_LIGHT_OLIVE', '#A8B875');
define('COLOR_WHITE', '#F8F8F8');
define('COLOR_OFF_WHITE', '#E5E5E5');

// Database Configuration
define('DB_FILE', __DIR__ . '/../data/database.json');

// Session Configuration
session_start();

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Time Zone
date_default_timezone_set('UTC');

// Security
define('HASH_COST', 12); // For password hashing

// File Upload Configuration
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);

// Cart Configuration
define('CART_EXPIRY', 24 * 60 * 60); // 24 hours in seconds

// Pagination
define('ITEMS_PER_PAGE', 12);

// API Keys (if needed)
define('STRIPE_PUBLIC_KEY', 'your_stripe_public_key');
define('STRIPE_SECRET_KEY', 'your_stripe_secret_key');

// Social Media Links
define('SOCIAL_MEDIA', [
    'instagram' => 'https://instagram.com/noxapparel',
    'facebook' => 'https://facebook.com/noxapparel',
    'twitter' => 'https://twitter.com/noxapparel'
]);

// Company Information
define('COMPANY_INFO', [
    'name' => 'Nox Apparel',
    'address' => '123 Fashion Street, Style City',
    'phone' => '+1 (555) 123-4567',
    'email' => 'contact@noxapparel.com'
]);
