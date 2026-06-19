<?php
/**
 * Products Page - NSBM Marketplace AI
 */
$isSubPage = true;
$pageTitle = 'Products';
require_once __DIR__ . '/../config/init.php';

$productModel = new Product();
$categoryModel = new Category();

// Get filters
$filters = [
    'category_id' => $_GET['category'] ?? null,
    'search' => $_GET['search'] ?? null,
    'min_price' => $_GET['min_price'] ?? null,
    'max_price' => $_GET['max_price'] ?? null,
    'sort' => $_GET['sort'] ?? null
];

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = ITEMS_PER_PAGE;
$offset = ($page - 1) * $limit;

$products = $productModel->getAll($limit, $offset, $filters);
$totalProducts = $productModel->getCount($filters);
$totalPages = ceil($totalProducts / $limit);
$categories = $categoryModel->getAll();

// Get active category name
$activeCategoryName = 'All Products';
if (!empty($filters['category_id'])) {
    $activeCategory = $categoryModel->getById((int)$filters['category_id']);
    if ($activeCategory) $activeCategoryName = $activeCategory['name'];
}

include __DIR__ . '/../includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1><?= sanitize($activeCategoryName) ?></h1>
        <nav class="breadcrumb-custom">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item active"><?= sanitize($activeCategoryName) ?></li>
            </ol>
        </nav>
        <?php if (!empty($filters['search'])): ?>
            <p class="text-muted-custom mt-2">Search results for: "<strong><?= sanitize($filters['search']) ?></strong>" (<?= $totalProducts ?> found)</p>
        <?php endif; ?>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="row">
            <!-- Sidebar Filters -->
            <div class="col-lg-3 mb-4">
                <div class="glass-card">
                    <h5 class="mb-3"><i class="bi bi-funnel me-2"></i>Filters</h5>
                    
                    <form method="GET" action="products.php">
                        <!-- Search -->
                        <div class="mb-3">
                            <label class="form-label-custom">Search</label>
                            <input type="text" name="search" class="form-control form-control-custom form-control-sm" 
                                   value="<?= sanitize($filters['search'] ?? '') ?>" placeholder="Search products...">
                        </div>

                        <!-- Categories -->
                        <div class="mb-3">
                            <label class="form-label-custom">Category</label>
                            <select name="category" class="form-control form-control-custom form-control-sm">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= ($filters['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                        <?= sanitize($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Price Range -->
                        <div class="mb-3">
                            <label class="form-label-custom">Price Range</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" name="min_price" class="form-control form-control-custom form-control-sm" 
                                           placeholder="Min" value="<?= sanitize($filters['min_price'] ?? '') ?>">
                                </div>
                                <div class="col-6">
                                    <input type="number" name="max_price" class="form-control form-control-custom form-control-sm" 
                                           placeholder="Max" value="<?= sanitize($filters['max_price'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Sort -->
                        <div class="mb-3">
                            <label class="form-label-custom">Sort By</label>
                            <select name="sort" class="form-control form-control-custom form-control-sm">
                                <option value="">Newest First</option>
                                <option value="price_asc" <?= ($filters['sort'] === 'price_asc') ? 'selected' : '' ?>>Price: Low to High</option>
                                <option value="price_desc" <?= ($filters['sort'] === 'price_desc') ? 'selected' : '' ?>>Price: High to Low</option>
                                <option value="rating" <?= ($filters['sort'] === 'rating') ? 'selected' : '' ?>>Top Rated</option>
                                <option value="popular" <?= ($filters['sort'] === 'popular') ? 'selected' : '' ?>>Most Popular</option>
                                <option value="name_asc" <?= ($filters['sort'] === 'name_asc') ? 'selected' : '' ?>>Name: A-Z</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary-custom btn-sm w-100 mb-2">
                            <i class="bi bi-search me-1"></i> Apply Filters
                        </button>
                        <a href="products.php" class="btn btn-outline-custom btn-sm w-100">
                            <i class="bi bi-x-circle me-1"></i> Clear All
                        </a>
                    </form>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <p class="text-muted-custom mb-0">Showing <?= count($products) ?> of <?= $totalProducts ?> products</p>
                </div>

                <?php if (empty($products)): ?>
                    <div class="glass-card text-center py-5">
                        <i class="bi bi-search" style="font-size:3rem;color:var(--text-muted);"></i>
                        <h4 class="mt-3">No Products Found</h4>
                        <p class="text-muted-custom">Try adjusting your filters or search terms</p>
                        <a href="products.php" class="btn btn-primary-custom">View All Products</a>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($products as $product): 
                            $price = $product['sale_price'] ?: $product['price'];
                            $hasDiscount = $product['sale_price'] && $product['sale_price'] < $product['price'];
                            $discount = $hasDiscount ? round((1 - $product['sale_price'] / $product['price']) * 100) : 0;
                        ?>
                        <div class="col-md-4 col-sm-6" data-animate>
                            <div class="product-card">
                                <div class="product-card-image">
                                    <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg, rgba(108,99,255,0.1), rgba(0,212,170,0.05));">
                                        <i class="bi bi-box-seam" style="font-size:3rem;color:var(--primary-light);opacity:0.5;"></i>
                                    </div>
                                    <?php if ($hasDiscount): ?>
                                        <span class="product-badge product-badge-sale">-<?= $discount ?>%</span>
                                    <?php endif; ?>
                                    <?php if ($product['is_featured']): ?>
                                        <span class="product-badge product-badge-featured" style="<?= $hasDiscount ? 'top:45px;' : '' ?>">Featured</span>
                                    <?php endif; ?>
                                </div>
                                <div class="product-card-body">
                                    <span class="product-category"><?= sanitize($product['category_name']) ?></span>
                                    <h5 class="product-title"><?= sanitize($product['name']) ?></h5>
                                    <div class="product-rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi bi-star<?= $i <= round($product['rating']) ? '-fill' : '' ?> star"></i>
                                        <?php endfor; ?>
                                        <span class="count">(<?= $product['total_reviews'] ?>)</span>
                                    </div>
                                    <div class="product-price">
                                        <span class="current"><?= formatPrice($price) ?></span>
                                        <?php if ($hasDiscount): ?>
                                            <span class="original"><?= formatPrice($product['price']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="product-card-actions">
                                    <a href="product.php?id=<?= $product['id'] ?>" class="btn btn-outline-custom btn-sm">View</a>
                                    <button class="btn btn-primary-custom btn-sm" onclick='Cart.add(<?= json_encode(["id" => $product["id"], "name" => $product["name"], "price" => (float)$price, "sale_price" => $product["sale_price"], "image" => $product["image"], "stock_quantity" => $product["stock_quantity"]]) ?>)'>
                                        <i class="bi bi-cart-plus"></i> Add
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <nav class="mt-5">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" style="background:var(--bg-glass);border-color:var(--border-glass);color:var(--text-primary);">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                                   style="<?= $i === $page ? 'background:var(--primary);border-color:var(--primary);' : 'background:var(--bg-glass);border-color:var(--border-glass);color:var(--text-primary);' ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" style="background:var(--bg-glass);border-color:var(--border-glass);color:var(--text-primary);">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
