<?php
/**
 * Checkout Page - NSBM Marketplace AI
 */
$isSubPage = true;
$pageTitle = 'Checkout';
require_once __DIR__ . '/../config/init.php';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="bi bi-credit-card me-2"></i>Checkout</h1>
        <nav class="breadcrumb-custom">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="cart.php">Cart</a></li>
                <li class="breadcrumb-item active">Checkout</li>
            </ol>
        </nav>
    </div>
</div>

<section class="section">
    <div class="container">
        <!-- Order Success (hidden initially) -->
        <div id="orderSuccess" class="glass-card text-center py-5" style="display:none;">
            <div style="font-size:5rem;margin-bottom:1rem;">🎉</div>
            <h2 class="text-gradient mb-3">Order Placed Successfully!</h2>
            <p class="text-muted-custom mb-4">Thank you for your purchase. Your order is being processed.</p>
            <p class="mb-4"><strong>Order ID:</strong> <span id="successOrderId"></span></p>
            <div class="d-flex gap-3 justify-content-center">
                <a href="orders.php" class="btn btn-primary-custom">View My Orders</a>
                <a href="products.php" class="btn btn-outline-custom">Continue Shopping</a>
            </div>
        </div>

        <!-- Checkout Form -->
        <div id="checkoutForm">
            <div class="row g-4">
                <!-- Shipping Details -->
                <div class="col-lg-7">
                    <div class="glass-card">
                        <h4 class="mb-4"><i class="bi bi-truck me-2"></i>Shipping Details</h4>
                        <form id="checkoutFormEl" onsubmit="placeOrder(event)">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label-custom">Full Name</label>
                                    <input type="text" name="name" class="form-control form-control-custom" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-custom">Phone Number</label>
                                    <input type="tel" name="phone" class="form-control form-control-custom" placeholder="+94 7X XXX XXXX" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label-custom">Shipping Address</label>
                                    <textarea name="address" class="form-control form-control-custom" rows="3" required placeholder="Enter your full address"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-custom">City</label>
                                    <input type="text" name="city" class="form-control form-control-custom" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-custom">Payment Method</label>
                                    <select name="payment_method" class="form-control form-control-custom">
                                        <option value="card">Credit/Debit Card (Simulated)</option>
                                        <option value="cash">Cash on Delivery</option>
                                        <option value="bank">Bank Transfer</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label-custom">Order Notes (Optional)</label>
                                    <textarea name="notes" class="form-control form-control-custom" rows="2" placeholder="Special instructions..."></textarea>
                                </div>
                            </div>

                            <!-- Payment Simulation -->
                            <div class="mt-4 p-3" style="background:rgba(108,99,255,0.1);border-radius:var(--radius-sm);border:1px dashed var(--primary);">
                                <p class="mb-0 small text-muted-custom">
                                    <i class="bi bi-info-circle me-1"></i>
                                    <strong>Note:</strong> This is a purchase simulation. No real payment will be processed. 
                                    Click "Place Order" to simulate a successful purchase.
                                </p>
                            </div>

                            <button type="submit" class="btn btn-primary-custom btn-lg w-100 mt-4" id="placeOrderBtn">
                                <i class="bi bi-bag-check me-2"></i>Place Order
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="col-lg-5">
                    <div class="cart-summary">
                        <h4 class="mb-4"><i class="bi bi-receipt me-2"></i>Order Summary</h4>
                        <div id="checkoutItems"></div>
                        <hr style="border-color:var(--border-glass);">
                        <div class="cart-summary-row">
                            <span class="text-muted-custom">Subtotal</span>
                            <span id="checkSubtotal">Rs. 0.00</span>
                        </div>
                        <div class="cart-summary-row">
                            <span class="text-muted-custom">Tax (5%)</span>
                            <span id="checkTax">Rs. 0.00</span>
                        </div>
                        <div class="cart-summary-row">
                            <span class="text-muted-custom">Shipping</span>
                            <span id="checkShipping">Rs. 0.00</span>
                        </div>
                        <div class="cart-summary-row cart-summary-total">
                            <span><strong>Total</strong></span>
                            <span class="text-gradient" id="checkTotal"><strong>Rs. 0.00</strong></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$extraScripts = '
<script>
function renderCheckout() {
    const items = Cart.items;
    if (items.length === 0) {
        window.location.href = "cart.php";
        return;
    }

    let html = "";
    items.forEach(item => {
        html += `
            <div class="d-flex justify-content-between align-items-center mb-2 pb-2" style="border-bottom:1px solid var(--border-glass);">
                <div>
                    <small class="d-block">${item.name}</small>
                    <small class="text-muted-custom">x${item.quantity}</small>
                </div>
                <small><strong>${formatPrice(item.price * item.quantity)}</strong></small>
            </div>
        `;
    });
    document.getElementById("checkoutItems").innerHTML = html;

    document.getElementById("checkSubtotal").textContent = formatPrice(Cart.getSubtotal());
    document.getElementById("checkTax").textContent = formatPrice(Cart.getTax());
    document.getElementById("checkShipping").textContent = Cart.getShipping() === 0 ? "FREE" : formatPrice(Cart.getShipping());
    document.getElementById("checkTotal").innerHTML = `<strong>${formatPrice(Cart.getTotal())}</strong>`;
}

async function placeOrder(e) {
    e.preventDefault();
    
    if (!Auth.isLoggedIn()) {
        Toast.show("Please login to place an order", "error");
        const modal = new bootstrap.Modal(document.getElementById("authModal"));
        modal.show();
        return;
    }

    const form = e.target;
    const btn = document.getElementById("placeOrderBtn");
    btn.disabled = true;
    btn.innerHTML = \'<span class="loading-spinner" style="width:16px;height:16px;border-width:2px;display:inline-block;"></span> Processing...\';

    const orderData = {
        items: Cart.items.map(item => ({ product_id: item.id, quantity: item.quantity })),
        shipping_address: form.address.value,
        shipping_city: form.city.value,
        shipping_phone: form.phone.value,
        payment_method: form.payment_method.value,
        notes: form.notes.value
    };

    try {
        const result = await API.post("orders.php", orderData);
        if (result.success) {
            Cart.clear();
            document.getElementById("checkoutForm").style.display = "none";
            document.getElementById("orderSuccess").style.display = "block";
            document.getElementById("successOrderId").textContent = "#" + result.order_id;
            Toast.show("Order placed successfully!", "success");
        } else {
            Toast.show(result.message || "Order failed", "error");
            btn.disabled = false;
            btn.innerHTML = \'<i class="bi bi-bag-check me-2"></i>Place Order\';
        }
    } catch (error) {
        Toast.show("Something went wrong", "error");
        btn.disabled = false;
        btn.innerHTML = \'<i class="bi bi-bag-check me-2"></i>Place Order\';
    }
}

document.addEventListener("DOMContentLoaded", renderCheckout);
</script>';
include __DIR__ . '/../includes/footer.php';
?>
