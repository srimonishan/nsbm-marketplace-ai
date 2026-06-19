<?php
if (session_status() === PHP_SESSION_NONE) {
    require_once __DIR__ . '/../config/init.php';
}
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="NSBM Marketplace AI - Premium AI-Powered Shopping for NSBM Green University Students">
    <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' | ' : '' ?>NSBM Marketplace AI</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= isset($isSubPage) ? '../' : '' ?>assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?= isset($isSubPage) ? '../' : '' ?>index.php">
                <span class="brand-icon"><i class="bi bi-shop"></i></span>
                NSBM Marketplace
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'index' ? 'active' : '' ?>" href="<?= isset($isSubPage) ? '../' : '' ?>index.php">
                            <i class="bi bi-house-door me-1"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'products' ? 'active' : '' ?>" href="<?= isset($isSubPage) ? '' : 'pages/' ?>products.php">
                            <i class="bi bi-grid me-1"></i> Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'categories' ? 'active' : '' ?>" href="<?= isset($isSubPage) ? '' : 'pages/' ?>categories.php">
                            <i class="bi bi-collection me-1"></i> Categories
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'about' ? 'active' : '' ?>" href="<?= isset($isSubPage) ? '' : 'pages/' ?>about.php">
                            <i class="bi bi-info-circle me-1"></i> About
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'contact' ? 'active' : '' ?>" href="<?= isset($isSubPage) ? '' : 'pages/' ?>contact.php">
                            <i class="bi bi-envelope me-1"></i> Contact
                        </a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center gap-3">
                    <!-- Search -->
                    <button class="btn btn-glass btn-sm" data-bs-toggle="modal" data-bs-target="#searchModal">
                        <i class="bi bi-search"></i>
                    </button>
                    
                    <!-- Cart -->
                    <a href="<?= isset($isSubPage) ? '' : 'pages/' ?>cart.php" class="btn btn-glass btn-sm position-relative">
                        <i class="bi bi-cart3"></i>
                        <span class="nav-cart-badge cart-count" style="display:none;">0</span>
                    </a>
                    
                    <!-- Auth Links -->
                    <div class="auth-links">
                        <button class="btn btn-primary-custom btn-sm" data-bs-toggle="modal" data-bs-target="#authModal">
                            <i class="bi bi-person me-1"></i> Login
                        </button>
                    </div>
                    
                    <!-- User Links (shown when logged in) -->
                    <div class="user-links dropdown" style="display:none;">
                        <button class="btn btn-glass btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i> <span class="user-name">User</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" style="background:var(--bg-card);border:1px solid var(--border-glass);">
                            <li><a class="dropdown-item text-light" href="<?= isset($isSubPage) ? '' : 'pages/' ?>orders.php"><i class="bi bi-bag me-2"></i>My Orders</a></li>
                            <li><a class="dropdown-item text-light" href="<?= isset($isSubPage) ? '../' : '' ?>admin/index.php"><i class="bi bi-speedometer2 me-2"></i>Admin Panel</a></li>
                            <li><hr class="dropdown-divider" style="border-color:var(--border-glass);"></li>
                            <li><a class="dropdown-item text-light" href="#" onclick="Auth.logout()"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Search Modal -->
    <div class="modal fade modal-custom" id="searchModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body p-4">
                    <form action="<?= isset($isSubPage) ? '' : 'pages/' ?>products.php" method="GET">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control form-control-custom form-control-lg" placeholder="Search products..." autofocus>
                            <button class="btn btn-primary-custom" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Auth Modal -->
    <div class="modal fade modal-custom" id="authModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <ul class="nav nav-pills w-100" id="authTabs">
                        <li class="nav-item flex-fill">
                            <button class="nav-link active w-100 btn-glass" data-bs-toggle="pill" data-bs-target="#loginTab">Login</button>
                        </li>
                        <li class="nav-item flex-fill">
                            <button class="nav-link w-100 btn-glass" data-bs-toggle="pill" data-bs-target="#registerTab">Register</button>
                        </li>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="tab-content">
                        <!-- Login Tab -->
                        <div class="tab-pane fade show active" id="loginTab">
                            <form id="loginForm" onsubmit="handleLogin(event)">
                                <div class="mb-3">
                                    <label class="form-label-custom">Email</label>
                                    <input type="email" name="email" class="form-control form-control-custom" placeholder="your@nsbm.ac.lk" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label-custom">Password</label>
                                    <input type="password" name="password" class="form-control form-control-custom" placeholder="Enter password" required>
                                </div>
                                <div class="mb-3 text-muted-custom small">
                                    Demo: admin@nsbm.ac.lk / password
                                </div>
                                <button type="submit" class="btn btn-primary-custom w-100">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Login
                                </button>
                            </form>
                        </div>
                        <!-- Register Tab -->
                        <div class="tab-pane fade" id="registerTab">
                            <form id="registerForm" onsubmit="handleRegister(event)">
                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <label class="form-label-custom">First Name</label>
                                        <input type="text" name="first_name" class="form-control form-control-custom" required>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label class="form-label-custom">Last Name</label>
                                        <input type="text" name="last_name" class="form-control form-control-custom" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label-custom">Email</label>
                                    <input type="email" name="email" class="form-control form-control-custom" placeholder="your@students.nsbm.ac.lk" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label-custom">Password</label>
                                    <input type="password" name="password" class="form-control form-control-custom" placeholder="Min 8 characters" required minlength="8">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label-custom">Confirm Password</label>
                                    <input type="password" name="confirm_password" class="form-control form-control-custom" required>
                                </div>
                                <button type="submit" class="btn btn-primary-custom w-100">
                                    <i class="bi bi-person-plus me-2"></i>Create Account
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main>
