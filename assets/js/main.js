/**
 * Luxe & Co. Events - Wedding Planning Website
 * Main JavaScript File
 */

document.addEventListener('DOMContentLoaded', function() {
    // Fix cursor visibility issue - Add this at the top to ensure cursor is visible immediately
    document.body.style.cursor = 'auto';
    
    // Loading screen functionality
    const loadingScreen = document.querySelector('.loading-screen');
    if (loadingScreen) {
        // Show loading screen immediately
        loadingScreen.style.display = 'flex';
        
        // Hide loading screen when page is fully loaded
        window.addEventListener('load', function() {
            // Add a small delay to ensure everything is loaded
            setTimeout(() => {
                loadingScreen.classList.add('fade-out');
                // Remove loading screen from DOM after fade out
                setTimeout(() => {
                    loadingScreen.remove();
                }, 500);
            }, 1000); // Reduced from 5000ms to 1000ms for better user experience
        });
        
        // Fallback: Hide loading screen after 5 seconds even if page isn't fully loaded
        setTimeout(() => {
            if (loadingScreen && document.body.contains(loadingScreen)) {
                loadingScreen.classList.add('fade-out');
                setTimeout(() => {
                    if (loadingScreen && document.body.contains(loadingScreen)) {
                        loadingScreen.remove();
                    }
                }, 500);
            }
        }, 5000);
    }
    
    // Create hearts container
    const heartsContainer = document.createElement('div');
    heartsContainer.className = 'hearts-container';
    document.body.appendChild(heartsContainer);
    
    // Track scroll performance
    let lastScrollTime = 0;
    let isHeartsScrolling = false;
    
    // Check if device is mobile or has low performance
    const isMobileOrLowPerformance = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || 
                                    window.innerWidth < 768;
    
    // Start hearts animation only on desktop devices
    if (!isMobileOrLowPerformance) {
        createHearts();
    }
    
    // Hearts animation function with reduced number of hearts for better performance
    function createHearts() {
        const numberOfHearts = isMobileOrLowPerformance ? 10 : 20;
        
        for (let i = 0; i < numberOfHearts; i++) {
            setTimeout(() => {
                // Don't create hearts during rapid scrolling for better performance
                if (isHeartsScrolling && Date.now() - lastScrollTime < 100) {
                    return;
                }
                
                const heart = document.createElement('div');
                heart.className = 'heart';
                
                // Random position, size, and delay
                const size = Math.random() * 20 + 10;
                const left = Math.random() * 100;
                const animationDuration = Math.random() * 3 + 3;
                const delay = Math.random() * 5;
                
                heart.style.width = `${size}px`;
                heart.style.height = `${size}px`;
                heart.style.left = `${left}%`;
                heart.style.animationDuration = `${animationDuration}s`;
                heart.style.animationDelay = `${delay}s`;
                
                heartsContainer.appendChild(heart);
                
                // Remove heart after animation completes
                setTimeout(() => {
                    heart.remove();
                }, (animationDuration + delay) * 1000);
            }, i * 200);
        }
        
        // Create hearts periodically with longer intervals for better performance
        setTimeout(() => {
            createRandomHearts();
        }, 10000);
    }
    
    // Create random hearts occasionally
    function createRandomHearts() {
        // Don't create hearts during rapid scrolling for better performance
        if (isHeartsScrolling && Date.now() - lastScrollTime < 100) {
            // Try again after a short delay
            setTimeout(createRandomHearts, 500);
            return;
        }
        
        const numberOfHearts = Math.floor(Math.random() * 5) + 3;
        
        for (let i = 0; i < numberOfHearts; i++) {
            setTimeout(() => {
                const heart = document.createElement('div');
                heart.className = 'heart';
                
                // Random position, size, and delay
                const size = Math.random() * 20 + 10;
                const left = Math.random() * 100;
                const animationDuration = Math.random() * 3 + 3;
                
                heart.style.width = `${size}px`;
                heart.style.height = `${size}px`;
                heart.style.left = `${left}%`;
                heart.style.animationDuration = `${animationDuration}s`;
                
                heartsContainer.appendChild(heart);
                
                // Remove heart after animation completes
                setTimeout(() => {
                    heart.remove();
                }, animationDuration * 1000);
            }, i * 300);
        }
        
        // Schedule next batch of hearts
        const nextBatch = Math.random() * 15000 + 10000; // Between 10-25 seconds
        setTimeout(createRandomHearts, nextBatch);
    }
    
    // Apply additional animations to elements
    function applyAdditionalAnimations() {
        // Add pulse animation to service icons
        document.querySelectorAll('.service-icon').forEach((icon, index) => {
            icon.classList.add('pulse-animation');
            // Add delay to stagger animations
            icon.style.animationDelay = `${index * 0.2}s`;
        });
        
        // Add bounce animation to hero buttons
        document.querySelectorAll('.hero-content .btn').forEach((btn, index) => {
            btn.classList.add('bounce-animation');
            btn.style.animationDelay = `${index * 0.3}s`;
        });
        
        // Add shimmer effect to section titles
        document.querySelectorAll('.section-title').forEach(title => {
            title.classList.add('shimmer-animation');
        });
        
        // Add rotate animation to social icons
        document.querySelectorAll('.social-icons a').forEach((icon, index) => {
            icon.classList.add('rotate-animation');
            icon.style.animationDelay = `${index * 0.5}s`;
        });
        
        // Add fade-in-scale to gallery items
        document.querySelectorAll('.gallery-item').forEach((item, index) => {
            item.classList.add('fade-in-scale');
            item.style.animationDelay = `${0.1 + index * 0.1}s`;
        });
    }
    
    // Call the function to apply additional animations
    setTimeout(applyAdditionalAnimations, 2000);
    
    // Navbar scroll effect
    const navbar = document.querySelector('.navbar');
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            navbar.classList.add('py-2');
            navbar.classList.remove('py-3');
        } else {
            navbar.classList.add('py-3');
            navbar.classList.remove('py-2');
        }
    });
    
    // Initialize Testimonial Carousel
    if (document.querySelector('.testimonial-carousel')) {
        $('.testimonial-carousel').slick({
            dots: true,
            infinite: true,
            speed: 500,
            slidesToShow: 2,
            slidesToScroll: 1,
            autoplay: true,
            autoplaySpeed: 5000,
            responsive: [
                {
                    breakpoint: 992,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1
                    }
                }
            ]
        });
    }
    
    // Gallery Lightbox
    if (document.querySelector('.gallery-item')) {
        $('.gallery-item').magnificPopup({
            type: 'image',
            gallery: {
                enabled: true
            },
            zoom: {
                enabled: true,
                duration: 300
            }
        });
    }
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 80,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Form validation
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Simple validation
            let isValid = true;
            const requiredFields = contactForm.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (isValid) {
                // Here you would normally send the form data to the server
                // For now, just show a success message
                const formMessage = document.getElementById('formMessage');
                formMessage.innerHTML = '<div class="alert alert-success">Thank you for your message! We will get back to you soon.</div>';
                contactForm.reset();
                
                // Hide the message after 5 seconds
                setTimeout(() => {
                    formMessage.innerHTML = '';
                }, 5000);
            }
        });
    }
    
    // Animation on scroll with performance optimization
    const animatedElements = document.querySelectorAll('.animate-on-scroll');
    
    // Use a throttled scroll handler to improve performance and prevent animation issues
    let scrollTimeout;
    let isScrolling = false;
    
    function checkIfInView() {
        // Don't run multiple instances of this function simultaneously
        if (isScrolling) return;
        
        isScrolling = true;
        
        // Use requestAnimationFrame for better performance
        requestAnimationFrame(() => {
            const windowHeight = window.innerHeight;
            const windowTopPosition = window.scrollY;
            const windowBottomPosition = windowTopPosition + windowHeight;
            
            animatedElements.forEach(element => {
                const elementHeight = element.offsetHeight;
                const elementTopPosition = element.getBoundingClientRect().top + windowTopPosition;
                const elementBottomPosition = elementTopPosition + elementHeight;
                
                if (
                    (elementBottomPosition >= windowTopPosition) &&
                    (elementTopPosition <= windowBottomPosition)
                ) {
                    element.classList.add('animated');
                }
            });
            
            // Allow the function to run again after a short delay
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                isScrolling = false;
            }, 50);
        });
    }
    
    // Use passive event listener for better scroll performance
    window.addEventListener('scroll', checkIfInView, { passive: true });
    window.addEventListener('resize', checkIfInView, { passive: true });
    window.addEventListener('load', checkIfInView);
    
    // Initialize checkIfInView on page load
    checkIfInView();

    // ===== USER AVATAR FUNCTIONALITY =====
    function initializeUserAvatar() {
        const userAvatarDesktop = document.querySelector('.d-none.d-lg-block .user-avatar-container');
        const userDropdownDesktop = document.getElementById('userDropdown');
        
        const userAvatarMobile = document.querySelector('.d-lg-none .user-avatar-container');
        const userDropdownMobile = document.getElementById('userDropdownMobile');
        
        const isMobile = window.innerWidth < 992;
        
        // Initialize desktop avatar
        if (userAvatarDesktop && userDropdownDesktop) {
            // Improve dropdown behavior
            const dropdownMenu = userDropdownDesktop.nextElementSibling;
            
            // Add hover effect for dropdown items
            const dropdownItems = dropdownMenu.querySelectorAll('.dropdown-item');
            dropdownItems.forEach(item => {
                item.addEventListener('mouseenter', function() {
                    this.style.transition = 'all 0.2s ease';
                });
            });
            
            // Prevent dropdown from closing when clicking inside it
            dropdownMenu.addEventListener('click', function(e) {
                if (e.target.classList.contains('dropdown-item')) {
                    // Allow navigation for links
                    return;
                }
                e.stopPropagation();
            });
        }
        
        // Initialize mobile avatar
        if (userAvatarMobile && userDropdownMobile) {
            const mobileDropdownMenu = userDropdownMobile.nextElementSibling;
            
            // Improve mobile dropdown behavior
            mobileDropdownMenu.addEventListener('click', function(e) {
                if (!e.target.classList.contains('dropdown-item')) {
                    e.stopPropagation();
                }
            });
        }
        
        // Handle responsive behavior
        window.addEventListener('resize', function() {
            const newIsMobile = window.innerWidth < 992;
            if (newIsMobile !== isMobile) {
                // Close dropdowns when resizing
                const openDropdowns = document.querySelectorAll('.dropdown-menu.show');
                openDropdowns.forEach(dropdown => {
                    const bsDropdown = bootstrap.Dropdown.getInstance(dropdown.previousElementSibling);
                    if (bsDropdown) {
                        bsDropdown.hide();
                    }
                });
            }
        });
    }
    
    // Initialize user avatar functionality
    initializeUserAvatar();

    // ===== SERVICES PAGE FUNCTIONALITY =====
    function initializeServicesPage() {
        // Fix cursor visibility issue
        document.body.style.cursor = 'auto';
        
        // Remove any custom cursor elements that might be present
        const cursorDot = document.querySelector('.cursor-dot');
        const cursorOutline = document.querySelector('.cursor-outline');
        
        if (cursorDot) cursorDot.style.display = 'none';
        if (cursorOutline) cursorOutline.style.display = 'none';
        
        // Enhanced hover effect for service cards
        const serviceCards = document.querySelectorAll('.service-card');
        
        serviceCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                // Add a subtle border glow effect
                this.style.boxShadow = '0 15px 30px rgba(255, 64, 129, 0.2)';
                
                // Animate the service icon
                const icon = this.querySelector('.service-icon i');
                if (icon) {
                    icon.style.transform = 'scale(1.2) rotate(5deg)';
                    icon.style.transition = 'transform 0.3s ease';
                }
            });
            
            card.addEventListener('mouseleave', function() {
                // Remove the border glow effect
                this.style.boxShadow = '0 5px 15px rgba(0, 0, 0, 0.05)';
                
                // Reset the service icon
                const icon = this.querySelector('.service-icon i');
                if (icon) {
                    icon.style.transform = 'scale(1) rotate(0deg)';
                }
            });
        });
    }

    // Check if we're on the services page and initialize if needed
    if (document.querySelector('.services-section')) {
        initializeServicesPage();
    }

    // ===== AUTH FORMS FUNCTIONALITY =====
    function initializeAuthForms() {
        // Tab switching functionality
        const tabs = document.querySelectorAll('.auth-tab');
        const sections = document.querySelectorAll('.form-section');

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const targetTab = tab.dataset.tab;
                
                // Update active tab
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                
                // Update active section
                sections.forEach(section => {
                    section.classList.remove('active');
                    if (section.id === `${targetTab}-section`) {
                        section.classList.add('active');
                    }
                });
            });
        });

        // Password toggle functionality
        const passwordToggles = document.querySelectorAll('.password-toggle');
        passwordToggles.forEach(toggle => {
            toggle.addEventListener('click', function() {
                const input = this.previousElementSibling;
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        });

        // Form validation and submission
        const forms = document.querySelectorAll('.auth-form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Get form data
                const formData = new FormData(this);
                const data = Object.fromEntries(formData.entries());
                
                // Basic validation
                let isValid = true;
                const requiredFields = this.querySelectorAll('[required]');
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.classList.add('is-invalid');
                    } else {
                        field.classList.remove('is-invalid');
                    }
                });
                
                // Password match validation for registration
                if (this.id === 'register-form') {
                    const password = this.querySelector('input[type="password"]').value;
                    const confirmPassword = this.querySelector('input[type="password"]:last-of-type').value;
                    
                    if (password !== confirmPassword) {
                        isValid = false;
                        this.querySelector('input[type="password"]:last-of-type').classList.add('is-invalid');
                        
                        // Show password mismatch error
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'alert alert-danger mt-3';
                        errorDiv.textContent = 'Passwords do not match';
                        this.insertBefore(errorDiv, this.firstChild);
                        
                        // Remove error message after 3 seconds
                        setTimeout(() => {
                            errorDiv.remove();
                        }, 3000);
                    }
                }
                
                if (isValid) {
                    // Show loading state
                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
                    submitBtn.disabled = true;
                    
                    // Submit the form
                    this.submit();
                }
            });
        });

        // Social login handlers
        const socialButtons = document.querySelectorAll('.social-btn');
        socialButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const platform = this.querySelector('i').classList[1].split('-')[2];
                
                // Show loading state
                const originalContent = this.innerHTML;
                this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
                
                // Simulate social login (replace with actual social login implementation)
                setTimeout(() => {
                    this.innerHTML = originalContent;
                }, 1500);
            });
        });
    }

    // Initialize auth forms if they exist on the page
    if (document.querySelector('.auth-container')) {
        initializeAuthForms();
    }

    // Initialize dropdowns for mobile
    function initializeDropdowns() {
        // Get all dropdown toggles
        const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
        
        // For mobile devices, ensure dropdowns work with tap
        if (window.innerWidth < 992) {
            dropdownToggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    // Prevent default behavior only for service dropdown on mobile
                    if (this.id === 'servicesDropdown' && window.innerWidth < 992) {
                        e.preventDefault();
                        const dropdownMenu = this.nextElementSibling;
                        if (dropdownMenu.classList.contains('show')) {
                            dropdownMenu.classList.remove('show');
                        } else {
                            // Close any open dropdowns
                            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                                menu.classList.remove('show');
                            });
                            dropdownMenu.classList.add('show');
                        }
                    }
                });
            });
            
            // Close dropdowns when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dropdown')) {
                    document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                        menu.classList.remove('show');
                    });
                }
            });
        }
    }
    
    // Call the function to initialize dropdowns
    initializeDropdowns();
    
    // Re-initialize on window resize
    window.addEventListener('resize', function() {
        initializeDropdowns();
    });

    // Enhanced mobile navigation
    function enhanceMobileNavigation() {
        const navbarToggler = document.querySelector('.navbar-toggler');
        const navbarCollapse = document.querySelector('.navbar-collapse');
        
        // Close navbar when clicking outside
        document.addEventListener('click', function(e) {
            if (window.innerWidth < 992 && 
                navbarCollapse.classList.contains('show') && 
                !navbarCollapse.contains(e.target) && 
                !navbarToggler.contains(e.target)) {
                
                // Use Bootstrap's collapse API to hide the menu
                const bsCollapse = new bootstrap.Collapse(navbarCollapse);
                bsCollapse.hide();
            }
        });
        
        // Close navbar when clicking on a nav link (for smoother mobile experience)
        const navLinks = document.querySelectorAll('.navbar-nav .nav-link:not(.dropdown-toggle)');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth < 992 && navbarCollapse.classList.contains('show')) {
                    const bsCollapse = new bootstrap.Collapse(navbarCollapse);
                    bsCollapse.hide();
                }
            });
        });
        
        // Improve touch experience for dropdowns on mobile
        const dropdownItems = document.querySelectorAll('.dropdown-item');
        dropdownItems.forEach(item => {
            item.addEventListener('touchstart', function() {
                this.classList.add('active-touch');
            });
            
            item.addEventListener('touchend', function() {
                setTimeout(() => {
                    this.classList.remove('active-touch');
                }, 100);
            });
        });
    }
    
    // Call the function to enhance mobile navigation
    enhanceMobileNavigation();
    
    // Handle orientation changes
    window.addEventListener('orientationchange', function() {
        // Recalculate heights and positions after orientation change
        setTimeout(() => {
            // Check if we're in view
            checkIfInView();
            
            // Reinitialize dropdowns
            initializeDropdowns();
            
            // Close any open mobile menu
            const navbarCollapse = document.querySelector('.navbar-collapse');
            if (navbarCollapse.classList.contains('show')) {
                const bsCollapse = new bootstrap.Collapse(navbarCollapse);
                bsCollapse.hide();
            }
        }, 200);
    });
    
    // Improve scroll performance on mobile
    let mobileScrollTimeout;
    window.addEventListener('scroll', function() {
        // Update scroll tracking
        lastScrollTime = Date.now();
        isHeartsScrolling = true;
        
        clearTimeout(mobileScrollTimeout);
        
        // Disable animations during scroll for better performance
        // But keep hearts animation running
        document.body.classList.add('disable-animations');
        
        // Make sure hearts continue to animate during scrolling
        const heartsContainer = document.querySelector('.hearts-container');
        if (heartsContainer) {
            heartsContainer.classList.add('keep-animating');
        }
        
        mobileScrollTimeout = setTimeout(function() {
            document.body.classList.remove('disable-animations');
            if (heartsContainer) {
                heartsContainer.classList.remove('keep-animating');
            }
            // Reset scrolling flag after a delay
            setTimeout(() => {
                isHeartsScrolling = false;
            }, 200);
        }, 150);
    });

    // Implement lazy loading for images
    function initLazyLoading() {
        // Check if IntersectionObserver is supported
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        const src = img.getAttribute('data-src');
                        
                        if (src) {
                            img.src = src;
                            img.classList.add('loaded');
                            observer.unobserve(img);
                        }
                    }
                });
            }, {
                rootMargin: '50px 0px',
                threshold: 0.01
            });
            
            // Target all images with data-src attribute
            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
                img.classList.add('lazy-load');
            });
        } else {
            // Fallback for browsers that don't support IntersectionObserver
            const lazyImages = document.querySelectorAll('img[data-src]');
            
            function lazyLoad() {
                const scrollTop = window.pageYOffset;
                
                lazyImages.forEach(img => {
                    if (img.offsetTop < window.innerHeight + scrollTop) {
                        const src = img.getAttribute('data-src');
                        if (src) {
                            img.src = src;
                            img.classList.add('loaded');
                        }
                    }
                });
                
                // If all images are loaded, remove the scroll event listener
                if (lazyImages.length === 0) {
                    window.removeEventListener('scroll', lazyLoad);
                }
            }
            
            // Add initial class
            lazyImages.forEach(img => {
                img.classList.add('lazy-load');
            });
            
            // Load images initially in viewport
            lazyLoad();
            
            // Add scroll event
            window.addEventListener('scroll', lazyLoad);
        }
    }
    
    // Initialize lazy loading
    initLazyLoading();
    
    // Special handling for footer area to ensure hearts animation works smoothly
    const footer = document.querySelector('.footer');
    if (footer) {
        // Add a special class to the footer for better animation handling
        footer.classList.add('hearts-enabled-section');
        
        // Create an intersection observer to detect when footer is in view
        const footerObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // When footer is in view, ensure hearts continue to animate
                    document.body.classList.add('footer-in-view');
                    
                    // Force hearts to continue animating
                    const heartsContainer = document.querySelector('.hearts-container');
                    if (heartsContainer) {
                        heartsContainer.classList.add('footer-active');
                        
                        // Create a few extra hearts for visual effect
                        for (let i = 0; i < 5; i++) {
                            setTimeout(() => {
                                const heart = document.createElement('div');
                                heart.className = 'heart footer-heart';
                                
                                // Random position, size, and delay
                                const size = Math.random() * 20 + 10;
                                const left = Math.random() * 100;
                                const animationDuration = Math.random() * 3 + 3;
                                
                                heart.style.width = `${size}px`;
                                heart.style.height = `${size}px`;
                                heart.style.left = `${left}%`;
                                heart.style.animationDuration = `${animationDuration}s`;
                                
                                heartsContainer.appendChild(heart);
                                
                                // Remove heart after animation completes
                                setTimeout(() => {
                                    heart.remove();
                                }, animationDuration * 1000);
                            }, i * 300);
                        }
                    }
                } else {
                    // When footer is out of view, remove the special class
                    document.body.classList.remove('footer-in-view');
                    
                    const heartsContainer = document.querySelector('.hearts-container');
                    if (heartsContainer) {
                        heartsContainer.classList.remove('footer-active');
                    }
                }
            });
        }, {
            threshold: 0.1 // Trigger when at least 10% of the footer is visible
        });
        
        // Start observing the footer
        footerObserver.observe(footer);
    }

    // ===== BEAUTY SALON PAGE FUNCTIONALITY =====
    function initializeBeautySalonPage() {
        // Check if we're on the beauty salon page
        if (!document.querySelector('.beauty-salon-page')) {
            return;
        }

        // Initialize AOS
        if (typeof AOS !== 'undefined') {
            AOS.init();
        }

        // Back to top button functionality
        const backToTopBtn = document.getElementById('backToTopBtn');
        if (backToTopBtn) {
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    backToTopBtn.classList.add('show');
                } else {
                    backToTopBtn.classList.remove('show');
                }
            });
            
            backToTopBtn.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }
        
        // Reset filters button functionality
        const resetFiltersBtn = document.getElementById('resetFilters');
        if (resetFiltersBtn) {
            resetFiltersBtn.addEventListener('click', function() {
                document.getElementById('searchSalon').value = '';
                document.getElementById('filterRating').value = '';
                document.getElementById('filterService').value = '';
                
                // Trigger the filter function
                const event = new Event('input');
                document.getElementById('searchSalon').dispatchEvent(event);
            });
        }

        // Heart Animation Function
        function createHeart() {
            const heart = document.createElement('div');
            heart.classList.add('heart');
            heart.style.left = Math.random() * 100 + 'vw';
            heart.style.animationDuration = (Math.random() * 3 + 2) + 's';
            heart.style.opacity = Math.random();
            heart.style.fontSize = (Math.random() * 10 + 10) + 'px';
            document.getElementById('heart-container').appendChild(heart);
            
            setTimeout(() => {
                heart.remove();
            }, 5000);
        }

        // Create hearts periodically
        setInterval(createHeart, 300);
        
        // Sample data for beauty salons
        const salons = [
            {
                id: 1,
                name: "Glamour Beauty Studio",
                image: "../../assets/images/salons/salon1.jpg",
                rating: 4.8,
                reviews: 124,
                services: ["Hair", "Makeup", "Nails"],
                priceRange: "$$$",
                location: "123 Main Street, City Center",
                description: "Luxury beauty salon offering premium services for brides and wedding parties. Our team of experts will make sure you look stunning on your special day.",
                phone: "+1 (555) 123-4567",
                website: "www.glamourbeauty.com",
                hours: "Mon-Sat: 9AM-8PM, Sun: 10AM-6PM"
            },
            {
                id: 2,
                name: "Elegance Spa & Salon",
                image: "../../assets/images/salons/salon2.jpg",
                rating: 4.6,
                reviews: 98,
                services: ["Hair", "Makeup", "Spa"],
                priceRange: "$$",
                location: "456 Park Avenue, Downtown",
                description: "Full-service salon and spa dedicated to making your wedding preparations stress-free and enjoyable. Special packages available for bridal parties.",
                phone: "+1 (555) 987-6543",
                website: "www.elegancespa.com",
                hours: "Mon-Sat: 8AM-9PM, Sun: 10AM-7PM"
            },
            {
                id: 3,
                name: "Bridal Beauty Lounge",
                image: "../../assets/images/salons/salon3.jpg",
                rating: 5.0,
                reviews: 87,
                services: ["Hair", "Makeup", "Nails", "Spa"],
                priceRange: "$$$",
                location: "789 Wedding Lane, Uptown",
                description: "Specialized in bridal beauty services with a team of certified professionals. We offer trials, consultations, and on-site services for your wedding day.",
                phone: "+1 (555) 765-4321",
                website: "www.bridalbeauty.com",
                hours: "Mon-Sun: 9AM-9PM"
            },
            {
                id: 4,
                name: "Natural Glow Salon",
                image: "../../assets/images/salons/salon4.jpg",
                rating: 4.3,
                reviews: 65,
                services: ["Hair", "Makeup", "Nails"],
                priceRange: "$$",
                location: "321 Beauty Blvd, Westside",
                description: "Eco-friendly salon using organic products for a natural look. Perfect for brides who prefer a more subtle, natural appearance.",
                phone: "+1 (555) 234-5678",
                website: "www.naturalglow.com",
                hours: "Tue-Sat: 10AM-7PM, Sun: 11AM-5PM, Mon: Closed"
            },
            {
                id: 5,
                name: "Royal Treatment Salon",
                image: "../../assets/images/salons/salon5.jpg",
                rating: 4.7,
                reviews: 112,
                services: ["Hair", "Makeup", "Spa"],
                priceRange: "$$$",
                location: "555 Luxury Ave, Eastside",
                description: "Premium salon offering the royal treatment for brides. Our VIP packages include champagne, snacks, and a private salon area for your party.",
                phone: "+1 (555) 876-5432",
                website: "www.royaltreatment.com",
                hours: "Mon-Sat: 8AM-8PM, Sun: 9AM-6PM"
            },
            {
                id: 6,
                name: "Budget Beauty Bar",
                image: "../../assets/images/salons/salon6.jpg",
                rating: 4.1,
                reviews: 78,
                services: ["Hair", "Makeup", "Nails"],
                priceRange: "$",
                location: "999 Value Street, Southside",
                description: "Quality beauty services at affordable prices. Great for wedding parties on a budget without compromising on quality.",
                phone: "+1 (555) 345-6789",
                website: "www.budgetbeauty.com",
                hours: "Mon-Sun: 9AM-7PM"
            }
        ];

        // Function to render salon cards
        function renderSalons(salonsArray) {
            const salonsListElement = document.getElementById('salonsList');
            if (!salonsListElement) return;
            
            salonsListElement.innerHTML = '';

            if (salonsArray.length === 0) {
                salonsListElement.innerHTML = '<div class="col-12 text-center py-5"><h3 class="salon-heading">No salons found matching your criteria</h3></div>';
                return;
            }

            salonsArray.forEach((salon, index) => {
                const starsHTML = generateStarRating(salon.rating);
                const servicesHTML = salon.services.map(service => 
                    `<span class="badge service-badge">${service}</span>`
                ).join('');

                const salonCard = document.createElement('div');
                salonCard.className = 'col-md-6 col-lg-4 mb-4';
                salonCard.setAttribute('data-aos', 'fade-up');
                salonCard.setAttribute('data-aos-duration', '800');
                salonCard.setAttribute('data-aos-delay', (index * 100).toString());
                
                salonCard.innerHTML = `
                    <div class="card salon-card h-100 shadow-sm" data-salon-id="${salon.id}">
                        <img src="${salon.image}" class="card-img-top salon-image" alt="${salon.name}">
                        <div class="card-body">
                            <h5 class="card-title salon-heading">${salon.name}</h5>
                            <div class="mb-2">
                                <span class="rating">${starsHTML}</span>
                                <span class="text-muted ms-2">(${salon.reviews} reviews)</span>
                            </div>
                            <div class="mb-2">
                                ${servicesHTML}
                            </div>
                            <p class="card-text mb-1"><i class="fas fa-map-marker-alt me-2"></i>${salon.location}</p>
                            <p class="price-range mb-3">${salon.priceRange}</p>
                            <button class="btn btn-outline-primary btn-sm view-details">View Details</button>
                        </div>
                    </div>
                `;
                salonsListElement.appendChild(salonCard);

                // Add click event to the card
                salonCard.querySelector('.view-details').addEventListener('click', function() {
                    showSalonDetails(salon);
                });
            });
        }

        // Function to generate star rating HTML
        function generateStarRating(rating) {
            let starsHTML = '';
            const fullStars = Math.floor(rating);
            const halfStar = rating % 1 >= 0.5;

            for (let i = 1; i <= 5; i++) {
                if (i <= fullStars) {
                    starsHTML += '<i class="fas fa-star"></i>';
                } else if (i === fullStars + 1 && halfStar) {
                    starsHTML += '<i class="fas fa-star-half-alt"></i>';
                } else {
                    starsHTML += '<i class="far fa-star"></i>';
                }
            }

            return starsHTML;
        }

        // Function to show salon details in modal
        function showSalonDetails(salon) {
            const modalTitle = document.getElementById('salonModalLabel');
            const modalBody = document.getElementById('salonModalBody');
            
            if (!modalTitle || !modalBody) return;
            
            modalTitle.textContent = salon.name;
            
            const starsHTML = generateStarRating(salon.rating);
            const servicesHTML = salon.services.map(service => 
                `<span class="badge service-badge">${service}</span>`
            ).join('');
            
            modalBody.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <img src="${salon.image}" class="img-fluid rounded mb-3" alt="${salon.name}">
                    </div>
                    <div class="col-md-6">
                        <div class="mb-2">
                            <span class="rating">${starsHTML}</span>
                            <span class="text-muted ms-2">(${salon.reviews} reviews)</span>
                        </div>
                        <p class="price-range mb-2">${salon.priceRange}</p>
                        <p><i class="fas fa-map-marker-alt me-2"></i>${salon.location}</p>
                        <p><i class="fas fa-phone me-2"></i>${salon.phone}</p>
                        <p><i class="fas fa-globe me-2"></i>${salon.website}</p>
                        <p><i class="fas fa-clock me-2"></i>${salon.hours}</p>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <h5 class="salon-heading">Services</h5>
                        <div class="mb-3">
                            ${servicesHTML}
                        </div>
                        <h5 class="salon-heading">Description</h5>
                        <p>${salon.description}</p>
                    </div>
                </div>
            `;
            
            // Show the modal
            const salonModal = new bootstrap.Modal(document.getElementById('salonModal'));
            salonModal.show();
            
            // Set up the Book Now button
            const bookNowBtn = document.getElementById('bookNowBtn');
            if (bookNowBtn) {
                bookNowBtn.onclick = function() {
                    window.location.href = `../../booking.php?service=beauty&salon=${salon.id}`;
                };
            }
        }

        // Filter and search functionality
        function filterSalons() {
            const searchInput = document.getElementById('searchSalon');
            const ratingFilter = document.getElementById('filterRating');
            const serviceFilter = document.getElementById('filterService');
            
            if (!searchInput || !ratingFilter || !serviceFilter) return;
            
            const searchTerm = searchInput.value.toLowerCase();
            const ratingValue = ratingFilter.value;
            const serviceValue = serviceFilter.value;
            
            const filteredSalons = salons.filter(salon => {
                // Search term filter
                const matchesSearch = salon.name.toLowerCase().includes(searchTerm) || 
                                     salon.description.toLowerCase().includes(searchTerm) ||
                                     salon.location.toLowerCase().includes(searchTerm);
                
                // Rating filter
                const matchesRating = ratingValue === '' || salon.rating >= parseFloat(ratingValue);
                
                // Service filter
                const matchesService = serviceValue === '' || salon.services.includes(serviceValue);
                
                return matchesSearch && matchesRating && matchesService;
            });
            
            renderSalons(filteredSalons);
        }

        // Set up event listeners for filters
        const searchInput = document.getElementById('searchSalon');
        const ratingFilter = document.getElementById('filterRating');
        const serviceFilter = document.getElementById('filterService');
        
        if (searchInput) {
            searchInput.addEventListener('input', filterSalons);
        }
        
        if (ratingFilter) {
            ratingFilter.addEventListener('change', filterSalons);
        }
        
        if (serviceFilter) {
            serviceFilter.addEventListener('change', filterSalons);
        }

        // Initial render
        renderSalons(salons);
    }

    // Initialize all components
    applyAdditionalAnimations();
    checkIfInView();
    initializeUserAvatar();
    initializeServicesPage();
    initializeAuthForms();
    initializeDropdowns();
    enhanceMobileNavigation();
    initLazyLoading();
    initializeBeautySalonPage();

    // Function to ensure cursor visibility throughout the website
    function ensureCursorVisibility() {
        // Set body cursor to auto
        document.body.style.cursor = 'auto';
        
        // Hide custom cursor elements
        const cursorDot = document.querySelector('.cursor-dot');
        const cursorOutline = document.querySelector('.cursor-outline');
        
        if (cursorDot) cursorDot.style.display = 'none';
        if (cursorOutline) cursorOutline.style.display = 'none';
        
        // Add event listener to ensure cursor remains visible when moving between pages
        document.addEventListener('mousemove', function() {
            document.body.style.cursor = 'auto';
        });
        
        // Ensure cursor is visible on all clickable elements
        const clickableElements = document.querySelectorAll('a, button, input, select, textarea, [role="button"]');
        clickableElements.forEach(element => {
            element.style.cursor = 'pointer';
        });
    }
    
    // Call the function to ensure cursor visibility
    ensureCursorVisibility();

    // ===== ACCOUNT PAGE FUNCTIONALITY =====
    function initializeAccountPage() {
        // Check if we're on the account page
        if (!document.querySelector('.account-section')) {
            return;
        }

        // Initialize AOS
        if (typeof AOS !== 'undefined') {
            AOS.init({
                duration: 800,
                once: true
            });
        }

        // Handle tab switching
        const tabs = document.querySelectorAll('.list-group-item');
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                tabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Handle booking confirmation
        const confirmBookingBtn = document.querySelector('.confirm-booking');
        if (confirmBookingBtn) {
            confirmBookingBtn.addEventListener('click', function() {
                // Show planner modal
                const plannerModal = new bootstrap.Modal(document.getElementById('plannerModal'));
                plannerModal.show();
            });
        }

        // Handle payment flow
        const proceedToPaymentBtn = document.getElementById('proceedToPayment');
        const paymentModal = document.getElementById('paymentModal');
        const confirmPaymentBtn = document.getElementById('confirmPayment');

        if (proceedToPaymentBtn && paymentModal) {
            proceedToPaymentBtn.addEventListener('click', function() {
                // Close planner modal
                const plannerModal = bootstrap.Modal.getInstance(document.getElementById('plannerModal'));
                plannerModal.hide();

                // Show payment modal
                const paymentModalInstance = new bootstrap.Modal(paymentModal);
                paymentModalInstance.show();
            });
        }

        // Handle payment confirmation
        if (confirmPaymentBtn) {
            confirmPaymentBtn.addEventListener('click', function() {
                // Get form data
                const form = document.getElementById('paymentForm');
                const formData = new FormData(form);

                // Validate form
                let isValid = true;
                const requiredFields = form.querySelectorAll('input[required]');
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.classList.add('is-invalid');
                    } else {
                        field.classList.remove('is-invalid');
                    }
                });

                if (isValid) {
                    // Show loading state
                    this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
                    this.disabled = true;

                    // Simulate payment processing
                    setTimeout(() => {
                        // Update booking status
                        const bookingStatus = document.querySelector('.booking-status');
                        if (bookingStatus) {
                            bookingStatus.textContent = 'Confirmed';
                            bookingStatus.classList.remove('status-pending');
                            bookingStatus.classList.add('status-confirmed');
                        }

                        // Update progress bar
                        const progressBar = document.querySelector('.booking-progress .progress-bar');
                        if (progressBar) {
                            progressBar.style.width = '100%';
                        }

                        // Close payment modal
                        const paymentModalInstance = bootstrap.Modal.getInstance(paymentModal);
                        paymentModalInstance.hide();

                        // Show success message
                        showNotification('Payment successful! Your booking has been confirmed.', 'success');

                        // Reset button state
                        this.innerHTML = 'Confirm Payment';
                        this.disabled = false;
                    }, 2000);
                }
            });
        }

        // Handle profile form submission
        const profileForm = document.querySelector('#profile form');
        if (profileForm) {
            profileForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const submitBtn = this.querySelector('button[type="submit"]');
                
                // Show loading state
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';
                submitBtn.disabled = true;

                // Simulate API call
                setTimeout(() => {
                    showNotification('Profile updated successfully!', 'success');
                    
                    // Reset button state
                    submitBtn.innerHTML = 'Update Profile';
                    submitBtn.disabled = false;
                }, 1500);
            });
        }

        // Handle password change
        const passwordForm = document.querySelector('#settings form');
        if (passwordForm) {
            passwordForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const submitBtn = this.querySelector('button[type="submit"]');
                
                // Show loading state
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';
                submitBtn.disabled = true;

                // Simulate API call
                setTimeout(() => {
                    showNotification('Password updated successfully!', 'success');
                    this.reset();
                    
                    // Reset button state
                    submitBtn.innerHTML = 'Update Password';
                    submitBtn.disabled = false;
                }, 1500);
            });
        }

        // Notification function
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} notification`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            // Trigger animation
            setTimeout(() => {
                notification.classList.add('show');
            }, 100);
            
            // Remove notification after 3 seconds
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 3000);
        }
    }

    // Initialize account page functionality
    initializeAccountPage();

    // ===== CART AND RECEIPT FUNCTIONALITY =====
    function initializeCartFunctions() {
        // Check if we're on the cart page
        if (!document.querySelector('.cart-section')) {
            return;
        }

        // Save cart items to localStorage when updated
        function saveCartToLocalStorage() {
            if (typeof cartItems !== 'undefined') {
                localStorage.setItem('savedCartItems', JSON.stringify(cartItems));
            }
        }

        // Load cart items from localStorage if available
        function loadCartFromLocalStorage() {
            const savedCartItems = localStorage.getItem('savedCartItems');
            if (savedCartItems && typeof cartItems !== 'undefined') {
                try {
                    const parsedCart = JSON.parse(savedCartItems);
                    if (Array.isArray(parsedCart) && parsedCart.length > 0) {
                        cartItems = parsedCart;
                        updateCartDisplay();
                        updateCartSummary();
                    }
                } catch (e) {
                    console.error('Error parsing saved cart items:', e);
                }
            }
        }

        // Add event listeners to cart functions if they exist
        if (typeof updateQuantity === 'function') {
            const originalUpdateQuantity = updateQuantity;
            window.updateQuantity = function(itemId, change) {
                originalUpdateQuantity(itemId, change);
                saveCartToLocalStorage();
            };
        }

        if (typeof updateQuantityInput === 'function') {
            const originalUpdateQuantityInput = updateQuantityInput;
            window.updateQuantityInput = function(itemId, value) {
                originalUpdateQuantityInput(itemId, value);
                saveCartToLocalStorage();
            };
        }

        if (typeof removeItem === 'function') {
            const originalRemoveItem = removeItem;
            window.removeItem = function(itemId) {
                originalRemoveItem(itemId);
                saveCartToLocalStorage();
            };
        }

        // Add animation effects to cart buttons
        const cartButtons = document.querySelectorAll('.quantity-btn, .remove-item');
        cartButtons.forEach(button => {
            button.addEventListener('click', function() {
                this.classList.add('button-click');
                setTimeout(() => {
                    this.classList.remove('button-click');
                }, 300);
            });
        });

        // Add animation to checkout button
        const checkoutBtn = document.querySelector('.checkout-btn');
        if (checkoutBtn) {
            checkoutBtn.addEventListener('mouseenter', function() {
                this.classList.add('checkout-hover');
            });
            
            checkoutBtn.addEventListener('mouseleave', function() {
                this.classList.remove('checkout-hover');
            });
        }

        // Load cart items when page loads
        loadCartFromLocalStorage();
    }

    // Initialize receipt download functionality
    function initializeReceiptFunctions() {
        document.addEventListener('click', function(e) {
            // Check if the download receipt button is clicked
            if (e.target.id === 'downloadReceiptBtn' || e.target.closest('#downloadReceiptBtn')) {
                const receipt = document.getElementById('receipt');
                if (!receipt) return;
                
                const downloadBtn = e.target.id === 'downloadReceiptBtn' ? e.target : e.target.closest('#downloadReceiptBtn');
                const originalText = downloadBtn.innerHTML;
                downloadBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Generating PDF...';
                downloadBtn.disabled = true;
                
                // Check if html2pdf is loaded
                if (typeof html2pdf === 'undefined') {
                    // Load html2pdf dynamically if not already loaded
                    const script = document.createElement('script');
                    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
                    script.onload = function() {
                        generatePdf(receipt, downloadBtn, originalText);
                    };
                    document.head.appendChild(script);
                } else {
                    // Generate PDF if html2pdf is already loaded
                    generatePdf(receipt, downloadBtn, originalText);
                }
            }
            
            // Check if the print receipt button is clicked
            if (e.target.id === 'printReceiptBtn' || e.target.closest('#printReceiptBtn')) {
                const printBtn = e.target.id === 'printReceiptBtn' ? e.target : e.target.closest('#printReceiptBtn');
                const originalText = printBtn.innerHTML;
                
                // Show loading state
                printBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Preparing...';
                printBtn.disabled = true;
                
                // Delay for visual feedback
                setTimeout(() => {
                    // Reset button
                    printBtn.innerHTML = originalText;
                    printBtn.disabled = false;
                    
                    // Print
                    window.print();
                }, 500);
            }
            
            // Check if the request planner button is clicked
            if (e.target.id === 'requestPlannerBtn' || e.target.closest('#requestPlannerBtn')) {
                const plannerBtn = e.target.id === 'requestPlannerBtn' ? e.target : e.target.closest('#requestPlannerBtn');
                
                // Store cart items for the account page
                if (typeof cartItems !== 'undefined') {
                    localStorage.setItem('cartItems', JSON.stringify(cartItems));
                    localStorage.setItem('requestPlanner', 'true');
                }
                
                // Update button state
                plannerBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Redirecting...';
                plannerBtn.disabled = true;
                
                // Close modal if it exists
                const receiptModal = document.getElementById('receiptModal');
                if (receiptModal && typeof bootstrap !== 'undefined') {
                    const modalInstance = bootstrap.Modal.getInstance(receiptModal);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                }
                
                // Redirect to account page
                setTimeout(() => {
                    window.location.href = '/WeddinPlaning/pages/account.php#bookings';
                }, 800);
            }
        });
        
        // Function to generate and download PDF
        function generatePdf(element, button, originalButtonText) {
            // Temporarily add print styles to make the PDF look better
            const tempStyle = document.createElement('style');
            tempStyle.innerHTML = `
                @media print {
                    #receipt { padding: 20px !important; }
                    #receipt h3 { font-size: 20px !important; }
                    #receipt p { font-size: 12px !important; margin-bottom: 4px !important; }
                    #receipt .table th, #receipt .table td { padding: 8px !important; font-size: 12px !important; }
                }
            `;
            document.head.appendChild(tempStyle);
            
            const opt = {
                margin: [0.5, 0.5, 0.5, 0.5],
                filename: 'wedding-services-receipt.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true },
                jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
            };
            
            // Generate PDF with a slight delay for better UI feedback
            setTimeout(() => {
                html2pdf().set(opt).from(element).save().then(() => {
                    // Reset button state
                    button.innerHTML = originalButtonText;
                    button.disabled = false;
                    
                    // Remove temp styles
                    document.head.removeChild(tempStyle);
                });
            }, 500);
        }
        
        // Handle print media queries
        window.addEventListener('beforeprint', function() {
            // Add class to body for print-specific styles
            document.body.classList.add('printing-receipt');
            
            // Show only the receipt
            const receipt = document.getElementById('receipt');
            if (receipt) {
                receipt.classList.add('print-mode');
            }
        });
        
        window.addEventListener('afterprint', function() {
            // Remove print-specific classes
            document.body.classList.remove('printing-receipt');
            
            const receipt = document.getElementById('receipt');
            if (receipt) {
                receipt.classList.remove('print-mode');
            }
        });
        
        // Handle receipt responsive styling
        function adjustReceiptStyles() {
            const receipt = document.getElementById('receipt');
            if (!receipt) return;
            
            if (window.innerWidth < 576) {
                // Extra small devices
                receipt.querySelectorAll('.table td, .table th').forEach(el => {
                    el.style.padding = '4px';
                    el.style.fontSize = '10px';
                });
            } else if (window.innerWidth < 768) {
                // Small devices
                receipt.querySelectorAll('.table td, .table th').forEach(el => {
                    el.style.padding = '6px';
                    el.style.fontSize = '12px';
                });
            } else {
                // Medium and larger devices
                receipt.querySelectorAll('.table td, .table th').forEach(el => {
                    el.style.padding = '8px';
                    el.style.fontSize = '14px';
                });
            }
        }
        
        // Adjust receipt styles on window resize
        window.addEventListener('resize', adjustReceiptStyles);
        
        // Initialize receipt styles when a modal is shown
        document.addEventListener('shown.bs.modal', function(e) {
            if (e.target.id === 'receiptModal') {
                adjustReceiptStyles();
            }
        });
    }

    // ===== NOTIFICATION SYSTEM =====
    function initializeNotificationSystem() {
        // Create a global notification function
        window.showNotification = function(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} notification`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            // Trigger animation
            setTimeout(() => {
                notification.classList.add('show');
            }, 100);
            
            // Remove notification after 5 seconds
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 5000);
        };
    }

    // Initialize new functions
    initializeCartFunctions();
    initializeReceiptFunctions();
    initializeNotificationSystem();
});
