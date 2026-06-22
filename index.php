<?php
/**
 * GreenLink Market - Homepage
 */
$pageTitle = 'Home';
require_once __DIR__ . '/config/init.php';

$productModel = new Product();
$categoryModel = new Category();

$featuredProducts = $productModel->getFeatured(8);
$categories = $categoryModel->getAll();
$totalProducts = $productModel->getCount();
$heroImageFile = __DIR__ . '/assets/images/greenlink-campus-hero.webp';
$hasHeroImage = is_file($heroImageFile);

include __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="hero-content hero-content-inner animate-fadeInUp">
                    <span class="badge-glass mb-3">
                        <i class="bi bi-stars me-1"></i> Built for NSBM campus life
                    </span>
                    <h1 class="hero-title" id="heroTitle">
                        Campus essentials, <span class="gradient-text">connected intelligently.</span>
                    </h1>
                    <p class="hero-subtitle" id="heroSubtitle">
                        GreenLink Market brings trusted products, student-focused services, and AI-powered discovery into one seamless NSBM shopping experience.
                    </p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="pages/products.php" class="btn btn-primary-custom btn-lg">
                            <i class="bi bi-bag me-2"></i><span id="heroCTA">Shop Now</span>
                        </a>
                        <button class="btn btn-outline-custom btn-lg" onclick="toggleAIChat()">
                            <i class="bi bi-robot me-2"></i>Ask AI
                        </button>
                    </div>
                    <div class="hero-stats">
                        <div class="hero-stat">
                            <div class="hero-stat-number" data-count="<?= $totalProducts ?>">0</div>
                            <div class="hero-stat-label">Curated listings</div>
                        </div>
                        <div class="hero-stat">
                            <div class="hero-stat-number" data-count="<?= count($categories) ?>">0</div>
                            <div class="hero-stat-label">Categories</div>
                        </div>
                        <div class="hero-stat">
                            <div class="hero-stat-number">24/7</div>
                            <div class="hero-stat-label">AI assistance</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-visual">
                    <div class="hero-glow"></div>
                    <div class="hero-floating-proof">
                        <i class="bi bi-shield-check"></i>
                        <div><strong>Campus focused</strong><small>Curated for NSBM students</small></div>
                    </div>
                    <div class="hero-media-frame">
                        <?php if ($hasHeroImage): ?>
                            <img src="assets/images/greenlink-campus-hero.webp" class="hero-campus-image" alt="NSBM Green University campus and student marketplace experience">
                        <?php else: ?>
                            <div class="hero-image-placeholder">
                                <div class="hero-placeholder-mark">
                                    <i class="bi bi-buildings"></i>
                                    <strong>University hero image ready</strong>
                                    <span class="d-block small mt-1">1600 × 1200 WebP recommended</span>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="hero-image-shade"></div>
                        <div class="hero-media-caption">
                            <div>
                                <h4>Smart shopping for campus life</h4>
                                <p>Discover what you need, when you need it.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="section">
    <div class="container">
        <div class="text-center mb-5" data-animate>
            <h2 class="section-title">Browse <span class="text-gradient">Categories</span></h2>
            <p class="section-subtitle">Find exactly what you need for campus life</p>
        </div>
        <div class="row g-4">
            <?php 
            $icons = ['bi-laptop', 'bi-journal-bookmark', 'bi-bag', 'bi-cup-hot', 'bi-trophy', 'bi-palette', 'bi-briefcase', 'bi-house-door'];
            foreach ($categories as $i => $category): 
            ?>
            <div class="col-lg-3 col-md-4 col-6" data-animate>
                <a href="pages/products.php?category=<?= $category['id'] ?>" class="text-decoration-none">
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="bi <?= $icons[$i % count($icons)] ?>"></i>
                        </div>
                        <h5 class="category-name"><?= sanitize($category['name']) ?></h5>
                        <span class="category-count"><?= $category['product_count'] ?? 0 ?> Products</span>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="section" style="background: var(--bg-darker);">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-5" data-animate>
            <div>
                <h2 class="section-title">Featured <span class="text-gradient">Products</span></h2>
                <p class="section-subtitle mb-0">Handpicked items loved by NSBM students</p>
            </div>
            <a href="pages/products.php" class="btn btn-outline-custom">
                View All <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="row g-4" id="featuredProducts">
            <?php foreach ($featuredProducts as $product): 
                $price = $product['sale_price'] ?: $product['price'];
                $hasDiscount = $product['sale_price'] && $product['sale_price'] < $product['price'];
                $discount = $hasDiscount ? round((1 - $product['sale_price'] / $product['price']) * 100) : 0;
                $productImage = productImageUrl($product['image'] ?? null);
            ?>
            <div class="col-lg-3 col-md-4 col-sm-6" data-animate>
                <div class="product-card">
                    <div class="product-card-image">
                        <?php if ($productImage): ?>
                            <img src="<?= sanitize($productImage) ?>" alt="<?= sanitize($product['name']) ?>" loading="lazy">
                        <?php else: ?>
                            <div class="product-image-placeholder"><i class="bi bi-box-seam"></i></div>
                        <?php endif; ?>
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
                        <a href="pages/product.php?id=<?= $product['id'] ?>" class="btn btn-outline-custom btn-sm">View</a>
                        <button class="btn btn-primary-custom btn-sm" onclick='Cart.add(<?= json_encode(["id" => $product["id"], "name" => $product["name"], "price" => (float)$price, "sale_price" => $product["sale_price"], "image" => $productImage, "stock_quantity" => $product["stock_quantity"]]) ?>)'>
                            <i class="bi bi-cart-plus"></i> Add
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- AI Features Section -->
<section class="section">
    <div class="container">
        <div class="text-center mb-5" data-animate>
            <h2 class="section-title">Powered by <span class="text-gradient">AI</span></h2>
            <p class="section-subtitle">Experience intelligent shopping with Gemini AI integration</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-4" data-animate>
                <div class="glass-card text-center h-100">
                    <div class="category-icon mx-auto mb-3" style="background:linear-gradient(135deg, #087a4b, #16a66a);">
                        <i class="bi bi-chat-dots"></i>
                    </div>
                    <h4 class="mb-2">Shopping Assistant</h4>
                    <p class="text-muted-custom">Chat with our AI to find the perfect products. Get personalized recommendations based on your needs.</p>
                    <button class="btn btn-glass btn-sm" onclick="toggleAIChat()">Try Now <i class="bi bi-arrow-right"></i></button>
                </div>
            </div>
            <div class="col-lg-4" data-animate>
                <div class="glass-card text-center h-100">
                    <div class="category-icon mx-auto mb-3" style="background:linear-gradient(135deg, #b6e34a, #d0f27c);">
                        <i class="bi bi-gift"></i>
                    </div>
                    <h4 class="mb-2">Gift Recommender</h4>
                    <p class="text-muted-custom">Looking for the perfect gift? Our AI analyzes preferences and suggests matching products from our catalog.</p>
                    <button class="btn btn-glass btn-sm" onclick="toggleAIChat()">Get Suggestions <i class="bi bi-arrow-right"></i></button>
                </div>
            </div>
            <div class="col-lg-4" data-animate>
                <div class="glass-card text-center h-100">
                    <div class="category-icon mx-auto mb-3" style="background:linear-gradient(135deg, #f2b84b, #ffd477);">
                        <i class="bi bi-magic"></i>
                    </div>
                    <h4 class="mb-2">Dynamic Content</h4>
                    <p class="text-muted-custom">AI-generated marketing content that keeps our homepage fresh and engaging with dynamic headlines.</p>
                    <button class="btn btn-glass btn-sm" id="refreshHeroBtn" onclick="refreshHeroContent()">Refresh <i class="bi bi-arrow-clockwise"></i></button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="section" style="background: var(--bg-darker);">
    <div class="container">
        <div class="glass-card text-center p-5" data-animate>
            <h3 class="mb-2">Stay Updated</h3>
            <p class="text-muted-custom mb-4">Get notified about new products, deals, and campus events</p>
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="email" class="form-control form-control-custom" placeholder="your@students.nsbm.ac.lk">
                        <button class="btn btn-primary-custom">Subscribe</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$extraScripts = '
<script>
async function refreshHeroContent() {
    const btn = document.getElementById("refreshHeroBtn");
    btn.innerHTML = \'<span class="loading-spinner" style="width:16px;height:16px;border-width:2px;display:inline-block;"></span> Generating...\';
    btn.disabled = true;
    
    try {
        const response = await API.post("ai.php?action=hero", {});
        if (response.success && response.data) {
            const data = response.data;
            if (data.headline) document.getElementById("heroTitle").innerHTML = data.headline;
            if (data.subtitle) document.getElementById("heroSubtitle").textContent = data.subtitle;
            if (data.cta) document.getElementById("heroCTA").textContent = data.cta;
            Toast.show("Hero content refreshed by AI!", "success");
        }
    } catch (error) {
        Toast.show("Could not refresh content", "error");
    }
    
    btn.innerHTML = \'Refresh <i class="bi bi-arrow-clockwise"></i>\';
    btn.disabled = false;
}
</script>';
include __DIR__ . '/includes/footer.php';
?>
