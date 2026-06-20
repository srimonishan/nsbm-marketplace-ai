<?php
/**
 * Contact Page - GreenLink Market
 */
$isSubPage = true;
$pageTitle = 'Contact Us';
require_once __DIR__ . '/../config/init.php';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="bi bi-envelope me-2"></i>Contact Us</h1>
        <nav class="breadcrumb-custom">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item active">Contact</li>
            </ol>
        </nav>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="row g-5">
            <!-- Contact Form -->
            <div class="col-lg-7" data-animate>
                <div class="glass-card">
                    <h3 class="mb-4">Send us a Message</h3>
                    <form id="contactForm" onsubmit="handleContact(event)">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label-custom">Your Name</label>
                                <input type="text" name="name" class="form-control form-control-custom" required placeholder="John Doe">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">Email Address</label>
                                <input type="email" name="email" class="form-control form-control-custom" required placeholder="you@nsbm.ac.lk">
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom">Subject</label>
                                <input type="text" name="subject" class="form-control form-control-custom" required placeholder="How can we help?">
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom">Message</label>
                                <textarea name="message" class="form-control form-control-custom" rows="5" required placeholder="Tell us more..."></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary-custom btn-lg" id="contactBtn">
                                    <i class="bi bi-send me-2"></i>Send Message
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Contact Info -->
            <div class="col-lg-5" data-animate>
                <div class="glass-card contact-info-card mb-4">
                    <div class="contact-info-heading">
                        <span class="contact-info-eyebrow">Contact information</span>
                        <h4>Get in Touch</h4>
                        <p>Questions about GreenLink Market? Our campus support team is ready to help.</p>
                    </div>
                    <div class="contact-info-list">
                    <div class="contact-info-item">
                        <div class="contact-info-icon">
                            <i class="bi bi-geo-alt"></i>
                        </div>
                        <div class="contact-info-content">
                            <h6>Address</h6>
                            <p>Mahenwaththa, Pitipana, Homagama, Sri Lanka</p>
                        </div>
                    </div>
                    <div class="contact-info-item">
                        <div class="contact-info-icon">
                            <i class="bi bi-telephone"></i>
                        </div>
                        <div class="contact-info-content">
                            <h6>Phone</h6>
                            <a href="tel:+94115445000">+94 11 544 5000</a>
                        </div>
                    </div>
                    <div class="contact-info-item">
                        <div class="contact-info-icon">
                            <i class="bi bi-envelope"></i>
                        </div>
                        <div class="contact-info-content">
                            <h6>Email</h6>
                            <a href="mailto:marketplace@nsbm.ac.lk">marketplace@nsbm.ac.lk</a>
                        </div>
                    </div>
                    <div class="contact-info-item">
                        <div class="contact-info-icon">
                            <i class="bi bi-clock"></i>
                        </div>
                        <div class="contact-info-content">
                            <h6>Opening Hours</h6>
                            <p>Mon–Fri: 8:00 AM–6:00 PM<br>Saturday: 9:00 AM–2:00 PM</p>
                        </div>
                    </div>
                    </div>
                </div>

                <!-- Map placeholder -->
                <div class="glass-card text-center p-4">
                    <i class="bi bi-map" style="font-size:3rem;color:var(--primary);"></i>
                    <p class="text-muted-custom mt-2 mb-0">NSBM Green University Town<br>Pitipana, Homagama</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$extraScripts = '
<script>
async function handleContact(e) {
    e.preventDefault();
    const form = e.target;
    const btn = document.getElementById("contactBtn");
    btn.disabled = true;
    btn.innerHTML = \'<span class="loading-spinner" style="width:16px;height:16px;border-width:2px;display:inline-block;"></span> Sending...\';

    const data = {
        name: form.name.value,
        email: form.email.value,
        subject: form.subject.value,
        message: form.message.value
    };

    const result = await API.post("contact.php", data);
    if (result.success) {
        Toast.show("Message sent successfully!", "success");
        form.reset();
    } else {
        Toast.show(result.message || "Failed to send message", "error");
    }

    btn.disabled = false;
    btn.innerHTML = \'<i class="bi bi-send me-2"></i>Send Message\';
}
</script>';
include __DIR__ . '/../includes/footer.php';
?>
