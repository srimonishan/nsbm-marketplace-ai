<?php
/**
 * Product Detail Page - GreenLink Market
 */
$isSubPage = true;
require_once __DIR__ . '/../config/init.php';

$productModel = new Product();
$reviewModel = new Review();

$productId = (int)($_GET['id'] ?? 0);
$product = $productModel->getById($productId);

if (!$product) {
    header('Location: products.php');
    exit;
}

$pageTitle = $product['name'];
$reviews = $reviewModel->getByProduct($productId);
$relatedProducts = $productModel->getRelated($product['category_id'], $product['id']);
$price = $product['sale_price'] ?: $product['price'];
$hasDiscount = $product['sale_price'] && $product['sale_price'] < $product['price'];
$discount = $hasDiscount ? round((1 - $product['sale_price'] / $product['price']) * 100) : 0;

include __DIR__ . '/../includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <nav class="breadcrumb-custom">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="products.php">Products</a></li>
                <li class="breadcrumb-item"><a href="products.php?category=<?= $product['category_id'] ?>"><?= sanitize($product['category_name']) ?></a></li>
                <li class="breadcrumb-item active"><?= sanitize($product['name']) ?></li>
            </ol>
        </nav>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="row g-5">
            <!-- Product Image -->
            <div class="col-lg-6" data-animate>
                <div class="product-detail-image">
                    <?php $productImage = productImageUrl($product['image'] ?? null); ?>
                    <?php if ($productImage): ?>
                        <img src="<?= sanitize($productImage) ?>" alt="<?= sanitize($product['name']) ?>">
                    <?php else: ?>
                        <div class="product-image-placeholder product-image-placeholder-large"><i class="bi bi-box-seam"></i></div>
                    <?php endif; ?>
                </div>
                <?php if ($hasDiscount): ?>
                    <div class="mt-3 text-center">
                        <span class="badge bg-danger fs-6 px-3 py-2">🔥 Save <?= $discount ?>% - Limited Time Offer!</span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Product Info -->
            <div class="col-lg-6" data-animate>
                <div class="product-detail-info">
                    <span class="badge-glass mb-3"><?= sanitize($product['category_name']) ?></span>
                    
                    <h1 class="mb-3" style="font-size:2rem;font-weight:800;"><?= sanitize($product['name']) ?></h1>
                    
                    <!-- Rating -->
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="product-rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="bi bi-star<?= $i <= round($product['rating']) ? '-fill' : '' ?> star"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="text-muted-custom"><?= $product['total_reviews'] ?> reviews</span>
                        <span class="text-muted-custom">|</span>
                        <span class="text-muted-custom"><i class="bi bi-eye me-1"></i><?= number_format($product['views']) ?> views</span>
                    </div>

                    <!-- Price -->
                    <div class="mb-4">
                        <span class="product-detail-price"><?= formatPrice($price) ?></span>
                        <?php if ($hasDiscount): ?>
                            <span class="text-muted-custom text-decoration-line-through ms-2 fs-5"><?= formatPrice($product['price']) ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Description -->
                    <p class="text-muted-custom mb-4" style="line-height:1.8;"><?= sanitize($product['description']) ?></p>

                    <!-- Stock Status -->
                    <div class="mb-4">
                        <?php if ($product['stock_quantity'] > 0): ?>
                            <span class="badge" style="background:rgba(182, 227, 74,0.2);color:var(--secondary);padding:0.5rem 1rem;">
                                <i class="bi bi-check-circle me-1"></i> In Stock (<?= $product['stock_quantity'] ?> available)
                            </span>
                        <?php else: ?>
                            <span class="badge" style="background:rgba(255,107,157,0.2);color:var(--accent);padding:0.5rem 1rem;">
                                <i class="bi bi-x-circle me-1"></i> Out of Stock
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- SKU -->
                    <?php if ($product['sku']): ?>
                        <p class="text-muted-custom small mb-4">SKU: <?= sanitize($product['sku']) ?></p>
                    <?php endif; ?>

                    <!-- Quantity & Add to Cart -->
                    <?php if ($product['stock_quantity'] > 0): ?>
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="quantity-control">
                            <button onclick="changeQty(-1)">-</button>
                            <input type="number" id="productQty" value="1" min="1" max="<?= $product['stock_quantity'] ?>" readonly>
                            <button onclick="changeQty(1)">+</button>
                        </div>
                        <button class="btn btn-primary-custom btn-lg flex-grow-1" onclick="addToCartDetail()">
                            <i class="bi bi-cart-plus me-2"></i> Add to Cart
                        </button>
                    </div>
                    <?php endif; ?>

                    <!-- Extra Info -->
                    <div class="row g-3 mt-3">
                        <div class="col-6">
                            <div class="glass-card p-3 text-center">
                                <i class="bi bi-truck text-primary mb-1 d-block"></i>
                                <small class="text-muted-custom">Free shipping over Rs. 10,000</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="glass-card p-3 text-center">
                                <i class="bi bi-shield-check text-primary mb-1 d-block"></i>
                                <small class="text-muted-custom">Secure checkout</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews Section -->
        <div class="mt-5 pt-5" style="border-top:1px solid var(--border-glass);">
            <h3 class="mb-4"><i class="bi bi-chat-quote me-2"></i>Customer Reviews (<?= count($reviews) ?>)</h3>
            
            <?php if (empty($reviews)): ?>
                <div class="glass-card text-center py-4">
                    <p class="text-muted-custom mb-0">No reviews yet. Be the first to review this product!</p>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($reviews as $review): ?>
                    <div class="col-md-6">
                        <div class="glass-card">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong><?= sanitize($review['first_name'] . ' ' . $review['last_name']) ?></strong>
                                <small class="text-muted-custom"><?= getTimeAgo($review['created_at']) ?></small>
                            </div>
                            <div class="product-rating mb-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi bi-star<?= $i <= $review['rating'] ? '-fill' : '' ?> star"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="text-muted-custom mb-0"><?= sanitize($review['comment']) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Related Products -->
        <?php if (!empty($relatedProducts)): ?>
        <div class="mt-5 pt-5" style="border-top:1px solid var(--border-glass);">
            <h3 class="mb-4">Related Products</h3>
            <div class="row g-4">
                <?php foreach ($relatedProducts as $related): 
                    $rPrice = $related['sale_price'] ?: $related['price'];
                    $relatedImage = productImageUrl($related['image'] ?? null);
                ?>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="product-card">
                        <div class="product-card-image">
                            <?php if ($relatedImage): ?>
                                <img src="<?= sanitize($relatedImage) ?>" alt="<?= sanitize($related['name']) ?>" loading="lazy">
                            <?php else: ?>
                                <div class="product-image-placeholder"><i class="bi bi-box-seam"></i></div>
                            <?php endif; ?>
                        </div>
                        <div class="product-card-body">
                            <h5 class="product-title"><?= sanitize($related['name']) ?></h5>
                            <div class="product-price">
                                <span class="current"><?= formatPrice($rPrice) ?></span>
                            </div>
                        </div>
                        <div class="product-card-actions">
                            <a href="product.php?id=<?= $related['id'] ?>" class="btn btn-outline-custom btn-sm w-100">View Details</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php
$extraScripts = '
<script>
const productData = ' . json_encode(["id" => $product["id"], "name" => $product["name"], "price" => (float)$price, "sale_price" => $product["sale_price"], "image" => productImageUrl($product["image"] ?? null), "stock_quantity" => $product["stock_quantity"]]) . ';

function changeQty(delta) {
    const input = document.getElementById("productQty");
    let val = parseInt(input.value) + delta;
    val = Math.max(1, Math.min(val, parseInt(input.max)));
    input.value = val;
}

function addToCartDetail() {
    const qty = parseInt(document.getElementById("productQty").value);
    for (let i = 0; i < qty; i++) {
        Cart.add(productData);
    }
}
</script>';
include __DIR__ . '/../includes/footer.php';
?>
