<?php
/**
 * Admin Dashboard - NSBM Marketplace AI
 */
session_start();
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Review.php';

$currentPage = 'dashboard';
$pageTitle = 'Dashboard';

// Get stats
try {
    $productModel = new Product();
    $userModel = new User();
    $orderModel = new Order();

    $totalProducts = $productModel->getCount();
    $totalCustomers = $userModel->getCount('customer');
    $totalOrders = $orderModel->getCount();
    $totalRevenue = $orderModel->getTotalRevenue();
    $recentOrders = $orderModel->getRecent(5);
    $recentCustomers = $userModel->getRecent(5);
    $orderStatus = $orderModel->getStatusCounts();
} catch (Exception $e) {
    // If DB not connected, use demo data
    $totalProducts = 20;
    $totalCustomers = 4;
    $totalOrders = 5;
    $totalRevenue = 332422;
    $recentOrders = [];
    $recentCustomers = [];
    $orderStatus = ['pending' => 1, 'processing' => 1, 'shipped' => 1, 'delivered' => 2];
}

include __DIR__ . '/includes/header.php';
?>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-card-icon" style="background:rgba(108,99,255,0.15);color:var(--primary);">
                <i class="bi bi-box-seam"></i>
            </div>
            <div class="stat-card-value"><?= number_format($totalProducts) ?></div>
            <div class="stat-card-label">Total Products</div>
            <div class="stat-card-change positive"><i class="bi bi-arrow-up"></i> Active listings</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-card-icon" style="background:rgba(0,212,170,0.15);color:var(--secondary);">
                <i class="bi bi-people"></i>
            </div>
            <div class="stat-card-value"><?= number_format($totalCustomers) ?></div>
            <div class="stat-card-label">Total Customers</div>
            <div class="stat-card-change positive"><i class="bi bi-arrow-up"></i> NSBM Students</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-card-icon" style="background:rgba(255,107,157,0.15);color:var(--accent);">
                <i class="bi bi-bag-check"></i>
            </div>
            <div class="stat-card-value"><?= number_format($totalOrders) ?></div>
            <div class="stat-card-label">Total Orders</div>
            <div class="stat-card-change positive"><i class="bi bi-arrow-up"></i> All time</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-card-icon" style="background:rgba(255,193,7,0.15);color:#ffc107;">
                <i class="bi bi-currency-dollar"></i>
            </div>
            <div class="stat-card-value"><?= formatPrice($totalRevenue) ?></div>
            <div class="stat-card-label">Total Revenue</div>
            <div class="stat-card-change positive"><i class="bi bi-arrow-up"></i> Lifetime</div>
        </div>
    </div>
</div>

<!-- Charts & Recent Data -->
<div class="row g-4 mb-4">
    <!-- Revenue Chart -->
    <div class="col-lg-8">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5><i class="bi bi-graph-up me-2"></i>Revenue Overview</h5>
                <select class="form-control form-control-custom form-control-sm" style="width:auto;" id="chartPeriod">
                    <option value="week">This Week</option>
                    <option value="month" selected>This Month</option>
                    <option value="year">This Year</option>
                </select>
            </div>
            <div class="admin-card-body">
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Status -->
    <div class="col-lg-4">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5><i class="bi bi-pie-chart me-2"></i>Order Status</h5>
            </div>
            <div class="admin-card-body">
                <div class="chart-container" style="height:250px;">
                    <canvas id="statusChart"></canvas>
                </div>
                <div class="mt-3">
                    <?php foreach ($orderStatus as $status => $count): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="status-badge status-<?= $status ?>"><?= ucfirst($status) ?></span>
                        <strong><?= $count ?></strong>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders & Customers -->
<div class="row g-4">
    <!-- Recent Orders -->
    <div class="col-lg-7">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5><i class="bi bi-clock-history me-2"></i>Recent Orders</h5>
                <a href="pages/orders.php" class="btn btn-glass btn-sm">View All</a>
            </div>
            <div class="admin-card-body p-0">
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentOrders)): ?>
                                <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td><strong><?= $order['order_number'] ?></strong></td>
                                    <td><?= sanitize(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? '')) ?></td>
                                    <td><?= formatPrice($order['total']) ?></td>
                                    <td><span class="status-badge status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center text-muted-custom">No orders yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Customers -->
    <div class="col-lg-5">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5><i class="bi bi-person-plus me-2"></i>New Customers</h5>
                <a href="pages/customers.php" class="btn btn-glass btn-sm">View All</a>
            </div>
            <div class="admin-card-body">
                <?php if (!empty($recentCustomers)): ?>
                    <?php foreach ($recentCustomers as $customer): ?>
                    <div class="d-flex align-items-center gap-3 mb-3 pb-3" style="border-bottom:1px solid var(--border-glass);">
                        <div class="stat-card-icon" style="width:40px;height:40px;min-width:40px;background:var(--gradient-primary);border-radius:50%;font-size:0.9rem;color:white;">
                            <?= strtoupper(substr($customer['first_name'], 0, 1)) ?>
                        </div>
                        <div class="flex-grow-1">
                            <strong style="font-size:0.9rem;"><?= sanitize($customer['first_name'] . ' ' . $customer['last_name']) ?></strong>
                            <div class="text-muted-custom" style="font-size:0.8rem;"><?= sanitize($customer['email']) ?></div>
                        </div>
                        <small class="text-muted-custom"><?= getTimeAgo($customer['created_at']) ?></small>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted-custom">No customers yet</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        datasets: [{
            label: 'Revenue (Rs.)',
            data: [45000, 62000, 38000, 89000, 55000, 72000, 94999],
            borderColor: '#6c63ff',
            backgroundColor: 'rgba(108, 99, 255, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#6c63ff',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            x: {
                grid: { color: 'rgba(255,255,255,0.05)' },
                ticks: { color: '#6b6b8a' }
            },
            y: {
                grid: { color: 'rgba(255,255,255,0.05)' },
                ticks: { color: '#6b6b8a', callback: v => 'Rs.' + (v/1000) + 'K' }
            }
        }
    }
});

// Status Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Delivered', 'Processing', 'Shipped', 'Pending', 'Cancelled'],
        datasets: [{
            data: [<?= $orderStatus['delivered'] ?? 2 ?>, <?= $orderStatus['processing'] ?? 1 ?>, <?= $orderStatus['shipped'] ?? 1 ?>, <?= $orderStatus['pending'] ?? 1 ?>, <?= $orderStatus['cancelled'] ?? 0 ?>],
            backgroundColor: ['#00d4aa', '#17a2b8', '#6c63ff', '#ffc107', '#dc3545'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '70%',
        plugins: {
            legend: { 
                position: 'bottom',
                labels: { color: '#b4b4cc', padding: 15, font: { size: 11 } }
            }
        }
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
