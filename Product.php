<?php
require_once 'includes/config.php';
require_once 'includes/JsonDatabase.php';
require_once 'includes/functions.php';
require_once 'includes/Website.php';
require_once 'includes/Product.php';
require_once 'includes/Reviews.php';

// Initialize database and classes
$db = new JsonDatabase();
$website = new Website($db);
$productHandler = new Product($db);
$reviews = new Reviews($db);

// Get product ID from URL
$productId = isset($_GET['id']) ? sanitizeInput($_GET['id']) : null;

if (!$productId) {
    header('Location: shop.php');
    exit;
}

// Get product details
$product = $productHandler->getProduct($productId);

if (!$product) {
    header('Location: shop.php');
    exit;
}

// Get product reviews
$productReviews = $reviews->getProductReviews($productId);
$averageRating = $reviews->getAverageRating($productId);
$ratingDistribution = $reviews->getRatingDistribution($productId);

// Get related products
$relatedProducts = $productHandler->getRelatedProducts($productId);

// Set page title
$pageTitle = $product['name'] . " - " . SITE_NAME;

include 'includes/header.php';
?>

<div class="product-header">
    <div class="container">
        <h1><?php echo $product['name']; ?></h1>
        <?php echo generateBreadcrumbs(['Home' => 'index.php', 'Shop' => 'shop.php', $product['name'] => '']); ?>
    </div>
</div>

<div class="product-grid">
    <div class="product-gallery">
        <div class="main-image">
            <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
        </div>
        <div class="thumbnails">
            <?php foreach ($product['gallery'] as $image): ?>
            <div class="thumbnail">
                <img src="<?php echo $image; ?>" alt="<?php echo $product['name']; ?>">
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="product-info">
        <div class="product-price">
            <span class="price">$<?php echo number_format($product['price'], 2); ?></span>
            <?php if (isset($product['oldPrice'])): ?>
            <span class="old-price">$<?php echo number_format($product['oldPrice'], 2); ?></span>
            <?php endif; ?>
        </div>
        
        <div class="product-rating">
            <div class="stars">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <i class="fas fa-star <?php echo $i <= $averageRating ? 'active' : ''; ?>"></i>
                <?php endfor; ?>
            </div>
            <span class="rating-count">(<?php echo count($productReviews); ?> reviews)</span>
        </div>
        
        <div class="product-serial">
            <span>Serial Number: <?php echo $product['serialNumber']; ?></span>
        </div>
        
        <div class="product-description">
            <?php echo $product['description']; ?>
        </div>
        
        <form class="product-form" method="POST" action="process/cart.php">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
            
            <?php if (isset($product['sizes'])): ?>
            <div class="form-group">
                <label for="size">Size</label>
                <select name="size" id="size" required>
                    <option value="">Select Size</option>
                    <?php foreach ($product['sizes'] as $size): ?>
                    <option value="<?php echo $size; ?>"><?php echo $size; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            
            <?php if (isset($product['colors'])): ?>
            <div class="form-group">
                <label for="color">Color</label>
                <select name="color" id="color" required>
                    <option value="">Select Color</option>
                    <?php foreach ($product['colors'] as $color): ?>
                    <option value="<?php echo $color; ?>"><?php echo $color; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="quantity">Quantity</label>
                <div class="quantity-selector">
                    <button type="button" class="quantity-btn minus">-</button>
                    <input type="number" name="quantity" id="quantity" value="1" min="1" max="10" required>
                    <button type="button" class="quantity-btn plus">+</button>
                </div>
            </div>
            
            <button type="submit" name="add_to_cart" class="btn btn-primary">Add to Cart</button>
        </form>
    </div>
</div>

