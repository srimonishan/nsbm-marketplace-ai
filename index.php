<?php
/**
 * NSBM Marketplace AI - Homepage
 */
$pageTitle = 'Home';
require_once __DIR__ . '/config/init.php';

$productModel = new Product();
$categoryModel = new Category();

$featuredProducts = $productModel->getFeatured(8);
$categories = $categoryModel->getAll();

include __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content animate-fadeInUp">
                    <span class="badge-glass mb-3">
                        <i class="bi bi-stars me-1"></i> AI-Powered Shopping Experience
                    </span>
                    <h1 class="hero-title" id="heroTitle">
                        Discover <span class="gradient-text">Premium Products</span> for Campus Life
                    </h1>
                    <p class="hero-subtitle" id="heroSubtitle">
                        The exclusive AI-powered marketplace for NSBM Green University. Smart recommendations, seamless shopping, and premium quality.
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
                            <div class="hero-stat-number" data-count="500">0</div>
                            <div class="hero-stat-label">Products</div>
                        </div>
                        <div class="hero-stat">
                            <div class="hero-stat-number" data-count="2000">0</div>
                            <div class="hero-stat-label">Students</div>
                        </div>
                        <div class="hero-stat">
                            <div class="hero-stat-number" data-count="98">0</div>
                            <div class="hero-stat-label">% Satisfaction</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block">
                <div class="hero-visual text-center">
                    <div class="hero-glow"></div>
                    <div class="glass-card p-4" style="max-width:400px;margin:0 auto;position:relative;z-index:2;">
                        <div style="font-size:4rem;margin-bottom:1rem;">🛍️</div>
                        <h4 class="text-gradient mb-2">Smart Shopping</h4>
                        <p class="text-muted-custom mb-0">AI-powered recommendations tailored for NSBM students</p>
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
            $icons = ['bi-laptop', 'bi-book', 'bi-bag-heart', 'bi-cup-hot', 'bi-bicycle', 'bi-palette', 'bi-tools', 'bi-house-heart'];
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
            ?>
            <div class="col-lg-3 col-md-4 col-sm-6" data-animate>
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
                        <a href="pages/product.php?id=<?= $product['id'] ?>" class="btn btn-outline-custom btn-sm">View</a>
                        <button class="btn btn-primary-custom btn-sm" onclick='Cart.add(<?= json_encode(["id" => $product["id"], "name" => $product["name"], "price" => (float)$price, "sale_price" => $product["sale_price"], "image" => $product["image"], "stock_quantity" => $product["stock_quantity"]]) ?>)'>
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
                    <div class="category-icon mx-auto mb-3" style="background:linear-gradient(135deg, #6c63ff, #8b83ff);">
                        <i class="bi bi-chat-dots"></i>
                    </div>
                    <h4 class="mb-2">Shopping Assistant</h4>
                    <p class="text-muted-custom">Chat with our AI to find the perfect products. Get personalized recommendations based on your needs.</p>
                    <button class="btn btn-glass btn-sm" onclick="toggleAIChat()">Try Now <i class="bi bi-arrow-right"></i></button>
                </div>
            </div>
            <div class="col-lg-4" data-animate>
                <div class="glass-card text-center h-100">
                    <div class="category-icon mx-auto mb-3" style="background:linear-gradient(135deg, #00d4aa, #33e0be);">
                        <i class="bi bi-gift"></i>
                    </div>
                    <h4 class="mb-2">Gift Recommender</h4>
                    <p class="text-muted-custom">Looking for the perfect gift? Our AI analyzes preferences and suggests matching products from our catalog.</p>
                    <button class="btn btn-glass btn-sm" onclick="toggleAIChat()">Get Suggestions <i class="bi bi-arrow-right"></i></button>
                </div>
            </div>
            <div class="col-lg-4" data-animate>
                <div class="glass-card text-center h-100">
                    <div class="category-icon mx-auto mb-3" style="background:linear-gradient(135deg, #ff6b9d, #ff8fb5);">
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
