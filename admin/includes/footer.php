            </div><!-- /admin-content -->
        </div><!-- /admin-main -->
    </div><!-- /admin-wrapper -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('adminSidebar').classList.toggle('active');
        }

        async function adminLogout() {
            await fetch('../api/auth.php?action=logout', { method: 'POST' });
            window.location.href = '../index.php';
        }

        // Toast for admin
        function showToast(message, type = 'success') {
            const container = document.querySelector('.toast-container') || (() => {
                const div = document.createElement('div');
                div.className = 'toast-container';
                div.style.cssText = 'position:fixed;top:80px;right:20px;z-index:9999;';
                document.body.appendChild(div);
                return div;
            })();
            
            const toast = document.createElement('div');
            toast.className = `toast-custom ${type}`;
            toast.innerHTML = `<i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'x-circle-fill'}"></i> <span>${message}</span>`;
            container.appendChild(toast);
            setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 300); }, 3000);
        }
    </script>
</body>
</html>
