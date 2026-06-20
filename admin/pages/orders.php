<?php
/**
 * Admin Orders Management - GreenLink Market
 */
session_start();
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Product.php';
require_once __DIR__ . '/../../models/Category.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Order.php';
require_once __DIR__ . '/../../models/Review.php';

$currentPage = 'orders';
$pageTitle = 'Order Management';

$orderModel = new Order();
$statusFilter = $_GET['status'] ?? '';
$filters = $statusFilter ? ['status' => $statusFilter] : [];
$page = max(1, (int)($_GET['page'] ?? 1));
$orders = $orderModel->getAll(ADMIN_ITEMS_PER_PAGE, ($page - 1) * ADMIN_ITEMS_PER_PAGE, $filters);
$totalOrders = $orderModel->getCount($filters);

include __DIR__ . '/../includes/header.php';
?>

<!-- Filter Bar -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div class="d-flex gap-2 flex-wrap">
        <a href="orders.php" class="btn btn-glass btn-sm <?= !$statusFilter ? 'active' : '' ?>">All (<?= $orderModel->getCount() ?>)</a>
        <a href="orders.php?status=pending" class="btn btn-glass btn-sm <?= $statusFilter === 'pending' ? 'active' : '' ?>">Pending</a>
        <a href="orders.php?status=processing" class="btn btn-glass btn-sm <?= $statusFilter === 'processing' ? 'active' : '' ?>">Processing</a>
        <a href="orders.php?status=shipped" class="btn btn-glass btn-sm <?= $statusFilter === 'shipped' ? 'active' : '' ?>">Shipped</a>
        <a href="orders.php?status=delivered" class="btn btn-glass btn-sm <?= $statusFilter === 'delivered' ? 'active' : '' ?>">Delivered</a>
    </div>
    <p class="text-muted-custom mb-0"><?= $totalOrders ?> orders</p>
</div>

<!-- Orders Table -->
<div class="admin-card">
    <div class="admin-card-body p-0">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><strong><?= $order['order_number'] ?></strong></td>
                        <td>
                            <div>
                                <strong style="font-size:0.85rem;"><?= sanitize(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? '')) ?></strong>
                                <div class="text-muted-custom" style="font-size:0.75rem;"><?= sanitize($order['email'] ?? '') ?></div>
                            </div>
                        </td>
                        <td><strong><?= formatPrice($order['total']) ?></strong></td>
                        <td><span class="status-badge status-<?= $order['payment_status'] ?>"><?= ucfirst($order['payment_status']) ?></span></td>
                        <td>
                            <select class="form-control form-control-custom form-control-sm" style="width:auto;font-size:0.8rem;" 
                                    onchange="updateOrderStatus(<?= $order['id'] ?>, this.value)">
                                <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                                <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </td>
                        <td><small class="text-muted-custom"><?= date('M d, Y', strtotime($order['created_at'])) ?></small></td>
                        <td>
                            <button class="btn-action" onclick="viewOrder(<?= $order['id'] ?>)" title="View">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($orders)): ?>
                    <tr><td colspan="7" class="text-center text-muted-custom py-4">No orders found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Order Detail Modal -->
<div class="modal fade" id="orderDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background:var(--bg-card);border:1px solid var(--border-glass);">
            <div class="modal-header" style="border-bottom:1px solid var(--border-glass);">
                <h5 class="modal-title">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1);"></button>
            </div>
            <div class="modal-body" id="orderDetailContent">
                <div class="text-center py-4"><div class="loading-spinner mx-auto"></div></div>
            </div>
        </div>
    </div>
</div>

<script>
async function updateOrderStatus(orderId, status) {
    const response = await fetch(`../../api/orders.php?id=${orderId}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ status })
    });
    const result = await response.json();
    if (result.success) showToast('Order status updated');
    else showToast('Update failed', 'error');
}

async function viewOrder(orderId) {
    const modal = new bootstrap.Modal(document.getElementById('orderDetailModal'));
    modal.show();
    
    const response = await fetch(`../../api/orders.php?id=${orderId}`);
    const result = await response.json();
    
    if (result.success) {
        const order = result.data;
        let itemsHtml = '';
        if (order.items) {
            order.items.forEach(item => {
                itemsHtml += `<tr><td>${item.product_name}</td><td>${item.quantity}</td><td>Rs. ${parseFloat(item.price).toLocaleString()}</td><td>Rs. ${parseFloat(item.total).toLocaleString()}</td></tr>`;
            });
        }
        
        document.getElementById('orderDetailContent').innerHTML = `
            <div class="row g-3">
                <div class="col-md-6">
                    <h6>Order Info</h6>
                    <p class="small text-muted-custom mb-1"><strong>Order:</strong> ${order.order_number}</p>
                    <p class="small text-muted-custom mb-1"><strong>Date:</strong> ${order.created_at}</p>
                    <p class="small text-muted-custom mb-1"><strong>Status:</strong> ${order.status}</p>
                    <p class="small text-muted-custom mb-1"><strong>Payment:</strong> ${order.payment_status}</p>
                </div>
                <div class="col-md-6">
                    <h6>Shipping</h6>
                    <p class="small text-muted-custom mb-1"><strong>Address:</strong> ${order.shipping_address}</p>
                    <p class="small text-muted-custom mb-1"><strong>City:</strong> ${order.shipping_city}</p>
                    <p class="small text-muted-custom mb-1"><strong>Phone:</strong> ${order.shipping_phone}</p>
                </div>
                <div class="col-12 mt-3">
                    <h6>Items</h6>
                    <table class="admin-table"><thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead><tbody>${itemsHtml}</tbody></table>
                </div>
                <div class="col-12 text-end mt-2">
                    <p class="mb-0"><strong>Total: Rs. ${parseFloat(order.total).toLocaleString()}</strong></p>
                </div>
            </div>
        `;
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
