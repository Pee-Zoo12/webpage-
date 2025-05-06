<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'classes/JsonDatabase.php';
require_once 'classes/Website.php';
require_once 'classes/Product.php';
require_once 'classes/QRCode.php';
require_once 'classes/Reviews.php';

// Initialize site
$site = new Website();
$db = new JsonDatabase();

// Get product ID
$productID = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get product data
$product = $db->getProductById($productID);

// If product not found, redirect to shop
if (!$product) {
    header('Location: shop.php');
    exit;
}

// Generate QR code
$qrCode = new QRCode();
$qrCode->serialNumber = $product->serialNumber;
$qrCode->authenticityCode = $product->authenticityCode;

// Get related products
$relatedProducts = $db->getRelatedProducts($product, 4);

// Get product reviews
$reviews = $db->getProductReviews($productID);
$reviewCount = count($reviews);
$averageRating = $reviewCount > 0 ? array_sum(array_column($reviews, 'rating')) / $reviewCount : 0;

// Page title
$pageTitle = $product->productName . " | Nox Apparel";
include 'includes/header.php';
?>

<main>
    <!-- Product Details -->
    <section class="product-details">
        <div class="container">
            <div class="product-grid">
                <!-- Product Images -->
                <div class="product-images animate-slide-right">
                    <div class="main-image">
                        <img id="main-product-image" src="<?php echo $product->image; ?>" alt="<?php echo $product->productName; ?>">
                    </div>
                    <?php if (!empty($product->gallery)): ?>
                    <div class="thumbnail-images">
                        <div class="thumbnail active" data-image="<?php echo $product->image; ?>">
                            <img src="<?php echo $product->image; ?>" alt="<?php echo $product->productName; ?>">
                        </div>
                        <?php foreach ($product->gallery as $image): ?>
                        <div class="thumbnail" data-image="<?php echo $image; ?>">
                            <img src="<?php echo $image; ?>" alt="<?php echo $product->productName; ?>">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Product Info -->
                <div class="product-info animate-slide-left">
                    <nav class="breadcrumb">
                        <a href="index.php">Home</a> &gt;
                        <a href="shop.php">Shop</a> &gt;
                        <a href="shop.php?category=<?php echo urlencode($product->type); ?>"><?php echo $product->type; ?></a> &gt;
                        <span><?php echo $product->productName; ?></span>
                    </nav>

                    <h1><?php echo $product->productName; ?></h1>
                    
                    <div class="product-meta">
                        <div class="product-price">$<?php echo number_format($product->unitCost, 2); ?></div>
                        
                        <?php if ($reviewCount > 0): ?>
                        <div class="product-rating">
                            <div class="stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= round($averageRating)): ?>
                                        <i class="fas fa-star"></i>
                                    <?php else: ?>
                                        <i class="far fa-star"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                            <span>(<?php echo $reviewCount; ?> reviews)</span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="product-description">
                        <?php echo $product->description; ?>
                    </div>

                    <?php if (!empty($product->features)): ?>
                    <div class="product-features">
                        <h3>Features</h3>
                        <ul>
                            <?php foreach ($product->features as $feature): ?>
                            <li><?php echo $feature; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($product->sizes)): ?>
                    <div class="product-variant">
                        <h3>Size</h3>
                        <div class="size-options">
                            <?php foreach ($product->sizes as $size): ?>
                            <label class="size-option">
                                <input type="radio" name="size" value="<?php echo $size; ?>" <?php echo $size === $product->sizes[0] ? 'checked' : ''; ?>>
                                <span><?php echo $size; ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($product->colors)): ?>
                    <div class="product-variant">
                        <h3>Color</h3>
                        <div class="color-options">
                            <?php foreach ($product->colors as $color => $code): ?>
                            <label class="color-option" title="<?php echo ucfirst($color); ?>">
                                <input type="radio" name="color" value="<?php echo $color; ?>" <?php echo $color === array_key_first($product->colors) ? 'checked' : ''; ?>>
                                <span style="background-color: <?php echo $code; ?>"></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="product-actions">
                        <div class="quantity-selector">
                            <button class="quantity-btn minus">-</button>
                            <input type="number" id="product-quantity" value="1" min="1" max="<?php echo $product->stockQuantity; ?>">
                            <button class="quantity-btn plus">+</button>
                        </div>

                        <button id="add-to-cart" class="btn btn-primary" data-product-id="<?php echo $product->productID; ?>">
                            Add to Cart
                        </button>
                    </div>

                    <div class="stock-info <?php echo $product->stockQuantity > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                        <?php if ($product->stockQuantity > 0): ?>
                            <i class="fas fa-check-circle"></i> In Stock
                        <?php else: ?>
                            <i class="fas fa-times-circle"></i> Out of Stock
                        <?php endif; ?>
                    </div>

                    <div class="product-authenticity">
                        <h3>Authenticity Guaranteed</h3>
                        <p>Each Nox Apparel product comes with a unique QR code for authenticity verification.</p>
                        <div class="qr-preview">
                            <img src="assets/images/qr-sample.svg" alt="QR Code Sample">
                        </div>
                        <a href="authenticity.php" class="btn btn-text">Learn More</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Product Tabs -->
    <section class="product-tabs">
        <div class="container">
            <div class="tabs">
                <div class="tab-buttons">
                    <button class="tab-btn active" data-tab="details">Details</button>
                    <button class="tab-btn" data-tab="specifications">Specifications</button>
                    <button class="tab-btn" data-tab="reviews">Reviews (<?php echo $reviewCount; ?>)</button>
                </div>

                <!-- Details Tab -->
                <div class="tab-content active" id="details-tab">
                    <div class="tab-grid">
                        <div class="tab-text animate-on-scroll">
                            <h3>Product Details</h3>
                            <?php echo $product->detailedDescription; ?>
                        </div>
                        <div class="tab-image animate-on-scroll">
                            <img src="<?php echo !empty($product->detailImage) ? $product->detailImage : $product->image; ?>" alt="<?php echo $product->productName; ?> Details">
                        </div>
                    </div>
                </div>

                <!-- Specifications Tab -->
                <div class="tab-content" id="specifications-tab">
                    <div class="specifications animate-on-scroll">
                        <h3>Technical Specifications</h3>
                        <table class="specs-table">
                            <?php foreach ($product->specifications as $spec => $value): ?>
                            <tr>
                                <th><?php echo $spec; ?></th>
                                <td><?php echo $value; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                </div>

                <!-- Reviews Tab -->
                <div class="tab-content" id="reviews-tab">
                    <div class="reviews-section animate-on-scroll">
                        <div class="reviews-summary">
                            <div class="average-rating">
                                <div class="rating-number"><?php echo number_format($averageRating, 1); ?></div>
                                <div class="rating-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= round($averageRating)): ?>
                                            <i class="fas fa-star"></i>
                                        <?php else: ?>
                                            <i class="far fa-star"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                <div class="rating-count"><?php echo $reviewCount; ?> Reviews</div>
                            </div>
                        </div>

                        <div class="write-review">
                            <button id="write-review-btn" class="btn btn-secondary">Write a Review</button>
                        </div>

                        <div id="review-form-container" class="review-form-container hidden">
                            <form id="review-form" action="process/submit-review.php" method="post">
                                <input type="hidden" name="productID" value="<?php echo $product->productID; ?>">
                                
                                <div class="form-group">
                                    <label for="rating">Rating</label>
                                    <div class="rating-input">
                                        <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>">
                                        <label for="star<?php echo $i; ?>"><i class="far fa-star"></i></label>
                                        <?php endfor; ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="name">Name</label>
                                    <input type="text" id="name" name="name" required>
                                </div>

                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email" required>
                                </div>

                                <div class="form-group">
                                    <label for="reviewContent">Review</label>
                                    <textarea id="reviewContent" name="reviewContent" rows="5" required></textarea>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">Submit Review</button>
                                    <button type="button" id="cancel-review" class="btn btn-text">Cancel</button>
                                </div>
                            </form>
                        </div>

                        <?php if ($reviewCount > 0): ?>
                        <div class="reviews-list">
                            <?php foreach ($reviews as $review): ?>
                            <div class="review-item">
                                <div class="review-header">
                                    <div class="reviewer-name"><?php echo $review->customerName; ?></div>
                                    <div class="review-date"><?php echo date('F j, Y', strtotime($review->date)); ?></div>
                                </div>
                                <div class="review-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= $review->rating): ?>
                                            <i class="fas fa-star"></i>
                                        <?php else: ?>
                                            <i class="far fa-star"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                <div class="review-content">
                                    <?php echo $review->reviewContent; ?>
                                </div>
                                <?php if (!empty($review->images)): ?>
                                <div class="review-images">
                                    <?php foreach ($review->images as $image): ?>
                                    <img src="<?php echo $image; ?>" alt="Review Image">
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="no-reviews">
                            <p>There are no reviews yet. Be the first to review this product!</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Related Products -->
    <?php if (!empty($relatedProducts)): ?>
    <section class="related-products">
        <div class="container">
            <h2 class="section-title">You May Also Like</h2>
            <div class="products-slider">
                <?php foreach ($relatedProducts as $relatedProduct): ?>
                <div class="product-slide animate-on-scroll">
                    <div class="product-image">
                        <img src="<?php echo $relatedProduct->image; ?>" alt="<?php echo $relatedProduct->productName; ?>">
                        <div class="product-overlay">
                            <a href="product.php?id=<?php echo $relatedProduct->productID; ?>" class="btn-view">View</a>
                            <button class="btn-cart" data-product-id="<?php echo $relatedProduct->productID; ?>">Add to Cart</button>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3><?php echo $relatedProduct->productName; ?></h3>
                        <p class="product-price">$<?php echo number_format($relatedProduct->unitCost, 2); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>
