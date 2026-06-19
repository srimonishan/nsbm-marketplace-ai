    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="footer-brand">NSBM Marketplace AI</div>
                    <p class="footer-text">
                        The premier AI-powered marketplace exclusively for NSBM Green University students and staff. 
                        Discover, shop, and experience intelligent shopping like never before.
                    </p>
                    <div class="d-flex gap-3 mt-3">
                        <a href="#" class="btn btn-glass btn-sm"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="btn btn-glass btn-sm"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="btn btn-glass btn-sm"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" class="btn btn-glass btn-sm"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h5 class="footer-title">Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="<?= isset($isSubPage) ? '../' : '' ?>index.php">Home</a></li>
                        <li><a href="<?= isset($isSubPage) ? '' : 'pages/' ?>products.php">Products</a></li>
                        <li><a href="<?= isset($isSubPage) ? '' : 'pages/' ?>categories.php">Categories</a></li>
                        <li><a href="<?= isset($isSubPage) ? '' : 'pages/' ?>cart.php">Cart</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h5 class="footer-title">Support</h5>
                    <ul class="footer-links">
                        <li><a href="<?= isset($isSubPage) ? '' : 'pages/' ?>about.php">About Us</a></li>
                        <li><a href="<?= isset($isSubPage) ? '' : 'pages/' ?>contact.php">Contact</a></li>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 col-md-6">
                    <h5 class="footer-title">Contact Info</h5>
                    <ul class="footer-links">
                        <li><i class="bi bi-geo-alt me-2 text-primary"></i>Mahenwaththa, Pitipana, Homagama</li>
                        <li><i class="bi bi-telephone me-2 text-primary"></i>+94 11 544 5000</li>
                        <li><i class="bi bi-envelope me-2 text-primary"></i>marketplace@nsbm.ac.lk</li>
                        <li><i class="bi bi-clock me-2 text-primary"></i>Mon - Fri: 8:00 AM - 6:00 PM</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> NSBM Marketplace AI. All rights reserved. Built with <i class="bi bi-heart-fill text-danger"></i> for NSBM Green University.</p>
            </div>
        </div>
    </footer>

    <!-- AI Chat Widget -->
    <?php 
    $aiChatPath = __DIR__ . '/ai-chat.php';
    if (file_exists($aiChatPath)) {
        include $aiChatPath;
    }
    ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- App JS -->
    <script src="<?= isset($isSubPage) ? '../' : '' ?>assets/js/app.js"></script>
    
    <script>
        // Auth handlers
        async function handleLogin(e) {
            e.preventDefault();
            const form = e.target;
            const result = await Auth.login(
                form.email.value,
                form.password.value
            );
            if (result.success) {
                bootstrap.Modal.getInstance(document.getElementById('authModal')).hide();
                if (result.user.role === 'admin') {
                    setTimeout(() => window.location.href = '<?= isset($isSubPage) ? '../' : '' ?>admin/index.php', 500);
                }
            } else {
                Toast.show(result.message || 'Login failed', 'error');
            }
        }

        async function handleRegister(e) {
            e.preventDefault();
            const form = e.target;
            const result = await Auth.register({
                first_name: form.first_name.value,
                last_name: form.last_name.value,
                email: form.email.value,
                password: form.password.value,
                confirm_password: form.confirm_password.value
            });
            if (result.success) {
                bootstrap.Modal.getInstance(document.getElementById('authModal')).hide();
            } else {
                Toast.show(result.message || 'Registration failed', 'error');
            }
        }
    </script>
    <?php if (isset($extraScripts)) echo $extraScripts; ?>
</body>
</html>
