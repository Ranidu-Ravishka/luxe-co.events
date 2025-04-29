    </main>
    <!-- Footer -->
    <footer class="footer bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <!-- Contact Details -->
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5 class="text-pink mb-4">Contact Us</h5>
                    <p><i class="fas fa-map-marker-alt me-2"></i> 123 Wedding Lane, Dream City</p>
                    <p><i class="fas fa-phone me-2"></i> +1 (555) 123-4567</p>
                    <p><i class="fas fa-envelope me-2"></i> info@luxeandco.com</p>
                    <!-- Social Media Icons -->
                    <div class="social-icons mt-4">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-pinterest"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5 class="text-pink mb-4">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="/WeddinPlaning/pages/index.php" class="text-white text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="/WeddinPlaning/pages/index.php#services" class="text-white text-decoration-none">Services</a></li>
                        <li class="mb-2"><a href="/WeddinPlaning/pages/index.php#gallery" class="text-white text-decoration-none">Gallery</a></li>
                        <li><a href="/WeddinPlaning/pages/index.php#testimonials" class="text-white text-decoration-none">Testimonials</a></li>
                    </ul>
                </div>
                
                <!-- Newsletter -->
                <div class="col-md-4">
                    <h5 class="text-pink mb-4">Stay Updated</h5>
                    <p>Subscribe to our newsletter for wedding tips and inspiration.</p>
                    <form id="newsletterForm" onsubmit="return handleNewsletterForm(event)">
                        <div class="newsletter-form-container">
                            <input type="email" class="newsletter-input" id="newsletter-email" placeholder="Your Email" aria-label="Your Email" required>
                            <button class="newsletter-btn" type="submit">
                                <span>Subscribe</span>
                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            </button>
                        </div>
                        <div id="newsletter-message" class="mt-2"></div>
                    </form>
                </div>
            </div>
            
            <!-- Copyright -->
            <div class="text-center mt-4 pt-4 border-top border-secondary">
                <p class="mb-0">&copy; 2023 Luxe & Co. Events. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JS Loading Optimization -->
    <!-- Load jQuery first (from CDN with local fallback) -->
    <script>
      if (typeof jQuery == 'undefined') {
        document.write('<script src="/WeddinPlaning/assets/js/libs/jquery-3.6.0.min.js"><\/script>');
      }
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" 
            integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" 
            crossorigin="anonymous"></script>

    <!-- Bootstrap Bundle with Popper (loaded after jQuery) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" 
            integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" 
            crossorigin="anonymous"
            defer></script>

    <!-- Slick Slider with defer -->
    <script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js" 
            integrity="sha384-6lSJeXjXJxY7fF3z4cpZIa0ry0F6Op3fTQYoXz0+4G7b5h4u5qj1E5fD7Aq1IIw" 
            crossorigin="anonymous"
            defer></script>

    <!-- Magnific Popup with defer -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js" 
            integrity="sha512-IsNh5E3eYy3tr/JiX2Yx4vsCujtkhwl7SLqgnwLNgf04Hrt9BT9SXlLlZlWx+OK4ndzAoALhsMNcCmkggjZB1w==" 
            crossorigin="anonymous"
            defer></script>

    <!-- Custom JS - Load last with defer -->
    <script src="/WeddinPlaning/assets/js/main.js" defer></script>

    <!-- Optional: Preload important JS -->
    <link rel="preload" href="https://code.jquery.com/jquery-3.6.0.min.js" as="script">
    <link rel="preload" href="/WeddinPlaning/assets/js/main.js" as="script">
    
    <script>
document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("newsletterForm");
    const emailInput = document.getElementById("newsletter-email");
    const messageContainer = document.getElementById("newsletter-message");

    form.addEventListener("submit", handleNewsletterForm);

    function handleNewsletterForm(event) {
        event.preventDefault();

        const email = emailInput.value.trim();
        const submitButton = form.querySelector("button[type='submit']");
        const spinner = submitButton.querySelector(".spinner-border");
        const btnText = submitButton.querySelector(".submit-text");

        // Reset previous messages
        messageContainer.innerHTML = "";

        // Show loading
        spinner.classList.remove("d-none");
        btnText.textContent = "Sending...";

        // Validate email
        if (!isValidEmail(email)) {
            setTimeout(() => {
                showMessage("Please enter a valid email address.", "danger");
                resetButton();
            }, 300);
            return;
        }

        // Simulate submission
        setTimeout(() => {
            showMessage("Thank you for subscribing!", "success");
            emailInput.value = "";
            resetButton();
        }, 1000);
    }

    function isValidEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }

    function resetButton() {
        const submitButton = form.querySelector("button[type='submit']");
        const spinner = submitButton.querySelector(".spinner-border");
        const btnText = submitButton.querySelector(".submit-text");
        spinner.classList.add("d-none");
        btnText.textContent = "Subscribe";
    }

    function showMessage(message, type = "success") {
        const alertType = type === "success" ? "alert-success" : "alert-danger";
        messageContainer.innerHTML = `<div class="alert ${alertType} py-2 small">${message}</div>`;
    }
});
</script>
</main>
    <!-- End of Main Content -->
    <!-- Footer -->                                                      
</body>
</html>
