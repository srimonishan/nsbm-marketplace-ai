/**
 * GreenLink Market - Main Application JavaScript
 * Vanilla JS - No frameworks
 */

'use strict';

// ============================================
// APP CONFIGURATION
// ============================================
const APP = {
    apiBase: '',
    currency: 'Rs.',
    taxRate: 0.05,
    shippingFee: 350,
    freeShippingThreshold: 10000
};

// ============================================
// CART MANAGEMENT
// ============================================
const Cart = {
    items: [],

    init() {
        this.items = JSON.parse(localStorage.getItem('nsbm_cart') || '[]');
        this.updateBadge();
    },

    save() {
        localStorage.setItem('nsbm_cart', JSON.stringify(this.items));
        this.updateBadge();
    },

    add(product) {
        const existing = this.items.find(item => item.id === product.id);
        if (existing) {
            existing.quantity = Math.min(existing.quantity + 1, product.stock || 99);
        } else {
            this.items.push({
                id: product.id,
                name: product.name,
                price: product.sale_price || product.price,
                original_price: product.price,
                image: product.image,
                quantity: 1,
                stock: product.stock_quantity || 99
            });
        }
        this.save();
        Toast.show('Added to cart!', 'success');
    },

    remove(productId) {
        this.items = this.items.filter(item => item.id !== productId);
        this.save();
    },

    updateQuantity(productId, quantity) {
        const item = this.items.find(item => item.id === productId);
        if (item) {
            item.quantity = Math.max(1, Math.min(quantity, item.stock));
            this.save();
        }
    },

    clear() {
        this.items = [];
        this.save();
    },

    getSubtotal() {
        return this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    },

    getTax() {
        return this.getSubtotal() * APP.taxRate;
    },

    getShipping() {
        return this.getSubtotal() >= APP.freeShippingThreshold ? 0 : APP.shippingFee;
    },

    getTotal() {
        return this.getSubtotal() + this.getTax() + this.getShipping();
    },

    getCount() {
        return this.items.reduce((sum, item) => sum + item.quantity, 0);
    },

    updateBadge() {
        const badges = document.querySelectorAll('.cart-count');
        const count = this.getCount();
        badges.forEach(badge => {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'flex' : 'none';
        });
    }
};

// ============================================
// TOAST NOTIFICATIONS
// ============================================
const Toast = {
    container: null,

    init() {
        this.container = document.createElement('div');
        this.container.className = 'toast-container';
        document.body.appendChild(this.container);
    },

    show(message, type = 'info', duration = 3000) {
        if (!this.container) this.init();

        const icons = {
            success: '<i class="bi bi-check-circle-fill"></i>',
            error: '<i class="bi bi-x-circle-fill"></i>',
            info: '<i class="bi bi-info-circle-fill"></i>'
        };

        const toast = document.createElement('div');
        toast.className = `toast-custom ${type}`;
        toast.innerHTML = `${icons[type] || icons.info} <span>${message}</span>`;
        this.container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }
};

