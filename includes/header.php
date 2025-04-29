<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, minimum-scale=1.0, viewport-fit=cover">
    <title>Luxe & Co. Events - Wedding Planning</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Lora:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
    <!-- Slick Slider CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css">
    <!-- Magnific Popup CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/WeddinPlaning/assets/css/style.css">
    <style>
        /* Navigation Styles */
        .navbar {
            background-color: #ffffff !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 1rem 0;
            display: flex;
            justify-content: center;
        }

        .navbar-brand {
            color: #333 !important;
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }

        .navbar-brand img {
            height: 70px;
            margin-right: 10px;
        }

        .navbar-brand:hover {
            transform: scale(1.05);
        }

        .nav-link {
            color: #666 !important;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            font-size: 1rem;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem !important;
            position: relative;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: #ff4081;
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .nav-link:hover::after,
        .nav-link.active::after {
            width: 100%;
        }

        .nav-link:hover, 
        .nav-link.active {
            color: #ff4081 !important;
        }

        /* Navigation Items */
        .nav-item {
            position: relative;
        }

        .nav-item::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 50%;
            width: 0;
            height: 2px;
            background: #ff4081;
            transition: all 0.3s ease;
            transform: translateX(-50%);
            display: none; /* Hide the default underline */
        }

        .nav-item:hover::after,
        .nav-item.active::after {
            width: 0; /* Remove the nav-item underline */
        }

        /* Start Planning Button */
        .btn-start-planning {
            background-color: #ff4081;
            color: white !important;
            border-radius: 25px;
            padding: 0.5rem 1.5rem !important;
            font-weight: 500;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-start-planning:hover {
            background-color: #f50057;
            transform: translateY(-2px);
        }

        /* Login Button */
        .btn-outline-primary {
            border-color: #ff4081;
            color: #ff4081;
            border-radius: 25px;
            padding: 0.5rem 1.5rem !important;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-outline-primary:hover {
            background-color: #ff4081;
            border-color: #ff4081;
            color: white !important;
            transform: translateY(-2px);
        }

        /* Cart Icon */
        .cart-icon {
            color: #666;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            margin: 0 1rem;
            position: relative;
        }

        .cart-icon:hover {
            color: #ff4081;
        }

        /* User Avatar */
        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #ff4081;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            font-weight: 500;
            margin-left: 1rem;
            cursor: pointer;
            text-decoration: none;
        }

        /* Account Dropdown */
        .account-dropdown {
            min-width: 200px;
            padding: 0.5rem 0;
            margin-top: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border: none;
            border-radius: 10px;
        }

        /* Form Steps Styles */
        .form-steps {
            position: relative;
        }

        .form-step {
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .form-step.active {
            display: block;
            opacity: 1;
        }

        .step-title {
            color: #333;
            font-family: 'Playfair Display', serif;
            margin-bottom: 1.5rem;
            position: relative;
        }

        .step-title::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 50px;
            height: 2px;
            background-color: #ff4081;
        }

        .progress {
            height: 8px !important;
            background-color: #f5f5f5;
            border-radius: 4px;
            margin-bottom: 2rem;
        }

        .progress-bar.bg-pink {
            background-color: #ff4081 !important;
            transition: width 0.3s ease;
        }

        /* Form Navigation Buttons */
        #prevBtn, #nextBtn, #submitBtn {
            min-width: 120px;
            border-radius: 25px;
            padding: 8px 24px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        #prevBtn {
            background-color: #6c757d;
            border-color: #6c757d;
        }

        #nextBtn {
            background-color: #ff4081;
            border-color: #ff4081;
        }

        #submitBtn {
            background-color: #28a745;
            border-color: #28a745;
        }

        #prevBtn:hover, #nextBtn:hover, #submitBtn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        /* Services Dropdown Styling */
        .dropdown-menu {
            min-width: 200px;
            padding: 0.5rem 0;
            margin-top: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border: none;
            border-radius: 10px;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .dropdown-item {
            padding: 0.6rem 1rem;
            font-size: 0.9rem;
            color: #666;
            transition: all 0.3s ease;
        }

        .dropdown-item:hover {
            background-color: #fff0f5;
            color: #ff4081;
        }

        .dropdown-divider {
            margin: 0.5rem 0;
            border-color: #f5f5f5;
        }

        .account-dropdown .dropdown-item i {
            width: 20px;
            text-align: center;
            color: #ff4081;
        }

        .account-dropdown .dropdown-divider {
            margin: 0.5rem 0;
            border-color: #f5f5f5;
        }

        .account-dropdown .text-danger:hover {
            background-color: #ffe0e0;
        }

        .account-dropdown .text-danger:hover i {
            color: #dc3545;
        }

        /* Responsive Styles */
        @media (max-width: 991.98px) {
            .navbar-nav {
                padding: 1rem 0;
            }
            .nav-item::after {
                display: none;
            }
            .btn-start-planning {
                margin-top: 1rem;
            }
            .user-avatar {
                margin: 1rem 0;
            }
        }

        .navbar-nav {
            align-items: center;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
        }

        .dropdown-item.text-danger:hover {
            background-color: #dc3545;
            color: white !important;
        }

        .dropdown-item-text {
            padding: .5rem 1rem;
        }
        .dropdown-item i {
            width: 20px;
        }

        .user-avatar-container {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid #ff4081;
            transition: all 0.3s ease;
        }

        .user-avatar-container:hover {
            transform: scale(1.1);
            border-color: #f50057;
        }

        .user-avatar {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .dropdown-header {
            font-weight: 600;
            color: #ff4081;
        }

        .dropdown-item-text small {
            font-size: 0.75rem;
            color: #6c757d;
        }

        .dropdown-item.text-danger:hover i {
            color: white;
        }

        .nav-profile-image {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #ff4081;
        }
        
        .nav-link.profile-link {
            padding: 0;
            display: flex;
            align-items: center;
        }
    </style>
</head>
<body>
    <!-- Add smooth scrolling script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add smooth scrolling to all links that point to sections
        document.querySelectorAll('a[href*="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                // Only handle links to sections on the home page
                if (this.getAttribute('href').includes('index.php#')) {
                    const sectionId = this.getAttribute('href').split('#')[1];
                    const section = document.getElementById(sectionId);
                    
                    if (section) {
                        e.preventDefault();
                        window.scrollTo({
                            top: section.offsetTop - 80, // Adjust for fixed header
                            behavior: 'smooth'
                        });
                    }
                }
            });
        });
    });
    </script>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3" role="alert" style="z-index: 1050;">
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show position-fixed top-0 end-0 m-3" role="alert" style="z-index: 1050;">
            <?php 
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="/WeddinPlaning/pages/index.php">
                <img src="/WeddinPlaning/assets/images/logo.png" alt="Luxe & Co. Events Logo">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="/WeddinPlaning/pages/index.php">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Services
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/WeddinPlaning/pages/services.php">All Services</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php
                            require_once(__DIR__ . '/../includes/config.php');
                            // Fetch active service categories
                            $query = "SELECT id, name FROM service_categories WHERE status = 'active' ORDER BY name ASC";
                            $stmt = $conn->prepare($query);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            while ($category = $result->fetch_assoc()) {
                                echo '<li><a class="dropdown-item" href="/WeddinPlaning/pages/category-services.php?id=' . 
                                     htmlspecialchars($category['id']) . '">' . 
                                     htmlspecialchars($category['name']) . '</a></li>';
                            }
                            $stmt->close();
                            ?>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/WeddinPlaning/pages/index.php#gallery">Gallery</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/WeddinPlaning/pages/index.php#testimonials">Testimonials</a>
                    </li>
                    <li class="nav-item">
                        <a href="/WeddinPlaning/pages/cart.php" class="cart-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="btn btn-start-planning" data-bs-toggle="modal" data-bs-target="#bridalInfoModal">
                            Start Planning
                        </a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a href="#" class="user-avatar" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php 
                            $email = isset($_SESSION['email']) ? $_SESSION['email'] : '';
                            echo strtoupper(substr($email, 0, 1));
                            ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end account-dropdown">
                            <li>
                                <a class="dropdown-item d-flex align-items-center" href="/WeddinPlaning/pages/account.php#profile">
                                    <i class="fas fa-user me-2"></i>
                                    <span>Profile</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center" href="/WeddinPlaning/pages/account.php#bookings">
                                    <i class="fas fa-calendar-check me-2"></i>
                                    <span>My Bookings</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center" href="/WeddinPlaning/pages/account.php#payments">
                                    <i class="fas fa-credit-card me-2"></i>
                                    <span>Payments</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center" href="/WeddinPlaning/pages/account.php#settings">
                                    <i class="fas fa-cog me-2"></i>
                                    <span>Settings</span>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center text-danger" href="/WeddinPlaning/pages/logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>
                                    <span>Logout</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a href="/WeddinPlaning/includes/login & registration.php" class="btn btn-outline-primary ms-2">Login</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Bridal Information Modal -->
    <div class="modal fade" id="bridalInfoModal" tabindex="-1" aria-labelledby="bridalInfoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" id="bridalInfoModalLabel">Let's Plan Your Dream Wedding</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="bridalInfoForm" class="needs-validation" novalidate action="/WeddinPlaning/includes/process_planning.php" method="POST" enctype="multipart/form-data">
                        <!-- Progress Bar -->
                        <div class="progress mb-4" style="height: 5px;">
                            <div class="progress-bar bg-pink" role="progressbar" style="width: 0%;" id="formProgress"></div>
                        </div>

                        <!-- Form Steps -->
                        <div class="form-steps">
                            <!-- Step 1: Basic Information -->
                            <div class="form-step" data-step="1">
                                <h4 class="step-title mb-4">Basic Information</h4>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Bride's Name</label>
                                        <input type="text" class="form-control" name="brideName" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Groom's Name</label>
                                        <input type="text" class="form-control" name="groomName" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Bride's Age</label>
                                        <input type="number" class="form-control" name="brideAge" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Groom's Age</label>
                                        <input type="number" class="form-control" name="groomAge" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Wedding Date</label>
                                        <input type="date" class="form-control" name="weddingDate" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Venue Preference</label>
                                        <input type="text" class="form-control" name="venuePreference" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 2: Guest Information -->
                            <div class="form-step" data-step="2">
                                <h4 class="step-title mb-4">Guest Information</h4>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Expected Number of Guests</label>
                                        <input type="number" class="form-control" name="guestCount" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Guest List Spreadsheet</label>
                                        <input type="file" class="form-control" name="guestList" accept=".xlsx,.xls,.csv">
                                        <small class="text-muted">Upload Excel or CSV file</small>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Special Guest Considerations</label>
                                        <textarea class="form-control" name="guestConsiderations" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 3: Budget & Documents -->
                            <div class="form-step" data-step="3">
                                <h4 class="step-title mb-4">Budget & Documents</h4>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Budget Range</label>
                                        <select class="form-select" name="budgetRange" required>
                                            <option value="">Select Budget Range</option>
                                            <option value="10000-25000">$10,000 - $25,000</option>
                                            <option value="25000-50000">$25,000 - $50,000</option>
                                            <option value="50000-100000">$50,000 - $100,000</option>
                                            <option value="100000+">$100,000+</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Invitation Designs</label>
                                        <input type="file" class="form-control" name="invitationFiles[]" multiple accept=".pdf,.jpg,.png">
                                        <small class="text-muted">Upload PDF or Images</small>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Additional Notes</label>
                                        <textarea class="form-control" name="additionalNotes" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Navigation -->
                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-secondary" id="prevBtn" style="display: none;">Previous</button>
                            <button type="button" class="btn btn-primary" id="nextBtn">Next</button>
                            <button type="submit" class="btn btn-success" id="submitBtn" style="display: none;">Submit Plan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Function to add item to cart
    function addToCart(item) {
        const cartIcon = document.getElementById('cartIcon');
        const cartBadge = document.getElementById('cartBadge');
        
        // Add shake animation
        cartIcon.classList.add('shake');
        setTimeout(() => {
            cartIcon.classList.remove('shake');
        }, 820);
        
        // Update cart badge
        let currentCount = parseInt(cartBadge.textContent);
        cartBadge.textContent = currentCount + 1;
        cartBadge.style.display = 'flex';
        
        // Store cart data in localStorage
        let cartItems = JSON.parse(localStorage.getItem('cartItems')) || [];
        cartItems.push(item);
        localStorage.setItem('cartItems', JSON.stringify(cartItems));
    }

    // Initialize cart badge on page load
    document.addEventListener('DOMContentLoaded', function() {
        const cartItems = JSON.parse(localStorage.getItem('cartItems')) || [];
        const cartBadge = document.getElementById('cartBadge');
        if (cartItems.length > 0) {
            cartBadge.textContent = cartItems.length;
            cartBadge.style.display = 'flex';
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('bridalInfoForm');
        const steps = document.querySelectorAll('.form-step');
        const progressBar = document.getElementById('formProgress');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const submitBtn = document.getElementById('submitBtn');
        let currentStep = 1;

        // Update progress bar
        function updateProgress() {
            const progress = ((currentStep - 1) / (steps.length - 1)) * 100;
            progressBar.style.width = `${progress}%`;
        }

        // Show current step
        function showStep(step) {
            steps.forEach(s => s.classList.remove('active'));
            document.querySelector(`[data-step="${step}"]`).classList.add('active');
            
            // Update buttons
            prevBtn.style.display = step === 1 ? 'none' : 'block';
            nextBtn.style.display = step === steps.length ? 'none' : 'block';
            submitBtn.style.display = step === steps.length ? 'block' : 'none';
            
            updateProgress();
        }

        // Validate current step
        function validateStep(step) {
            const currentStepElement = document.querySelector(`[data-step="${step}"]`);
            const inputs = currentStepElement.querySelectorAll('input[required], select[required]');
            let isValid = true;

            inputs.forEach(input => {
                if (!input.value) {
                    isValid = false;
                    input.classList.add('is-invalid');
                    input.addEventListener('input', function() {
                        this.classList.remove('is-invalid');
                    });
                }
            });

            return isValid;
        }

        // Next button click
        nextBtn.addEventListener('click', function() {
            if (validateStep(currentStep)) {
                currentStep++;
                showStep(currentStep);
            }
        });

        // Previous button click
        prevBtn.addEventListener('click', function() {
            currentStep--;
            showStep(currentStep);
        });

        // Form submission
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (validateStep(currentStep)) {
                // Show loading state
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
                submitBtn.disabled = true;

                // Submit the form
                form.submit();
            }
        });

        // Initialize form
        showStep(currentStep);
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Auto-close alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });

        // Close modal if session variable is set
        <?php if (isset($_SESSION['close_modal'])): ?>
            const planningModal = document.getElementById('planningModal');
            if (planningModal) {
                const modal = bootstrap.Modal.getInstance(planningModal);
                if (modal) {
                    modal.hide();
                }
            }
            <?php unset($_SESSION['close_modal']); ?>
        <?php endif; ?>
    });
    </script>

    <!-- Main Content -->
    <main>
