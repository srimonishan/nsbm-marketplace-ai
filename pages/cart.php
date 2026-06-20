<?php
/**
 * Shopping Cart Page - GreenLink Market
 */
$isSubPage = true;
$pageTitle = 'Shopping Cart';
require_once __DIR__ . '/../config/init.php';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="bi bi-cart3 me-2"></i>Shopping Cart</h1>
        <nav class="breadcrumb-custom">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item active">Cart</li>
            </ol>
        </nav>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="row g-4">
            <!-- Cart Items -->
            <div class="col-lg-8">
                <div id="cartItems">
                    <!-- Populated by JS -->
                </div>
                <div id="emptyCart" class="glass-card text-center py-5" style="display:none;">
                    <i class="bi bi-cart-x" style="font-size:4rem;color:var(--text-muted);"></i>
                    <h3 class="mt-3">Your Cart is Empty</h3>
                    <p class="text-muted-custom">Looks like you haven't added any items yet</p>
                    <a href="products.php" class="btn btn-primary-custom">
                        <i class="bi bi-bag me-2"></i>Start Shopping
                    </a>
                </div>
            </div>

            <!-- Cart Summary -->
            <div class="col-lg-4">
                <div class="cart-summary" id="cartSummary" style="display:none;">
                    <h4 class="mb-4"><i class="bi bi-receipt me-2"></i>Order Summary</h4>
                    
                    <div class="cart-summary-row">
                        <span class="text-muted-custom">Subtotal</span>
                        <span id="cartSubtotal">Rs. 0.00</span>
                    </div>
                    <div class="cart-summary-row">
                        <span class="text-muted-custom">Tax (5%)</span>
                        <span id="cartTax">Rs. 0.00</span>
                    </div>
                    <div class="cart-summary-row">
                        <span class="text-muted-custom">Shipping</span>
                        <span id="cartShipping">Rs. 0.00</span>
                    </div>
                    <div class="cart-summary-row cart-summary-total">
                        <span><strong>Total</strong></span>
                        <span class="text-gradient" id="cartTotal"><strong>Rs. 0.00</strong></span>
                    </div>

                    <div class="mt-3 mb-3 p-2 text-center" style="background:rgba(182, 227, 74,0.1);border-radius:var(--radius-sm);">
                        <small class="text-muted-custom">
                            <i class="bi bi-truck me-1"></i>Free shipping on orders over Rs. 10,000
                        </small>
                    </div>

                    <a href="checkout.php" class="btn btn-primary-custom w-100 mb-2">
                        <i class="bi bi-credit-card me-2"></i>Proceed to Checkout
                    </a>
                    <button class="btn btn-outline-custom w-100" onclick="clearCart()">
                        <i class="bi bi-trash me-2"></i>Clear Cart
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$extraScripts = '
<script>
function renderCart() {
    const items = Cart.items;
    const cartDiv = document.getElementById("cartItems");
    const emptyDiv = document.getElementById("emptyCart");
    const summaryDiv = document.getElementById("cartSummary");

    if (items.length === 0) {
        cartDiv.innerHTML = "";
        emptyDiv.style.display = "block";
        summaryDiv.style.display = "none";
        return;
    }

    emptyDiv.style.display = "none";
    summaryDiv.style.display = "block";

    let html = "";
    items.forEach(item => {
        html += `
            <div class="cart-item">
                <div class="cart-item-image">
                    ${item.image
                        ? `<img src="${item.image}" alt="${item.name}">`
                        : "<div class=\"product-image-placeholder\"><i class=\"bi bi-box-seam\"></i></div>"}
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1">${item.name}</h6>
                    <p class="text-muted-custom small mb-1">${formatPrice(item.price)} each</p>
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-glass btn-sm" onclick="updateCartItem(${item.id}, ${item.quantity - 1})">-</button>
                        <span class="px-2">${item.quantity}</span>
                        <button class="btn btn-glass btn-sm" onclick="updateCartItem(${item.id}, ${item.quantity + 1})">+</button>
                    </div>
                </div>
                <div class="text-end">
                    <strong class="text-gradient">${formatPrice(item.price * item.quantity)}</strong>
                    <br>
                    <button class="btn btn-sm text-danger mt-1" onclick="removeCartItem(${item.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
    });
    cartDiv.innerHTML = html;

    // Update summary
    document.getElementById("cartSubtotal").textContent = formatPrice(Cart.getSubtotal());
    document.getElementById("cartTax").textContent = formatPrice(Cart.getTax());
    document.getElementById("cartShipping").textContent = Cart.getShipping() === 0 ? "FREE" : formatPrice(Cart.getShipping());
    document.getElementById("cartTotal").innerHTML = `<strong>${formatPrice(Cart.getTotal())}</strong>`;
}

function updateCartItem(id, qty) {
    if (qty < 1) {
        removeCartItem(id);
        return;
    }
    Cart.updateQuantity(id, qty);
    renderCart();
}

function removeCartItem(id) {
    Cart.remove(id);
    renderCart();
    Toast.show("Item removed from cart", "info");
}

function clearCart() {
    if (confirm("Are you sure you want to clear your cart?")) {
        Cart.clear();
        renderCart();
        Toast.show("Cart cleared", "info");
    }
}

// Initial render
document.addEventListener("DOMContentLoaded", renderCart);
</script>';
include __DIR__ . '/../includes/footer.php';
?>