// ============================================
// API HELPER
// ============================================
const API = {
    async get(endpoint) {
        try {
            const response = await fetch(`${APP.apiBase}/api/${endpoint}`, {
                credentials: 'same-origin'
            });
            return await response.json();
        } catch (error) {
            console.error('API Error:', error);
            return { success: false, message: 'Network error' };
        }
    },

    async post(endpoint, data) {
        try {
            const response = await fetch(`${APP.apiBase}/api/${endpoint}`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            return await response.json();
        } catch (error) {
            console.error('API Error:', error);
            return { success: false, message: 'Network error' };
        }
    },

    async put(endpoint, data) {
        try {
            const response = await fetch(`${APP.apiBase}/api/${endpoint}`, {
                method: 'PUT',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            return await response.json();
        } catch (error) {
            console.error('API Error:', error);
            return { success: false, message: 'Network error' };
        }
    },

    async delete(endpoint) {
        try {
            const response = await fetch(`${APP.apiBase}/api/${endpoint}`, {
                method: 'DELETE',
                credentials: 'same-origin'
            });
            return await response.json();
        } catch (error) {
            console.error('API Error:', error);
            return { success: false, message: 'Network error' };
        }
    }
};

// ============================================
// AUTH MANAGEMENT
// ============================================
const Auth = {
    user: null,
    ready: null,

    init() {
        if (!this.ready) {
            this.ready = (async () => {
                const result = await API.get('auth.php?action=check');
                this.user = result.success && result.authenticated ? result.user : null;

                if (this.user?.role === 'admin' && !window.location.pathname.includes('/admin/')) {
                    const adminPath = window.location.pathname.includes('/pages/')
                        ? '../admin/index.php'
                        : 'admin/index.php';
                    window.location.replace(adminPath);
                    return this.user;
                }

                this.updateUI();
                return this.user;
            })();
        }
        return this.ready;
    },

    async login(email, password) {
        const result = await API.post('auth.php?action=login', { email, password });
        if (result.success) {
            this.user = result.user;
            this.updateUI();
            Toast.show('Welcome back!', 'success');
            window.dispatchEvent(new CustomEvent('auth:changed', { detail: { user: this.user } }));
        }
        return result;
    },

    async register(data) {
        const result = await API.post('auth.php?action=register', data);
        if (result.success) {
            this.user = result.user;
            this.updateUI();
            Toast.show('Account created successfully!', 'success');
            window.dispatchEvent(new CustomEvent('auth:changed', { detail: { user: this.user } }));
        }
        return result;
    },

    async logout() {
        await API.post('auth.php?action=logout', {});
        this.user = null;
        this.updateUI();
        window.dispatchEvent(new CustomEvent('auth:changed', { detail: { user: null } }));
        Toast.show('Logged out successfully', 'info');
        setTimeout(() => window.location.href = '/', 1000);
    },

    updateUI() {
        const authLinks = document.querySelectorAll('.auth-links');
        const userLinks = document.querySelectorAll('.user-links');
        const userName = document.querySelectorAll('.user-name');
        const customerNotificationLinks = document.querySelectorAll('.customer-notification-links');
        const customerUserLinks = document.querySelectorAll('.customer-user-links');

        if (this.user) {
            authLinks.forEach(el => el.style.display = 'none');
            userLinks.forEach(el => el.style.display = 'block');
            userName.forEach(el => el.textContent = this.user.name);
            customerNotificationLinks.forEach(el => {
                el.style.display = this.isAdmin() ? 'none' : 'block';
            });
            customerUserLinks.forEach(el => {
                el.style.display = this.isAdmin() ? 'none' : 'block';
            });
        } else {
            authLinks.forEach(el => el.style.display = 'block');
            userLinks.forEach(el => el.style.display = 'none');
        }
    },

    isLoggedIn() {
        return this.user !== null;
    },

    isAdmin() {
        return this.user && this.user.role === 'admin';
    }
};

// ============================================
// CUSTOMER NOTIFICATIONS
// ============================================
function escapeAppHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

const Notifications = {
    timer: null,

    async init() {
        await Auth.init();
        await this.load();
        if (!this.timer) {
            this.timer = window.setInterval(() => this.load(), 30000);
        }
    },

    async load() {
        if (!Auth.isLoggedIn() || Auth.isAdmin()) {
            this.clear();
            return;
        }

        const result = await API.get('notifications.php');
        if (result.success) {
            this.render(result.data || [], Number(result.unread_count || 0));
        }
    },

    render(items, unreadCount) {
        const list = document.getElementById('notificationList');
        const badge = document.getElementById('notificationBadge');
        if (!list || !badge) return;

        badge.textContent = unreadCount > 99 ? '99+' : String(unreadCount);
        badge.style.display = unreadCount > 0 ? 'block' : 'none';

        if (!items.length) {
            list.innerHTML = '<div class="notification-empty">No notifications yet</div>';
            return;
        }

        list.innerHTML = items.map(item => `
            <button type="button" class="notification-item ${Number(item.is_read) ? '' : 'unread'}" onclick="Notifications.open(${Number(item.id)})">
                <strong>${escapeAppHtml(item.title)}</strong>
                <span>${escapeAppHtml(item.message)}</span>
                <small>${formatDate(item.created_at)}</small>
            </button>
        `).join('');
    },

    async open(id) {
        await API.post('notifications.php', { id });
        const ordersPath = window.location.pathname.includes('/pages/') ? 'orders.php' : 'pages/orders.php';
        window.location.href = ordersPath;
    },

    async markAllRead() {
        if (!Auth.isLoggedIn()) return;
        await API.post('notifications.php', { all: true });
        await this.load();
    },

    clear() {
        const list = document.getElementById('notificationList');
        const badge = document.getElementById('notificationBadge');
        if (list) list.innerHTML = '<div class="notification-empty">No notifications yet</div>';
        if (badge) badge.style.display = 'none';
    }
};

window.addEventListener('auth:changed', () => Notifications.load());

// ============================================
// SCROLL ANIMATIONS
// ============================================
const ScrollAnimations = {
    init() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

        document.querySelectorAll('[data-animate]').forEach(el => {
            observer.observe(el);
        });
    }
};

// ============================================
// NAVBAR SCROLL EFFECT
// ============================================
const NavbarScroll = {
    init() {
        const navbar = document.querySelector('.navbar-custom');
        if (!navbar) return;

        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }
};

// ============================================
// COUNTER ANIMATION
// ============================================
function animateCounters() {
    const counters = document.querySelectorAll('[data-count]');
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-count'));
        const duration = 2000;
        const step = target / (duration / 16);
        let current = 0;

        const timer = setInterval(() => {
            current += step;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            counter.textContent = Math.floor(current).toLocaleString();
        }, 16);
    });
}

