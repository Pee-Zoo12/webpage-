<?php
require_once 'includes/config.php';
require_once 'includes/JsonDatabase.php';
require_once 'includes/functions.php';

$pageTitle = 'Shop - ' . SITE_NAME;

// Initialize database
$db = new JsonDatabase();

// Get filter parameters
$type = isset($_GET['type']) ? sanitize($_GET['type']) : null;
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'newest';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = ITEMS_PER_PAGE;

// Get products
$products = $db->getProducts();

// Apply filters
if ($type) {
    $products = array_filter($products, function($product) use ($type) {
        return $product['type'] === $type;
    });
}

// Apply sorting
switch ($sort) {
    case 'price-low':
        usort($products, function($a, $b) {
            return $a['unitCost'] <=> $b['unitCost'];
        });
        break;
    case 'price-high':
        usort($products, function($a, $b) {
            return $b['unitCost'] <=> $a['unitCost'];
        });
        break;
    case 'newest':
        // Products are already sorted by newest in the database
        break;
}

// Pagination
$totalProducts = count($products);
$totalPages = ceil($totalProducts / $perPage);
$page = max(1, min($page, $totalPages));
$offset = ($page - 1) * $perPage;
$products = array_slice($products, $offset, $perPage);

include 'includes/header.php';
?>

<div class="shop-header">
    <div class="container">
        <h1>Shop</h1>
        <?php echo generateBreadcrumbs(['Home' => 'index.php', 'Shop' => 'shop.php']); ?>
    </div>
</div>

<div class="shop-content">
    <div class="container">
        <div class="shop-filters">
            <div class="filter-group">
                <label for="type-filter">Category:</label>
                <select id="type-filter" onchange="updateFilters()">
                    <option value="">All Categories</option>
                    <option value="hoodie" <?php echo $type === 'hoodie' ? 'selected' : ''; ?>>Hoodies</option>
                    <option value="tshirt" <?php echo $type === 'tshirt' ? 'selected' : ''; ?>>T-Shirts</option>
                    <option value="pants" <?php echo $type === 'pants' ? 'selected' : ''; ?>>Pants</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="sort-filter">Sort by:</label>
                <select id="sort-filter" onchange="updateFilters()">
                    <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest</option>
                    <option value="price-low" <?php echo $sort === 'price-low' ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="price-high" <?php echo $sort === 'price-high' ? 'selected' : ''; ?>>Price: High to Low</option>
                </select>
            </div>
        </div>

        <div class="products-grid">
            <?php foreach ($products as $product): ?>
            <div class="product-card animate-on-scroll">
                <div class="product-image">
                    <img src="<?php echo $product['images'][0]; ?>" alt="<?php echo $product['productName']; ?>">
                    <div class="product-overlay">
                        <a href="product.php?id=<?php echo $product['productID']; ?>" class="btn btn-outline">View Details</a>
                        <button class="btn btn-primary add-to-cart" data-product-id="<?php echo $product['productID']; ?>">
                            Add to Cart
                        </button>
                    </div>
                </div>
                <div class="product-info">
                    <h3><?php echo $product['productName']; ?></h3>
                    <p class="product-price"><?php echo formatPrice($product['unitCost']); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="pagination-container">
            <?php echo generatePagination($page, $totalPages, 'shop.php'); ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Update filters
function updateFilters() {
    const type = document.getElementById('type-filter').value;
    const sort = document.getElementById('sort-filter').value;
    const url = new URL(window.location.href);
    
    url.searchParams.set('type', type);
    url.searchParams.set('sort', sort);
    url.searchParams.set('page', '1');
    
    window.location.href = url.toString();
}

// Add to cart functionality
document.querySelectorAll('.add-to-cart').forEach(button => {
    button.addEventListener('click', function() {
        const productId = this.dataset.productId;
        
        fetch('process/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'add',
                productId: productId,
                quantity: 1
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Product added to cart!', 'success');
                // Update cart count
                const cartCount = document.querySelector('.cart-count');
                if (cartCount) {
                    cartCount.textContent = data.cartCount;
                }
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Something went wrong. Please try again.', 'error');
        });
    });
});

// Animate on scroll
const animateOnScroll = () => {
    const elements = document.querySelectorAll('.animate-on-scroll');
    
    elements.forEach(element => {
        const elementTop = element.getBoundingClientRect().top;
        const elementBottom = element.getBoundingClientRect().bottom;
        
        if (elementTop < window.innerHeight && elementBottom > 0) {
            element.classList.add('animated');
        }
    });
};

window.addEventListener('scroll', animateOnScroll);
window.addEventListener('load', animateOnScroll);
</script>

<?php include 'includes/footer.php'; ?>
