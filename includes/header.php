<?php
if (!isset($pageTitle)) {
    $pageTitle = SITE_NAME;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Custom JS -->
    <script src="assets/js/script.js" defer></script>
</head>
<body>
    <!-- Loading Screen -->
    <div class="loading-screen">
        <div class="loader">
            <div class="loader-text">NOX</div>
        </div>
    </div>

    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">NOX</a>
                
                <nav>
                    <ul class="nav-links">
                        <li><a href="shop.php">Shop</a></li>
                        <li><a href="collections.php">Collections</a></li>
                        <li><a href="about.php">About</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </nav>
                
                <div class="header-actions">
                    <a href="search.php" class="search-icon">
                        <i class="fas fa-search"></i>
                    </a>
                    
                    <?php if (isLoggedIn()): ?>
                        <a href="account.php" class="account-icon">
                            <i class="fas fa-user"></i>
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="account-icon">
                            <i class="fas fa-user"></i>
                        </a>
                    <?php endif; ?>
                    
                    <a href="cart.php" class="cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                        <?php
                        $cartCount = 0;
                        if (isset($_SESSION['cart'])) {
                            foreach ($_SESSION['cart'] as $item) {
                                $cartCount += $item['quantity'];
                            }
                        }
                        if ($cartCount > 0):
                        ?>
                        <span class="cart-count"><?php echo $cartCount; ?></span>
                        <?php endif; ?>
                    </a>
                    
                    <button class="mobile-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Flash Messages -->
    <?php
    $flash = getFlashMessage();
    if ($flash):
    ?>
    <div class="flash-message <?php echo $flash['type']; ?>">
        <?php echo $flash['message']; ?>
    </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main> 