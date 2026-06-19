<?php
/**
 * About Page - NSBM Marketplace AI
 */
$isSubPage = true;
$pageTitle = 'About Us';
require_once __DIR__ . '/../config/init.php';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="bi bi-info-circle me-2"></i>About Us</h1>
        <nav class="breadcrumb-custom">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item active">About</li>
            </ol>
        </nav>
    </div>
</div>

<section class="section">
    <div class="container">
        <!-- About Content -->
        <div class="row g-5 align-items-center mb-5">
            <div class="col-lg-6" data-animate>
                <span class="badge-glass mb-3"><i class="bi bi-mortarboard me-1"></i> NSBM Green University</span>
                <h2 class="section-title">The Future of <span class="text-gradient">Campus Shopping</span></h2>
                <p class="text-muted-custom" style="line-height:1.8;">
                    NSBM Marketplace AI is the premier AI-powered e-commerce platform designed exclusively for NSBM Green University students and staff. 
                    We combine cutting-edge artificial intelligence with a curated selection of products to deliver a shopping experience that's smart, 
                    personalized, and perfectly tailored to campus life.
                </p>
                <p class="text-muted-custom" style="line-height:1.8;">
                    Founded by students, for students — our platform leverages Google's Gemini AI to provide intelligent product recommendations, 
                    a conversational shopping assistant, and dynamic content that evolves with your needs.
                </p>
                <div class="row g-3 mt-3">
                    <div class="col-6">
                        <div class="glass-card text-center p-3">
                            <h3 class="text-gradient mb-0" data-count="500">500+</h3>
                            <small class="text-muted-custom">Products</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="glass-card text-center p-3">
                            <h3 class="text-gradient mb-0" data-count="2000">2000+</h3>
                            <small class="text-muted-custom">Happy Students</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6" data-animate>
                <div class="glass-card p-5 text-center">
                    <div style="font-size:5rem;margin-bottom:1rem;">🎓</div>
                    <h3 class="text-gradient">NSBM Green University</h3>
                    <p class="text-muted-custom">Sri Lanka's Premier Non-State University</p>
                    <p class="text-muted-custom small">Mahenwaththa, Pitipana, Homagama</p>
                </div>
            </div>
        </div>

        <!-- Features -->
        <div class="row g-4 mt-5">
            <div class="col-12 text-center mb-4" data-animate>
                <h2 class="section-title">Why Choose <span class="text-gradient">Us</span></h2>
            </div>
            <div class="col-lg-4 col-md-6" data-animate>
                <div class="glass-card text-center h-100">
                    <div class="category-icon mx-auto mb-3" style="background:linear-gradient(135deg, #6c63ff, #8b83ff);">
                        <i class="bi bi-robot"></i>
                    </div>
                    <h5>AI-Powered</h5>
                    <p class="text-muted-custom small">Gemini AI integration for smart recommendations, gift suggestions, and personalized shopping.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-animate>
                <div class="glass-card text-center h-100">
                    <div class="category-icon mx-auto mb-3" style="background:linear-gradient(135deg, #00d4aa, #33e0be);">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h5>Secure & Safe</h5>
                    <p class="text-muted-custom small">Enterprise-grade security with encrypted transactions and secure authentication.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-animate>
                <div class="glass-card text-center h-100">
                    <div class="category-icon mx-auto mb-3" style="background:linear-gradient(135deg, #ff6b9d, #ff8fb5);">
                        <i class="bi bi-people"></i>
                    </div>
                    <h5>Community First</h5>
                    <p class="text-muted-custom small">Built by NSBM students, for NSBM students. Supporting campus entrepreneurs.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-animate>
                <div class="glass-card text-center h-100">
                    <div class="category-icon mx-auto mb-3" style="background:linear-gradient(135deg, #ffc107, #ffcd38);">
                        <i class="bi bi-truck"></i>
                    </div>
                    <h5>Campus Delivery</h5>
                    <p class="text-muted-custom small">Fast delivery right to your dorm or campus location. Free shipping over Rs. 10,000.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-animate>
                <div class="glass-card text-center h-100">
                    <div class="category-icon mx-auto mb-3" style="background:linear-gradient(135deg, #17a2b8, #3dc5d8);">
                        <i class="bi bi-star"></i>
                    </div>
                    <h5>Quality Products</h5>
                    <p class="text-muted-custom small">Curated selection of premium products verified for quality and student-friendly pricing.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-animate>
                <div class="glass-card text-center h-100">
                    <div class="category-icon mx-auto mb-3" style="background:linear-gradient(135deg, #6610f2, #8540f5);">
                        <i class="bi bi-headset"></i>
                    </div>
                    <h5>24/7 AI Support</h5>
                    <p class="text-muted-custom small">Our AI assistant is always available to help you find what you need.</p>
                </div>
            </div>
        </div>

        <!-- Team Section -->
        <div class="text-center mt-5 pt-5" data-animate>
            <h2 class="section-title mb-4">Our <span class="text-gradient">Technology</span></h2>
            <div class="row g-3 justify-content-center">
                <div class="col-auto"><span class="badge-glass"><i class="bi bi-filetype-php me-1"></i>PHP 8+</span></div>
                <div class="col-auto"><span class="badge-glass"><i class="bi bi-database me-1"></i>MySQL</span></div>
                <div class="col-auto"><span class="badge-glass"><i class="bi bi-bootstrap me-1"></i>Bootstrap 5</span></div>
                <div class="col-auto"><span class="badge-glass"><i class="bi bi-filetype-js me-1"></i>Vanilla JS</span></div>
                <div class="col-auto"><span class="badge-glass"><i class="bi bi-stars me-1"></i>Gemini AI</span></div>
                <div class="col-auto"><span class="badge-glass"><i class="bi bi-shield-lock me-1"></i>PDO Security</span></div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
