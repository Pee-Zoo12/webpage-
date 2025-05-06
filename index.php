<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/JsonDatabase.php';
require_once 'Product.php';

// Initialize site
$site = new Website();
$db = new JsonDatabase();
$featuredProducts = $db->getProducts(4, 'featured');
$newArrivals = $db->getProducts(8, 'new');

// Page title
$pageTitle = "Home | Nox Apparel";
include 'header.php';
?>

<main>
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1 class="animate-text">NOX APPAREL</h1>
            <p class="animate-text-delay">Elevate Your Style. Embrace The Darkness.</p>
            <div class="hero-buttons animate-fade-in">
                <a href="shop.php" class="btn btn-primary">Shop Now</a>
                <a href="collections.php" class="btn btn-outline">View Collections</a>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="featured-products">
        <div class="container">
            <h2 class="section-title">Featured Items</h2>
            <div class="products-grid">
                <?php foreach ($featuredProducts as $product): ?>
                <div class="product-card animate-on-scroll">
                    <div class="product-image">
                        <img src="<?php echo $product->image; ?>" alt="<?php echo $product->productName; ?>">
                        <div class="product-overlay">
                            <a href="product.php?id=<?php echo $product->productID; ?>" class="btn-view">View</a>
                            <button class="btn-cart" data-product-id="<?php echo $product->productID; ?>">Add to Cart</button>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3><?php echo $product->productName; ?></h3>
                        <p class="product-price">$<?php echo number_format($product->unitCost, 2); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="view-more">
                <a href="shop.php" class="btn btn-secondary">View All Products</a>
            </div>
        </div>
    </section>

    <!-- Brand Ethos -->
    <section class="brand-ethos">
        <div class="container">
            <div class="ethos-grid">
                <div class="ethos-image animate-on-scroll">
                    <img src="assets/images/brand-ethos.jpg" alt="Nox Apparel Ethos">
                </div>
                <div class="ethos-content animate-on-scroll">
                    <h2>Our Philosophy</h2>
                    <p>At Nox Apparel, we believe in the power of darkness to highlight your unique style. Our designs blend urban aesthetics with avant-garde sensibilities, creating pieces that stand out in any setting.</p>
                    <p>Each garment is crafted with precision and passion, ensuring quality that matches our artistic vision. We use sustainable materials and ethical production methods to minimize our environmental footprint.</p>
                    <a href="about.php" class="btn btn-text">Learn More</a>
                </div>
            </div>
        </div>
    </section>

    <!-- New Arrivals -->
    <section class="new-arrivals">
        <div class="container">
            <h2 class="section-title">New Arrivals</h2>
            <div class="products-slider">
                <?php foreach ($newArrivals as $product): ?>
                <div class="product-slide animate-on-scroll">
                    <div class="product-image">
                        <img src="<?php echo $product->image; ?>" alt="<?php echo $product->productName; ?>">
                        <div class="product-overlay">
                            <a href="product.php?id=<?php echo $product->productID; ?>" class="btn-view">View</a>
                            <button class="btn-cart" data-product-id="<?php echo $product->productID; ?>">Add to Cart</button>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3><?php echo $product->productName; ?></h3>
                        <p class="product-price">$<?php echo number_format($product->unitCost, 2); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Authenticity Section -->
    <section class="authenticity">
        <div class="container">
            <div class="authenticity-content animate-on-scroll">
                <h2>Guaranteed Authenticity</h2>
                <p>Every Nox Apparel item comes with a unique QR code that verifies its authenticity. Scan to confirm your purchase is genuine and track its journey from our studio to your wardrobe.</p>
                <a href="authenticity.php" class="btn btn-primary">Learn About Our QR System</a>
            </div>
        </div>
    </section>

    <!-- Newsletter -->
    <section class="newsletter">
        <div class="container">
            <div class="newsletter-content animate-on-scroll">
                <h2>Join The Darkness</h2>
                <p>Subscribe to our newsletter for exclusive offers, early access to new collections, and style inspiration.</p>
                <form id="newsletter-form" action="process/newsletter.php" method="post">
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Your email address" required>
                        <button type="submit" class="btn btn-primary">Subscribe</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include 'footer.php'; ?>
