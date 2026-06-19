/**
 * NSBM Marketplace AI - Main Application JavaScript
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
            const response = await fetch(`${APP.apiBase}/api/${endpoint}`);
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
                method: 'DELETE'
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

    async init() {
        const result = await API.get('auth.php?action=check');
        if (result.success && result.authenticated) {
            this.user = result.user;
            this.updateUI();
        }
    },

    async login(email, password) {
        const result = await API.post('auth.php?action=login', { email, password });
        if (result.success) {
            this.user = result.user;
            this.updateUI();
            Toast.show('Welcome back!', 'success');
        }
        return result;
    },

    async register(data) {
        const result = await API.post('auth.php?action=register', data);
        if (result.success) {
            this.user = result.user;
            this.updateUI();
            Toast.show('Account created successfully!', 'success');
        }
        return result;
    },

    async logout() {
        await API.post('auth.php?action=logout', {});
        this.user = null;
        this.updateUI();
        Toast.show('Logged out successfully', 'info');
        setTimeout(() => window.location.href = '/', 1000);
    },

    updateUI() {
        const authLinks = document.querySelectorAll('.auth-links');
        const userLinks = document.querySelectorAll('.user-links');
        const userName = document.querySelectorAll('.user-name');

        if (this.user) {
            authLinks.forEach(el => el.style.display = 'none');
            userLinks.forEach(el => el.style.display = 'block');
            userName.forEach(el => el.textContent = this.user.name);
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
                    <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg, rgba(108,99,255,0.1), rgba(0,212,170,0.05));">
                        <i class="bi bi-box-seam" style="font-size:3rem;color:var(--primary-light);opacity:0.5;"></i>
                    </div>
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
                    <button class="btn btn-primary-custom btn-sm" onclick='Cart.add(${JSON.stringify({id: product.id, name: product.name, price: parseFloat(price), sale_price: product.sale_price, image: product.image, stock_quantity: product.stock_quantity})})'>
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