<div class="product-details">
    <div class="tabs">
        <button class="tab-btn active" data-tab="specifications">Specifications</button>
        <button class="tab-btn" data-tab="authenticity">Authenticity</button>
        <button class="tab-btn" data-tab="reviews">Reviews</button>
    </div>
    
    <div class="tab-content">
        <div class="tab-pane active" id="specifications">
            <h3>Product Specifications</h3>
            <ul class="specs-list">
                <?php foreach ($product['specifications'] as $spec): ?>
                <li>
                    <span class="spec-name"><?php echo $spec['name']; ?></span>
                    <span class="spec-value"><?php echo $spec['value']; ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <div class="tab-pane" id="authenticity">
            <h3>Product Authenticity</h3>
            <div class="authenticity-info">
                <p>Each Nox Apparel product comes with a unique QR code that allows you to verify its authenticity.</p>
                <div class="qr-code">
                    <img src="<?php echo $product['qrCode']; ?>" alt="Product QR Code">
                </div>
                <a href="verify.php" class="btn btn-outline">Verify Product</a>
            </div>
        </div>
        
        <div class="tab-pane" id="reviews">
            <h3>Customer Reviews</h3>
            <div class="reviews-summary">
                <div class="average-rating">
                    <div class="rating-number"><?php echo $averageRating; ?></div>
                    <div class="stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star <?php echo $i <= $averageRating ? 'active' : ''; ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <div class="rating-count"><?php echo count($productReviews); ?> reviews</div>
                </div>
                
                <div class="rating-bars">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                    <div class="rating-bar">
                        <span class="stars"><?php echo $i; ?> <i class="fas fa-star"></i></span>
                        <div class="bar">
                            <div class="fill" style="width: <?php echo ($ratingDistribution[$i] / count($productReviews)) * 100; ?>%"></div>
                        </div>
                        <span class="count"><?php echo $ratingDistribution[$i]; ?></span>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
            
            <div class="reviews-list">
                <?php foreach ($productReviews as $review): ?>
                <div class="review-item">
                    <div class="review-header">
                        <div class="reviewer-info">
                            <span class="reviewer-name"><?php echo $review['userName']; ?></span>
                            <span class="review-date"><?php echo formatDate($review['date']); ?></span>
                        </div>
                        <div class="review-rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'active' : ''; ?>"></i>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="review-content">
                        <?php echo $review['comment']; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($relatedProducts)): ?>
<div class="related-products">
    <div class="container">
        <h2>Related Products</h2>
        <div class="products-grid">
            <?php foreach ($relatedProducts as $related): ?>
            <div class="product-card">
                <div class="product-image">
                    <img src="<?php echo $related['image']; ?>" alt="<?php echo $related['name']; ?>">
                    <div class="product-overlay">
                        <a href="product.php?id=<?php echo $related['id']; ?>" class="btn btn-outline">View Details</a>
                    </div>
                </div>
                <div class="product-info">
                    <h3><?php echo $related['name']; ?></h3>
                    <div class="product-price">$<?php echo number_format($related['price'], 2); ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const tabId = btn.dataset.tab;
            
            // Update active tab button
            tabBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            // Update active tab pane
            tabPanes.forEach(pane => {
                pane.classList.remove('active');
                if (pane.id === tabId) {
                    pane.classList.add('active');
                }
            });
        });
    });
    
    // Quantity selector
    const quantityInput = document.getElementById('quantity');
    const minusBtn = document.querySelector('.quantity-btn.minus');
    const plusBtn = document.querySelector('.quantity-btn.plus');
    
    minusBtn.addEventListener('click', () => {
        const currentValue = parseInt(quantityInput.value);
        if (currentValue > 1) {
            quantityInput.value = currentValue - 1;
        }
    });
    
    plusBtn.addEventListener('click', () => {
        const currentValue = parseInt(quantityInput.value);
        if (currentValue < 10) {
            quantityInput.value = currentValue + 1;
        }
    });
    
    // Gallery image switching
    const mainImage = document.querySelector('.main-image img');
    const thumbnails = document.querySelectorAll('.thumbnail img');
    
    thumbnails.forEach(thumb => {
        thumb.addEventListener('click', () => {
            mainImage.src = thumb.src;
            thumbnails.forEach(t => t.parentElement.classList.remove('active'));
            thumb.parentElement.classList.add('active');
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
