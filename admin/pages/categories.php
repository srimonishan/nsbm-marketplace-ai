<?php
/**
 * Admin Categories Management - NSBM Marketplace AI
 */
session_start();
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Product.php';
require_once __DIR__ . '/../../models/Category.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Order.php';
require_once __DIR__ . '/../../models/Review.php';

$currentPage = 'categories';
$pageTitle = 'Categories Management';

$categoryModel = new Category();
$categories = $categoryModel->getAll(false);

include __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="text-muted-custom mb-0"><?= count($categories) ?> categories</p>
    <button class="btn btn-primary-custom btn-sm" data-bs-toggle="modal" data-bs-target="#categoryModal" onclick="resetCatForm()">
        <i class="bi bi-plus-lg me-1"></i> Add Category
    </button>
</div>

<div class="row g-4">
    <?php foreach ($categories as $category): ?>
    <div class="col-lg-4 col-md-6">
        <div class="admin-card">
            <div class="admin-card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="mb-1"><?= sanitize($category['name']) ?></h5>
                        <p class="text-muted-custom small mb-2"><?= sanitize($category['description'] ?? 'No description') ?></p>
                        <span class="badge-glass"><?= $category['product_count'] ?? 0 ?> Products</span>
                        <span class="status-badge status-<?= $category['is_active'] ? 'active' : 'inactive' ?> ms-2">
                            <?= $category['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </div>
                    <div class="d-flex gap-1">
                        <button class="btn-action" onclick='editCategory(<?= json_encode($category) ?>)'>
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn-action danger" onclick="deleteCategory(<?= $category['id'] ?>)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="background:var(--bg-card);border:1px solid var(--border-glass);">
            <div class="modal-header" style="border-bottom:1px solid var(--border-glass);">
                <h5 class="modal-title" id="catModalTitle">Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1);"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="catId" value="">
                <div class="mb-3">
                    <label class="form-label-custom">Category Name *</label>
                    <input type="text" id="catName" class="form-control form-control-custom" required>
                </div>
                <div class="mb-3">
                    <label class="form-label-custom">Description</label>
                    <textarea id="catDesc" class="form-control form-control-custom" rows="3"></textarea>
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label-custom">Sort Order</label>
                        <input type="number" id="catSort" class="form-control form-control-custom" value="0">
                    </div>
                    <div class="col-6">
                        <div class="form-check mt-4">
                            <input type="checkbox" id="catActive" class="form-check-input" checked>
                            <label class="form-check-label text-muted-custom" for="catActive">Active</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid var(--border-glass);">
                <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary-custom" onclick="saveCategory()">
                    <i class="bi bi-check-lg me-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function resetCatForm() {
    document.getElementById('catModalTitle').textContent = 'Add Category';
    document.getElementById('catId').value = '';
    document.getElementById('catName').value = '';
    document.getElementById('catDesc').value = '';
    document.getElementById('catSort').value = '0';
    document.getElementById('catActive').checked = true;
}

function editCategory(cat) {
    document.getElementById('catModalTitle').textContent = 'Edit Category';
    document.getElementById('catId').value = cat.id;
    document.getElementById('catName').value = cat.name;
    document.getElementById('catDesc').value = cat.description || '';
    document.getElementById('catSort').value = cat.sort_order;
    document.getElementById('catActive').checked = cat.is_active == 1;
    new bootstrap.Modal(document.getElementById('categoryModal')).show();
}

async function saveCategory() {
    const id = document.getElementById('catId').value;
    const data = {
        name: document.getElementById('catName').value,
        description: document.getElementById('catDesc').value,
        sort_order: parseInt(document.getElementById('catSort').value),
        is_active: document.getElementById('catActive').checked ? 1 : 0
    };

    const url = id ? `../../api/categories.php?id=${id}` : '../../api/categories.php';
    const method = id ? 'PUT' : 'POST';

    const response = await fetch(url, { method, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
    const result = await response.json();

    if (result.success) {
        showToast(result.message);
        setTimeout(() => location.reload(), 1000);
    } else {
        showToast(result.message || 'Error', 'error');
    }
}

async function deleteCategory(id) {
    if (!confirm('Delete this category? Products in this category will also be affected.')) return;
    const response = await fetch(`../../api/categories.php?id=${id}`, { method: 'DELETE' });
    const result = await response.json();
    if (result.success) { showToast('Deleted'); setTimeout(() => location.reload(), 1000); }
    else { showToast('Failed', 'error'); }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