// ============================================
// FORMAT HELPERS
// ============================================
function formatPrice(amount) {
    return `${APP.currency} ${parseFloat(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function renderStars(rating) {
    let stars = '';
    for (let i = 1; i <= 5; i++) {
        if (i <= Math.floor(rating)) {
            stars += '<i class="bi bi-star-fill star"></i>';
        } else if (i - 0.5 <= rating) {
            stars += '<i class="bi bi-star-half star"></i>';
        } else {
            stars += '<i class="bi bi-star star"></i>';
        }
    }
    return stars;
}

// ============================================
// PRODUCT CARD RENDERER
// ============================================
function renderProductCard(product) {
    const price = product.sale_price || product.price;
    const hasDiscount = product.sale_price && product.sale_price < product.price;
    const discount = hasDiscount ? Math.round((1 - product.sale_price / product.price) * 100) : 0;

    return `
        <div class="col-lg-3 col-md-4 col-sm-6 mb-4" data-animate>
            <div class="product-card">
                <div class="product-card-image">
                    ${product.image_url
                        ? `<img src="${product.image_url}" alt="${product.name}" loading="lazy">`
                        : '<div class="product-image-placeholder"><i class="bi bi-box-seam"></i></div>'}
                    ${hasDiscount ? `<span class="product-badge product-badge-sale">-${discount}%</span>` : ''}
                    ${product.is_featured ? `<span class="product-badge product-badge-featured">Featured</span>` : ''}
                </div>
                <div class="product-card-body">
                    <span class="product-category">${product.category_name || 'General'}</span>
                    <h5 class="product-title">${product.name}</h5>
                    <div class="product-rating">
                        ${renderStars(product.rating || 0)}
                        <span class="count">(${product.total_reviews || 0})</span>
                    </div>
                    <div class="product-price">
                        <span class="current">${formatPrice(price)}</span>
                        ${hasDiscount ? `<span class="original">${formatPrice(product.price)}</span>` : ''}
                    </div>
                </div>
                <div class="product-card-actions">
                    <a href="pages/product.php?id=${product.id}" class="btn btn-outline-custom btn-sm">View</a>
                    <button class="btn btn-primary-custom btn-sm" onclick='Cart.add(${JSON.stringify({id: product.id, name: product.name, price: parseFloat(price), sale_price: product.sale_price, image: product.image_url || product.image, stock_quantity: product.stock_quantity})})'>
                        <i class="bi bi-cart-plus"></i> Add
                    </button>
                </div>
            </div>
        </div>
    `;
}

// ============================================
// INITIALIZATION
// ============================================
document.addEventListener('DOMContentLoaded', () => {
    Cart.init();
    Toast.init();
    Auth.init();
    Notifications.init();
    NavbarScroll.init();
    ScrollAnimations.init();

    // Counter animation on scroll
    const statsSection = document.querySelector('.hero-stats');
    if (statsSection) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                    observer.unobserve(entry.target);
                }
            });
        });
        observer.observe(statsSection);
    }
});
