<?php
/**
 * My Orders Page - GreenLink Market
 */
$isSubPage = true;
$pageTitle = 'My Orders';
require_once __DIR__ . '/../config/init.php';

if (isAdmin()) {
    redirect('../admin/index.php');
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="bi bi-bag me-2"></i>My Orders</h1>
        <nav class="breadcrumb-custom">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item active">My Orders</li>
            </ol>
        </nav>
    </div>
</div>

<section class="section">
    <div class="container">
        <div id="ordersContent">
            <div class="text-center py-5">
                <div class="loading-spinner mx-auto"></div>
                <p class="text-muted-custom mt-3">Loading orders...</p>
            </div>
        </div>
    </div>
</section>

<?php
$extraScripts = '
<script>
async function loadOrders() {
    const content = document.getElementById("ordersContent");

    // Wait for the server-side session check before deciding whether the user
    // is logged in. Without this, the page can render the login prompt while
    // the navbar is still loading the authenticated user.
    await Auth.init();
    
    if (!Auth.isLoggedIn()) {
        content.innerHTML = `
            <div class="glass-card text-center py-5">
                <i class="bi bi-lock" style="font-size:3rem;color:var(--text-muted);"></i>
                <h4 class="mt-3">Please Login</h4>
                <p class="text-muted-custom">You need to be logged in to view your orders</p>
                <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#authModal">Login</button>
            </div>
        `;
        return;
    }

    const result = await API.get("orders.php");
    if (result.success && result.data.length > 0) {
        let html = "";
        result.data.forEach(order => {
            const statusColors = {
                pending: "#ffc107",
                processing: "#17a2b8",
                shipped: "#087a4b",
                delivered: "#b6e34a",
                cancelled: "#dc3545",
                refunded: "#6c757d"
            };
            const color = statusColors[order.status] || "#6c757d";
            
            html += `
                <div class="glass-card mb-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h6 class="mb-1">${order.order_number}</h6>
                            <small class="text-muted-custom">${formatDate(order.created_at)}</small>
                        </div>
                        <div class="text-end">
                            <span class="badge" style="background:${color}20;color:${color};padding:0.4rem 0.8rem;border-radius:20px;">
                                ${order.status.charAt(0).toUpperCase() + order.status.slice(1)}
                            </span>
                            <div class="mt-1"><strong class="text-gradient">${formatPrice(order.total)}</strong></div>
                        </div>
                    </div>
                </div>
            `;
        });
        content.innerHTML = html;
    } else {
        content.innerHTML = `
            <div class="glass-card text-center py-5">
                <i class="bi bi-bag-x" style="font-size:3rem;color:var(--text-muted);"></i>
                <h4 class="mt-3">No Orders Yet</h4>
                <p class="text-muted-custom">Start shopping to see your orders here</p>
                <a href="products.php" class="btn btn-primary-custom">Shop Now</a>
            </div>
        `;
    }
}

document.addEventListener("DOMContentLoaded", () => {
    loadOrders();
});

window.addEventListener("auth:changed", loadOrders);
</script>';
include __DIR__ . '/../includes/footer.php';
?>
