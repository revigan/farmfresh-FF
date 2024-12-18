<footer class="footer mt-5">
    <!-- Main Footer -->
    <div class="footer-main">
        <div class="container py-5">
            <div class="row g-4 align-items-center">
                <!-- Brand Section -->
                <div class="col-lg-4">
                    <div class="footer-brand d-flex align-items-center mb-3">
                        <div class="brand-icon">
                            <i class='bx bx-leaf'></i>
                        </div>
                        <h4 class="ms-3 mb-0 fw-bold">FarmFresh</h4>
                    </div>
                    <p class="text-muted mb-4">
                        Belanja sayur dan buah segar langsung dari petani lokal untuk kebutuhan sehari-hari Anda.
                    </p>
                    <div class="footer-social d-flex gap-3">
                        <a href="#" class="social-link whatsapp">
                            <i class='bx bxl-whatsapp'></i>
                        </a>
                        <a href="#" class="social-link instagram">
                            <i class='bx bxl-instagram'></i>
                        </a>
                        <a href="#" class="social-link facebook">
                            <i class='bx bxl-facebook'></i>
                        </a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="col-lg-4">
                    <h5 class="footer-title mb-4">Quick Links</h5>
                    <div class="d-flex flex-column gap-3 footer-links">
                        <a href="products.php" class="footer-link">
                            <i class='bx bx-store-alt me-2'></i>Produk
                        </a>
                        <a href="about.php" class="footer-link">
                            <i class='bx bx-info-circle me-2'></i>Tentang
                        </a>
                        <a href="contact.php" class="footer-link">
                            <i class='bx bx-envelope me-2'></i>Kontak
                        </a>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="col-lg-4">
                    <h5 class="footer-title mb-4">Hubungi Kami</h5>
                    <div class="footer-contact">
                        <div class="d-flex align-items-center mb-3">
                            <i class='bx bx-map contact-icon'></i>
                            <div class="ms-3">
                                <p class="mb-0">Jl. Contoh No. 123</p>
                                <p class="mb-0">Kota, Provinsi 12345</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <i class='bx bx-phone contact-icon'></i>
                            <div class="ms-3">
                                <p class="mb-0">+62 123 4567 890</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class='bx bx-envelope contact-icon'></i>
                            <div class="ms-3">
                                <p class="mb-0">info@farmfresh.com</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Copyright Section -->
    <div class="footer-bottom py-3">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <p class="mb-0 copyright-text">
                        &copy; <?= date('Y') ?> FarmFresh. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
/* Footer Styles */
.footer {
    background: linear-gradient(to bottom, #ffffff, #f8f9fa);
    position: relative;
}

.footer-main {
    position: relative;
    z-index: 1;
}

/* Brand Styles */
.brand-icon {
    width: 45px;
    height: 45px;
    background: var(--primary-color);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.brand-icon i {
    font-size: 28px;
    color: white;
}

/* Social Links */
.social-link {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    font-size: 20px;
    transition: all 0.3s ease;
}

.social-link.whatsapp {
    background: rgba(37, 211, 102, 0.1);
    color: #25d366;
}

.social-link.instagram {
    background: rgba(225, 48, 108, 0.1);
    color: #e1306c;
}

.social-link.facebook {
    background: rgba(66, 103, 178, 0.1);
    color: #4267B2;
}

.social-link:hover {
    transform: translateY(-5px);
}

/* Footer Links */
.footer-title {
    color: var(--primary-color);
    font-weight: 600;
    position: relative;
    padding-bottom: 10px;
}

.footer-title:after {
    content: '';
    position: absolute;
    left: 0;
    bottom: 0;
    width: 50px;
    height: 2px;
    background: var(--primary-color);
}

.footer-link {
    color: #6c757d;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
}

.footer-link:hover {
    color: var(--primary-color);
    transform: translateX(5px);
}

/* Contact Icons */
.contact-icon {
    width: 40px;
    height: 40px;
    background: rgba(46, 204, 113, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: var(--primary-color);
}

/* Copyright Section */
.footer-bottom {
    background: #f8f9fa;
    border-top: 1px solid rgba(0,0,0,0.05);
}

.copyright-text {
    color: #6c757d;
    font-size: 0.9rem;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .footer-title {
        margin-top: 1.5rem;
    }
    
    .footer-social {
        justify-content: center;
    }
    
    .footer-links {
        align-items: center;
    }
}
</style>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
