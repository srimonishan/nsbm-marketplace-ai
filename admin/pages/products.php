<?php
/**
 * Admin Products Management - GreenLink Market
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
                    <?php $adminProductImage = productImageUrl($product['image'] ?? null); ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="admin-product-thumb">
                                    <?php if ($adminProductImage): ?>
                                        <img src="<?= sanitize($adminProductImage) ?>" alt="<?= sanitize($product['name']) ?>">
                                    <?php else: ?>
                                        <i class="bi bi-box-seam"></i>
                                    <?php endif; ?>
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
                            <?php $product['image_url_resolved'] = $adminProductImage; ?>
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
                        <div class="col-12">
                            <div class="product-image-editor">
                                <div class="product-image-preview" id="productImagePreview">
                                    <img id="productImagePreviewImg" src="" alt="Product image preview" hidden>
                                    <div id="productImagePlaceholder" class="product-image-placeholder">
                                        <i class="bi bi-image"></i>
                                        <span>Product image preview</span>
                                    </div>
                                </div>
                                <div class="product-image-controls">
                                    <div>
                                        <label class="form-label-custom" for="productImageFile">Upload image</label>
                                        <input type="file" id="productImageFile" name="image_file" class="form-control form-control-custom" accept="image/jpeg,image/png,image/webp">
                                        <small class="text-muted-custom">JPG, PNG or WebP · maximum 5 MB</small>
                                    </div>
                                    <div class="product-image-divider"><span>or</span></div>
                                    <div>
                                        <label class="form-label-custom" for="productImageUrl">Image link</label>
                                        <input type="url" id="productImageUrl" class="form-control form-control-custom" placeholder="https://example.com/product.jpg">
                                        <small class="text-muted-custom">Use a direct HTTP or HTTPS image URL</small>
                                    </div>
                                    <div class="form-check mt-2" id="removeImageWrap" hidden>
                                        <input type="checkbox" id="productRemoveImage" class="form-check-input">
                                        <label class="form-check-label text-muted-custom" for="productRemoveImage">Remove current image</label>
                                    </div>
                                </div>
                            </div>
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
    document.getElementById('removeImageWrap').hidden = true;
    setImagePreview('');
}

function setImagePreview(src) {
    const image = document.getElementById('productImagePreviewImg');
    const placeholder = document.getElementById('productImagePlaceholder');
    if (!src) {
        image.hidden = true;
        image.removeAttribute('src');
        placeholder.hidden = false;
        return;
    }
    image.src = src;
    image.hidden = false;
    placeholder.hidden = true;
    image.onerror = () => {
        image.hidden = true;
        placeholder.hidden = false;
    };
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
    document.getElementById('productImageFile').value = '';
    document.getElementById('productImageUrl').value = /^https?:\/\//i.test(product.image || '') ? product.image : '';
    document.getElementById('productRemoveImage').checked = false;
    document.getElementById('removeImageWrap').hidden = !product.image;
    setImagePreview(product.image_url_resolved || '');
    
    new bootstrap.Modal(document.getElementById('productModal')).show();
}

async function saveProduct() {
    const id = document.getElementById('productId').value;
    const form = document.getElementById('productForm');
    if (!form.reportValidity()) return;

    const data = new FormData();
    data.append('name', document.getElementById('productName').value);
    data.append('category_id', document.getElementById('productCategory').value);
    data.append('short_description', document.getElementById('productShortDesc').value);
    data.append('description', document.getElementById('productDesc').value);
    data.append('price', document.getElementById('productPrice').value);
    data.append('sale_price', document.getElementById('productSalePrice').value);
    data.append('sku', document.getElementById('productSku').value);
    data.append('stock_quantity', document.getElementById('productStock').value);
    data.append('is_featured', document.getElementById('productFeatured').checked ? '1' : '0');
    data.append('is_active', document.getElementById('productActive').checked ? '1' : '0');
    data.append('image_url', document.getElementById('productImageUrl').value.trim());
    data.append('remove_image', document.getElementById('productRemoveImage').checked ? '1' : '0');
    const imageFile = document.getElementById('productImageFile').files[0];
    if (imageFile) data.append('image_file', imageFile);
    if (id) data.append('_method', 'PUT');

    const url = id ? `../../api/products.php?id=${id}` : '../../api/products.php';
    const response = await fetch(url, {
        method: 'POST',
        body: data
    });
    const result = await response.json();

    if (result.success) {
        showToast(result.message);
        setTimeout(() => location.reload(), 1000);
    } else {
        showToast(result.message || 'Error saving product', 'error');
    }
}

document.getElementById('productImageFile').addEventListener('change', event => {
    const file = event.target.files[0];
    if (file) {
        document.getElementById('productImageUrl').value = '';
        document.getElementById('productRemoveImage').checked = false;
        setImagePreview(URL.createObjectURL(file));
    }
});

document.getElementById('productImageUrl').addEventListener('input', event => {
    const url = event.target.value.trim();
    if (url) {
        document.getElementById('productImageFile').value = '';
        document.getElementById('productRemoveImage').checked = false;
    }
    setImagePreview(url);
});

document.getElementById('productRemoveImage').addEventListener('change', event => {
    if (event.target.checked) {
        document.getElementById('productImageFile').value = '';
        document.getElementById('productImageUrl').value = '';
        setImagePreview('');
    }
});

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
