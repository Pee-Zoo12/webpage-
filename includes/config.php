<?php
// Site configuration
define('SITE_NAME', 'Nox Apparel');
define('SITE_URL', 'http://localhost/nox-apparel');
define('SITE_EMAIL', 'info@noxapparel.com');
define('SITE_PHONE', '+1 (555) 123-4567');
define('SITE_ADDRESS', '123 Fashion District, New York, NY 10001');

// Social media links
define('SOCIAL_INSTAGRAM', 'https://instagram.com/noxapparel');
define('SOCIAL_FACEBOOK', 'https://facebook.com/noxapparel');
define('SOCIAL_TWITTER', 'https://twitter.com/noxapparel');
define('SOCIAL_PINTEREST', 'https://pinterest.com/noxapparel');

// Database settings (for future use if migrating from JSON)
define('DB_HOST', 'localhost');
define('DB_NAME', 'nox_apparel');
define('DB_USER', 'root');
define('DB_PASS', '');

// Session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Time zone
date_default_timezone_set('America/New_York');
