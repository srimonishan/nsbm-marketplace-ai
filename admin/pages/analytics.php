<?php
/**
 * Admin Analytics - GreenLink Market
 */
require_once __DIR__ . '/../../config/init.php';

$currentPage = 'analytics';
$pageTitle = 'Revenue Analytics';

$orderModel = new Order();
$productModel = new Product();
$userModel = new User();

$totalRevenue = $orderModel->getTotalRevenue();
$totalOrders = $orderModel->getCount();
$totalCustomers = $userModel->getCount('customer');
$avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

include __DIR__ . '/../includes/header.php';
?>

<!-- Analytics Stats -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-card-icon" style="background:rgba(8, 122, 75,0.15);color:var(--primary);">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div class="stat-card-value text-gradient"><?= formatPrice($totalRevenue) ?></div>
            <div class="stat-card-label">Total Revenue</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-card-icon" style="background:rgba(182, 227, 74,0.15);color:var(--secondary);">
                <i class="bi bi-bag"></i>
            </div>
            <div class="stat-card-value"><?= $totalOrders ?></div>
            <div class="stat-card-label">Total Orders</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-card-icon" style="background:rgba(255,107,157,0.15);color:var(--accent);">
                <i class="bi bi-receipt"></i>
            </div>
            <div class="stat-card-value"><?= formatPrice($avgOrderValue) ?></div>
            <div class="stat-card-label">Avg. Order Value</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-card-icon" style="background:rgba(255,193,7,0.15);color:#ffc107;">
                <i class="bi bi-person-check"></i>
            </div>
            <div class="stat-card-value"><?= $totalCustomers ?></div>
            <div class="stat-card-label">Active Customers</div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="row g-4">
    <div class="col-lg-8">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5><i class="bi bi-bar-chart me-2"></i>Monthly Revenue</h5>
            </div>
            <div class="admin-card-body">
                <div class="chart-container">
                    <canvas id="monthlyRevenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5><i class="bi bi-pie-chart me-2"></i>Sales by Category</h5>
            </div>
            <div class="admin-card-body">
                <div class="chart-container">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5><i class="bi bi-graph-up-arrow me-2"></i>Orders Trend</h5>
            </div>
            <div class="admin-card-body">
                <div class="chart-container">
                    <canvas id="ordersTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Monthly Revenue Chart
new Chart(document.getElementById('monthlyRevenueChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
            label: 'Revenue (Rs.)',
            data: [120000, 180000, 150000, 220000, 280000, 250000, 310000, 290000, 340000, 380000, 420000, 332422],
            backgroundColor: 'rgba(8, 122, 75, 0.6)',
            borderColor: '#087a4b',
            borderWidth: 2,
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#6b6b8a' } },
            y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#6b6b8a', callback: v => 'Rs.' + (v/1000) + 'K' } }
        }
    }
});

// Category Chart
new Chart(document.getElementById('categoryChart').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: ['Electronics', 'Books', 'Fashion', 'Food', 'Sports', 'Art', 'Services', 'Dorm'],
        datasets: [{
            data: [35, 15, 20, 10, 5, 5, 5, 5],
            backgroundColor: ['#087a4b', '#b6e34a', '#f2b84b', '#ffc107', '#17a2b8', '#e83e8c', '#6610f2', '#20c997'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '60%',
        plugins: { legend: { position: 'bottom', labels: { color: '#b4b4cc', font: { size: 10 }, padding: 10 } } }
    }
});

// Orders Trend
new Chart(document.getElementById('ordersTrendChart').getContext('2d'), {
    type: 'line',
    data: {
        labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6', 'Week 7', 'Week 8'],
        datasets: [{
            label: 'Orders',
            data: [12, 19, 15, 25, 22, 30, 28, 35],
            borderColor: '#b6e34a',
            backgroundColor: 'rgba(182, 227, 74, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#6b6b8a' } },
            y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#6b6b8a' } }
        }
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
