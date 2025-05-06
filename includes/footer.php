    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>About Nox</h3>
                    <p>Elevating urban fashion through dark aesthetics and premium quality. Join us in embracing the darkness.</p>
                    <div class="social-links">
                        <a href="<?php echo SOCIAL_MEDIA['instagram']; ?>" target="_blank">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="<?php echo SOCIAL_MEDIA['facebook']; ?>" target="_blank">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="<?php echo SOCIAL_MEDIA['twitter']; ?>" target="_blank">
                            <i class="fab fa-twitter"></i>
                        </a>
                    </div>
                </div>

                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="shop.php">Shop</a></li>
                        <li><a href="collections.php">Collections</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li><a href="authenticity.php">Authenticity</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h3>Customer Service</h3>
                    <ul>
                        <li><a href="shipping.php">Shipping & Returns</a></li>
                        <li><a href="size-guide.php">Size Guide</a></li>
                        <li><a href="faq.php">FAQ</a></li>
                        <li><a href="privacy.php">Privacy Policy</a></li>
                        <li><a href="terms.php">Terms & Conditions</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h3>Newsletter</h3>
                    <p>Subscribe to receive updates, access to exclusive deals, and more.</p>
                    <form id="newsletter-form" class="newsletter-form" action="process/newsletter.php" method="post">
                        <div class="form-group">
                            <input type="email" name="email" placeholder="Enter your email" required>
                            <button type="submit" class="btn btn-primary">Subscribe</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo COMPANY_INFO['name']; ?>. All rights reserved.</p>
                <div class="payment-methods">
                    <i class="fab fa-cc-visa"></i>
                    <i class="fab fa-cc-mastercard"></i>
                    <i class="fab fa-cc-amex"></i>
                    <i class="fab fa-cc-paypal"></i>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="back-to-top" class="back-to-top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Scripts -->
    <script>
        // Back to Top Button
        const backToTop = document.getElementById('back-to-top');
        
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 100) {
                backToTop.classList.add('show');
            } else {
                backToTop.classList.remove('show');
            }
        });
        
        backToTop.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Newsletter Form
        const newsletterForm = document.getElementById('newsletter-form');
        if (newsletterForm) {
            newsletterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                
                fetch(this.action, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Thank you for subscribing!', 'success');
                        this.reset();
                    } else {
                        showNotification(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Something went wrong. Please try again.', 'error');
                });
            });
        }
    </script>
</body>
</html> 