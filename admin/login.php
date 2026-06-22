<?php
/**
 * Admin Login Page - GreenLink Market
 */
require_once __DIR__ . '/../config/init.php';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - GreenLink Market</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="../assets/css/style.css?v=1.0.1" rel="stylesheet">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="glass-card p-5">
            <div class="text-center mb-4">
                <div style="width:60px;height:60px;background:var(--gradient-primary);border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:1.5rem;color:white;">
                    <i class="bi bi-shield-lock"></i>
                </div>
                <h3 class="mb-1">Admin Login</h3>
                <p class="text-muted-custom small">GreenLink Market Panel</p>
            </div>

            <form id="loginForm" onsubmit="handleLogin(event)">
                <div class="mb-3">
                    <label class="form-label-custom">Email Address</label>
                    <input type="email" name="email" class="form-control form-control-custom" required placeholder="admin@nsbm.ac.lk" value="admin@nsbm.ac.lk">
                </div>
                <div class="mb-3">
                    <label class="form-label-custom">Password</label>
                    <input type="password" name="password" class="form-control form-control-custom" required placeholder="Enter password" value="admin123">
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="rememberMe">
                    <label class="form-check-label text-muted-custom" for="rememberMe">Remember me</label>
                </div>
                <button type="submit" class="btn btn-primary-custom w-100 btn-lg" id="loginBtn">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Login
                </button>
            </form>

            <div class="text-center mt-3">
                <a href="../index.php" class="text-muted-custom small"><i class="bi bi-arrow-left me-1"></i>Back to Store</a>
            </div>
        </div>
    </div>

    <script>
    async function handleLogin(e) {
        e.preventDefault();
        const form = e.target;
        const btn = document.getElementById('loginBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="loading-spinner" style="width:16px;height:16px;border-width:2px;display:inline-block;"></span> Logging in...';

        const data = {
            email: form.email.value,
            password: form.password.value,
            role: 'admin'
        };

        try {
            const response = await fetch('../api/auth.php?action=login', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-GreenLink-Portal': 'admin'
                },
                body: JSON.stringify(data)
            });
            const result = await response.json();

            if (result.success) {
                window.location.href = 'index.php';
            } else {
                alert(result.message || 'Login failed');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-box-arrow-in-right me-2"></i>Login';
            }
        } catch (error) {
            alert('Connection error');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-box-arrow-in-right me-2"></i>Login';
        }
    }
    </script>
</body>
</html>
