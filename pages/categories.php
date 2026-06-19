<?php
/**
 * Categories Page - NSBM Marketplace AI
 */
$isSubPage = true;
$pageTitle = 'Categories';
require_once __DIR__ . '/../config/init.php';

$categoryModel = new Category();
$categories = $categoryModel->getAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="bi bi-collection me-2"></i>Shop by Category</h1>
        <nav class="breadcrumb-custom">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item active">Categories</li>
            </ol>
        </nav>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="row g-4">
            <?php 
            $icons = ['bi-laptop', 'bi-book', 'bi-bag-heart', 'bi-cup-hot', 'bi-bicycle', 'bi-palette', 'bi-tools', 'bi-house-heart'];
            $colors = [
                'linear-gradient(135deg, #6c63ff, #8b83ff)',
                'linear-gradient(135deg, #00d4aa, #33e0be)',
                'linear-gradient(135deg, #ff6b9d, #ff8fb5)',
                'linear-gradient(135deg, #ffc107, #ffcd38)',
                'linear-gradient(135deg, #17a2b8, #3dc5d8)',
                'linear-gradient(135deg, #e83e8c, #f06eaa)',
                'linear-gradient(135deg, #6610f2, #8540f5)',
                'linear-gradient(135deg, #20c997, #4dd4ac)'
            ];
            foreach ($categories as $i => $category): 
            ?>
            <div class="col-lg-3 col-md-4 col-sm-6" data-animate>
                <a href="products.php?category=<?= $category['id'] ?>" class="text-decoration-none">
                    <div class="glass-card text-center h-100" style="padding:2.5rem 1.5rem;">
                        <div class="category-icon mx-auto mb-3" style="background:<?= $colors[$i % count($colors)] ?>;width:70px;height:70px;font-size:1.8rem;">
                            <i class="bi <?= $icons[$i % count($icons)] ?>"></i>
                        </div>
                        <h4 class="mb-2"><?= sanitize($category['name']) ?></h4>
                        <p class="text-muted-custom small mb-2"><?= sanitize($category['description'] ?? '') ?></p>
                        <span class="badge-glass"><?= $category['product_count'] ?? 0 ?> Products</span>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
