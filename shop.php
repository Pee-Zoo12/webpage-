<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'classes/JsonDatabase.php';
require_once 'classes/Website.php';
require_once 'classes/Product.php';

// Initialize site
$site = new Website();
$db = new JsonDatabase();

// Get filter parameters
$category = isset($_GET['category']) ? $_GET['category'] : null;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';

// Get categories for filter
$categories = $db->getCategories();

// Get products
$productsPerPage = 12;
$products = $db->getFilteredProducts($category, $sort, $searchQuery, $page, $productsPerPage);
$totalProducts = $db->getFilteredProductsCount($category, $searchQuery);
$totalPages = ceil($totalProducts / $productsPerPage);

// Page title
$pageTitle = "Shop | Nox Apparel";
include 'includes/header.php';
?>

<main>
    <!-- Shop Header -->
    <section class="shop-header">
        <div class="container">
            <h1 class="animate-text">Shop Nox Apparel</h1>
            <p class="animate-text-delay">Discover our latest collections and signature pieces</p>
        </div>
    </section>

    <!-- Shop Content -->
    <section class="shop-content">
        <div class="container">
            <div class="shop-grid">
                <!-- Filters Sidebar -->
                <aside class="filters-sidebar animate-slide-right">
                    <div class="filter-section">
                        <h3>Categories</h3>
                        <ul class="category-filter">
                            <li>
                                <a href="shop.php" class="<?php echo !$category ? 'active' : ''; ?>">All Products</a>
                            </li>
                            <?php foreach ($categories as $cat): ?>
                            <li>
                                <a href="shop.php?category=<?php echo urlencode($cat); ?>" 
                                   class="<?php echo $category === $cat ? 'active' : ''; ?>">
                                    <?php echo $cat; ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <div class="filter-section">
                        <h3>Sort By</h3>
                        <ul class="sort-filter">
                            <li>
                                <a href="<?php echo buildUrl(['sort' => 'newest']); ?>" 
                                   class="<?php echo $sort === 'newest' ? 'active' : ''; ?>">
                                    Newest
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo buildUrl(['sort' => 'price-low']); ?>" 
                                   class="<?php echo $sort === 'price-low' ? 'active' : ''; ?>">
                                    Price: Low to High
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo buildUrl(['sort' => 'price-high']); ?>" 
                                   class="<?php echo $sort === 'price-high' ? 'active' : ''; ?>">
                                    Price: High to Low
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo buildUrl(['sort' => 'popular']); ?>" 
                                   class="<?php echo $sort === 'popular' ? 'active' : ''; ?>">
                                    Most Popular
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="filter-section">
                        <h3>Search</h3>
                        <form class="search-form" action="shop.php" method="get">
                            <?php if ($category): ?>
                            <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
                            <?php endif; ?>
                            <?php if ($sort): ?>
                            <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
                            <?php endif; ?>
                            <div class="form-group">
                                <input type="text" name="search" placeholder="Search products..." 
                                       value="<?php echo htmlspecialchars($searchQuery); ?>">
                                <button type="submit" class="search-btn">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </aside>

                <!-- Products Grid -->
                <div class="products-container">
                    <?php if (empty($products)): ?>
                    <div class="no-products">
                        <p>No products found. Try adjusting your filters.</p>
                    </div>
                    <?php else: ?>
                    <div class="products-grid">
                        <?php foreach ($products as $product): ?>
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

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                        <a href="<?php echo buildUrl(['page' => $page - 1]); ?>" class="page-nav">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                        <?php endif; ?>

                        <div class="page-numbers">
                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <a href="<?php echo buildUrl(['page' => $i]); ?>" 
                               class="<?php echo $i === $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                            <?php endfor; ?>
                        </div>

                        <?php if ($page < $totalPages): ?>
                        <a href="<?php echo buildUrl(['page' => $page + 1]); ?>" class="page-nav">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
