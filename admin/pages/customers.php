<?php
/**
 * Admin Customers Management - GreenLink Market
 */
session_start();
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Product.php';
require_once __DIR__ . '/../../models/Category.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Order.php';
require_once __DIR__ . '/../../models/Review.php';

$currentPage = 'customers';
$pageTitle = 'Customer Management';

$userModel = new User();
$customers = $userModel->getAll();
$totalCustomers = $userModel->getCount();

include __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="text-muted-custom mb-0"><?= $totalCustomers ?> total users</p>
</div>

<div class="admin-card">
    <div class="admin-card-body p-0">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:35px;height:35px;background:var(--gradient-primary);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.8rem;color:white;font-weight:700;">
                                    <?= strtoupper(substr($customer['first_name'], 0, 1) . substr($customer['last_name'], 0, 1)) ?>
                                </div>
                                <strong style="font-size:0.85rem;"><?= sanitize($customer['first_name'] . ' ' . $customer['last_name']) ?></strong>
                            </div>
                        </td>
                        <td><small><?= sanitize($customer['email']) ?></small></td>
                        <td><small><?= sanitize($customer['phone'] ?? 'N/A') ?></small></td>
                        <td><span class="badge-glass"><?= ucfirst($customer['role']) ?></span></td>
                        <td><span class="status-badge status-<?= $customer['status'] ?>"><?= ucfirst($customer['status']) ?></span></td>
                        <td><small class="text-muted-custom"><?= date('M d, Y', strtotime($customer['created_at'])) ?></small></td>
                        <td>
                            <button class="btn-action" onclick="toggleStatus(<?= $customer['id'] ?>, '<?= $customer['status'] === 'active' ? 'inactive' : 'active' ?>')" title="Toggle Status">
                                <i class="bi bi-toggle-<?= $customer['status'] === 'active' ? 'on' : 'off' ?>"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
async function toggleStatus(id, status) {
    const response = await fetch(`../../api/admin.php?action=update_customer&id=${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ status })
    });
    const result = await response.json();
    if (result.success) { showToast('Status updated'); setTimeout(() => location.reload(), 1000); }
    else showToast('Failed', 'error');
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
