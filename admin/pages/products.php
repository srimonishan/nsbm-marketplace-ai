<?php
/**
 * Admin Products Management - NSBM Marketplace AI
 */
session_start();
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Product.php';
require_once __DIR__ . '/../../models/Category.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Order.php';
require_once __DIR__ . '/../../models/Review.php';

$currentPage = 'products';
$pageTitle = 'Products Management';

$productModel = new Product();
$categoryModel = new Category();

$page = max(1, (int)($_GET['page'] ?? 1));
$products = $productModel->getAll(ADMIN_ITEMS_PER_PAGE, ($page - 1) * ADMIN_ITEMS_PER_PAGE);
$totalProducts = $productModel->getCount();
$categories = $categoryModel->getAll(false);

include __DIR__ . '/../includes/header.php';
?>

<!-- Action Bar -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="text-muted-custom mb-0"><?= $totalProducts ?> total products</p>
    <button class="btn btn-primary-custom btn-sm" data-bs-toggle="modal" data-bs-target="#productModal" onclick="resetForm()">
        <i class="bi bi-plus-lg me-1"></i> Add Product
    </button>
</div>

<!-- Products Table -->
<div class="admin-card">
    <div class="admin-card-body p-0">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:40px;height:40px;background:var(--gradient-card);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;">
                                    <i class="bi bi-box-seam" style="color:var(--primary-light);"></i>
                                </div>
                                <div>
                                    <strong style="font-size:0.85rem;"><?= sanitize(substr($product['name'], 0, 40)) ?></strong>
                                    <?php if ($product['is_featured']): ?>
                                        <span class="badge" style="background:var(--primary);font-size:0.6rem;margin-left:5px;">Featured</span>
                                    <?php endif; ?>
                                    <div class="text-muted-custom" style="font-size:0.75rem;">SKU: <?= $product['sku'] ?? 'N/A' ?></div>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge-glass"><?= sanitize($product['category_name'] ?? 'N/A') ?></span></td>
                        <td>
                            <strong><?= formatPrice($product['sale_price'] ?: $product['price']) ?></strong>
                            <?php if ($product['sale_price']): ?>
                                <br><small class="text-muted-custom text-decoration-line-through"><?= formatPrice($product['price']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="<?= $product['stock_quantity'] > 0 ? 'text-success' : 'text-danger' ?>">
                                <?= $product['stock_quantity'] ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge status-<?= $product['is_active'] ? 'active' : 'inactive' ?>">
                                <?= $product['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn-action" onclick='editProduct(<?= json_encode($product) ?>)' title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn-action danger" onclick="deleteProduct(<?= $product['id'] ?>)" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background:var(--bg-card);border:1px solid var(--border-glass);">
            <div class="modal-header" style="border-bottom:1px solid var(--border-glass);">
                <h5 class="modal-title" id="productModalTitle">Add Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1);"></button>
            </div>
            <div class="modal-body">
                <form id="productForm">
                    <input type="hidden" id="productId" value="">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label-custom">Product Name *</label>
                            <input type="text" id="productName" class="form-control form-control-custom" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-custom">Category *</label>
                            <select id="productCategory" class="form-control form-control-custom" required>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= sanitize($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label-custom">Short Description</label>
                            <input type="text" id="productShortDesc" class="form-control form-control-custom" maxlength="500">
                        </div>
                        <div class="col-12">
                            <label class="form-label-custom">Full Description *</label>
                            <textarea id="productDesc" class="form-control form-control-custom" rows="4" required></textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Price (Rs.) *</label>
                            <input type="number" id="productPrice" class="form-control form-control-custom" step="0.01" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Sale Price</label>
                            <input type="number" id="productSalePrice" class="form-control form-control-custom" step="0.01">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">SKU</label>
                            <input type="text" id="productSku" class="form-control form-control-custom">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Stock *</label>
                            <input type="number" id="productStock" class="form-control form-control-custom" required>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-3">
                                <input type="checkbox" id="productFeatured" class="form-check-input">
                                <label class="form-check-label text-muted-custom" for="productFeatured">Featured Product</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-3">
                                <input type="checkbox" id="productActive" class="form-check-input" checked>
                                <label class="form-check-label text-muted-custom" for="productActive">Active</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="border-top:1px solid var(--border-glass);">
                <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary-custom" onclick="saveProduct()">
                    <i class="bi bi-check-lg me-1"></i> Save Product
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function resetForm() {
    document.getElementById('productModalTitle').textContent = 'Add Product';
    document.getElementById('productId').value = '';
    document.getElementById('productForm').reset();
    document.getElementById('productActive').checked = true;
}

function editProduct(product) {
    document.getElementById('productModalTitle').textContent = 'Edit Product';
    document.getElementById('productId').value = product.id;
    document.getElementById('productName').value = product.name;
    document.getElementById('productCategory').value = product.category_id;
    document.getElementById('productShortDesc').value = product.short_description || '';
    document.getElementById('productDesc').value = product.description;
    document.getElementById('productPrice').value = product.price;
    document.getElementById('productSalePrice').value = product.sale_price || '';
    document.getElementById('productSku').value = product.sku || '';
    document.getElementById('productStock').value = product.stock_quantity;
    document.getElementById('productFeatured').checked = product.is_featured == 1;
    document.getElementById('productActive').checked = product.is_active == 1;
    
    new bootstrap.Modal(document.getElementById('productModal')).show();
}

async function saveProduct() {
    const id = document.getElementById('productId').value;
    const data = {
        name: document.getElementById('productName').value,
        category_id: document.getElementById('productCategory').value,
        short_description: document.getElementById('productShortDesc').value,
        description: document.getElementById('productDesc').value,
        price: document.getElementById('productPrice').value,
        sale_price: document.getElementById('productSalePrice').value || null,
        sku: document.getElementById('productSku').value,
        stock_quantity: document.getElementById('productStock').value,
        is_featured: document.getElementById('productFeatured').checked ? 1 : 0,
        is_active: document.getElementById('productActive').checked ? 1 : 0
    };

    const url = id ? `../../api/products.php?id=${id}` : '../../api/products.php';
    const method = id ? 'PUT' : 'POST';

    const response = await fetch(url, {
        method: method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
    const result = await response.json();

    if (result.success) {
        showToast(result.message);
        setTimeout(() => location.reload(), 1000);
    } else {
        showToast(result.message || 'Error saving product', 'error');
    }
}

async function deleteProduct(id) {
    if (!confirm('Are you sure you want to delete this product?')) return;
    
    const response = await fetch(`../../api/products.php?id=${id}`, { method: 'DELETE' });
    const result = await response.json();
    
    if (result.success) {
        showToast('Product deleted');
        setTimeout(() => location.reload(), 1000);
    } else {
        showToast('Delete failed', 'error');
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
