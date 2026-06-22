<?php
if (!isset($currentPage)) $currentPage = 'dashboard';
if (!isset($pageTitle)) $pageTitle = 'Dashboard';

if (!isAdmin()) {
    $loginPath = strpos($_SERVER['PHP_SELF'], '/pages/') !== false ? '../login.php' : 'login.php';
    redirect($loginPath);
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> | Admin - GreenLink Market</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="<?= strpos($_SERVER['PHP_SELF'], '/pages/') !== false ? '../../' : '../' ?>assets/css/style.css?v=1.0.1" rel="stylesheet">
    <link href="<?= strpos($_SERVER['PHP_SELF'], '/pages/') !== false ? '../' : '' ?>assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="admin-sidebar-brand">
                <div class="brand-icon"><i class="bi bi-shop"></i></div>
                <div>
                    <h5>NSBM Admin</h5>
                    <small style="color:var(--text-muted);font-size:0.7rem;">Marketplace AI</small>
                </div>
            </div>
            <nav class="admin-nav">
                <div class="admin-nav-section">Main</div>
                <a href="<?= strpos($_SERVER['PHP_SELF'], '/pages/') !== false ? '../' : '' ?>index.php" class="admin-nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                
                <div class="admin-nav-section">Catalog</div>
                <a href="<?= strpos($_SERVER['PHP_SELF'], '/pages/') !== false ? '' : 'pages/' ?>products.php" class="admin-nav-link <?= $currentPage === 'products' ? 'active' : '' ?>">
                    <i class="bi bi-box-seam"></i> Products
                </a>
                <a href="<?= strpos($_SERVER['PHP_SELF'], '/pages/') !== false ? '' : 'pages/' ?>categories.php" class="admin-nav-link <?= $currentPage === 'categories' ? 'active' : '' ?>">
                    <i class="bi bi-collection"></i> Categories
                </a>
                
                <div class="admin-nav-section">Sales</div>
                <a href="<?= strpos($_SERVER['PHP_SELF'], '/pages/') !== false ? '' : 'pages/' ?>orders.php" class="admin-nav-link <?= $currentPage === 'orders' ? 'active' : '' ?>">
                    <i class="bi bi-bag-check"></i> Orders
                </a>
                <a href="<?= strpos($_SERVER['PHP_SELF'], '/pages/') !== false ? '' : 'pages/' ?>customers.php" class="admin-nav-link <?= $currentPage === 'customers' ? 'active' : '' ?>">
                    <i class="bi bi-people"></i> Customers
                </a>
                
                <div class="admin-nav-section">AI & Analytics</div>
                <a href="<?= strpos($_SERVER['PHP_SELF'], '/pages/') !== false ? '' : 'pages/' ?>analytics.php" class="admin-nav-link <?= $currentPage === 'analytics' ? 'active' : '' ?>">
                    <i class="bi bi-graph-up"></i> Analytics
                </a>
                <a href="<?= strpos($_SERVER['PHP_SELF'], '/pages/') !== false ? '' : 'pages/' ?>ai-settings.php" class="admin-nav-link <?= $currentPage === 'ai-settings' ? 'active' : '' ?>">
                    <i class="bi bi-robot"></i> AI Settings
                </a>
                
                <div class="admin-nav-section">System</div>
                <a href="<?= strpos($_SERVER['PHP_SELF'], '/pages/') !== false ? '' : 'pages/' ?>messages.php" class="admin-nav-link <?= $currentPage === 'messages' ? 'active' : '' ?>">
                    <i class="bi bi-envelope"></i> Messages
                </a>
                <a href="#" class="admin-nav-link" onclick="adminViewStore(event)">
                    <i class="bi bi-house"></i> View Store
                </a>
                <a href="#" class="admin-nav-link" onclick="adminLogout()">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="admin-main">
            <!-- Top Bar -->
            <header class="admin-topbar">
                <div class="admin-topbar-left">
                    <button class="btn btn-glass btn-sm d-lg-none" onclick="toggleSidebar()">
                        <i class="bi bi-list"></i>
                    </button>
                    <h4 class="admin-page-title"><?= $pageTitle ?></h4>
                </div>
                <div class="admin-topbar-right">
                    <button class="btn btn-glass btn-sm" title="Notifications">
                        <i class="bi bi-bell"></i>
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-glass btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i> Admin
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" style="background:var(--bg-card);border:1px solid var(--border-glass);">
                            <li><a class="dropdown-item text-light" href="#"><i class="bi bi-gear me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider" style="border-color:var(--border-glass);"></li>
                            <li><a class="dropdown-item text-light" href="#" onclick="adminLogout()"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="admin-content">
