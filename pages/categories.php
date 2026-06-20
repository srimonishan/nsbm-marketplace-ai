<?php
/**
 * Categories Page - GreenLink Market
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
            $icons = ['bi-laptop', 'bi-journal-bookmark', 'bi-bag', 'bi-cup-hot', 'bi-trophy', 'bi-palette', 'bi-briefcase', 'bi-house-door'];
            foreach ($categories as $i => $category): 
            ?>
            <div class="col-lg-3 col-md-4 col-sm-6" data-animate>
                <a href="products.php?category=<?= $category['id'] ?>" class="text-decoration-none">
                    <div class="category-card category-card-detail h-100">
                        <div class="category-icon">
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
