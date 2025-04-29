<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get login data from session if it exists
$loginEmail = isset($_SESSION['login_email']) ? $_SESSION['login_email'] : '';
$loginErrors = isset($_SESSION['login_errors']) ? $_SESSION['login_errors'] : [];

// Get registration data from session if it exists
$registrationData = isset($_SESSION['registration_data']) ? $_SESSION['registration_data'] : [];
$registrationErrors = isset($_SESSION['registration_errors']) ? $_SESSION['registration_errors'] : [];
$registrationSuccess = isset($_SESSION['registration_success']) ? $_SESSION['registration_success'] : '';

// Clear session data after retrieving it
unset($_SESSION['login_email']);
unset($_SESSION['login_errors']);
unset($_SESSION['registration_data']);
unset($_SESSION['registration_errors']);
unset($_SESSION['registration_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Registration - Wedding Planning System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Lora:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Loading Animation -->
    <div class="loading-screen">
        <div class="loading-content">
            <div class="loading-logo">
                <span class="logo-text">Luxe & Co.Events</span>
                <div class="logo-dot"></div>
            </div>
            <div class="loading-bar">
                <div class="loading-progress"></div>
            </div>
        </div>
    </div>

    <?php include_once('header.php'); ?>

    <!-- Main Content -->
    <main class="auth-page">
        <!-- Background Pattern -->
        <div class="auth-background">
            <div class="pattern-overlay"></div>
        </div>

        <div class="auth-container animate-on-scroll">
            <div class="auth-header">
                <div class="auth-logo">
                    <i class="fas fa-heart"></i>
                </div>
                <h1 class="auth-title">Welcome Back</h1>
                <p class="auth-subtitle">Sign in to continue to your account</p>
            </div>
            
            <div class="auth-tabs">
                <div class="auth-tab active" data-tab="login">Login</div>
                <div class="auth-tab" data-tab="register">Register</div>
            </div>

            <!-- Login Form -->
            <div class="form-section active" id="login-section">
                <form class="auth-form" id="login-form" action="process_login.php" method="POST">
                    <!-- Login Error Messages -->
                    <?php if (!empty($loginErrors)): ?>
                    <div class="alert alert-danger">
                        <?php echo implode('<br>', array_map('htmlspecialchars', $loginErrors)); ?>
                    </div>
                    <?php endif; ?>

                    <div class="form-group mb-3">
                        <label for="login-email" class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" id="login-email" name="email" placeholder="Enter your email" required value="<?php echo htmlspecialchars($loginEmail); ?>">
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label for="login-password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="login-password" name="password" placeholder="Enter your password" required>
                            <button type="button" class="btn btn-outline-secondary password-toggle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="remember-me" name="remember">
                        <label class="form-check-label" for="remember-me">Remember me</label>
                    </div>
                    <button type="submit" class="btn btn-auth w-100">
                        <span class="btn-text">Login</span>
                        <span class="btn-loader"></span>
                    </button>
                    
                    <div class="form-footer">
                        <a href="#" class="forgot-password">Forgot Password?</a>
                    </div>
                </form>
            </div>

            <!-- Registration Form -->
            <div class="form-section" id="register-section">
                <form class="auth-form" id="register-form" action="process_registration.php" method="POST">
                    <!-- Registration Success Message -->
                    <?php if (!empty($registrationSuccess)): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($registrationSuccess); ?>
                    </div>
                    <?php endif; ?>

                    <!-- Registration Error Messages -->
                    <?php if (!empty($registrationErrors)): ?>
                    <div class="alert alert-danger">
                        <?php echo implode('<br>', array_map('htmlspecialchars', $registrationErrors)); ?>
                    </div>
                    <?php endif; ?>

                    <!-- Full Name -->
                    <div class="form-group mb-3">
                        <label for="full-name" class="form-label">Full Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="full-name" name="full_name" placeholder="Enter your full name" required value="<?php echo isset($registrationData['full_name']) ? htmlspecialchars($registrationData['full_name']) : ''; ?>">
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="form-group mb-3">
                        <label for="register-email" class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" id="register-email" name="email" placeholder="Enter your email" required value="<?php echo isset($registrationData['email']) ? htmlspecialchars($registrationData['email']) : ''; ?>">
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="form-group mb-3">
                        <label for="register-password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="register-password" name="password" placeholder="Create a password" required minlength="6">
                            <button type="button" class="btn btn-outline-secondary password-toggle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="form-group mb-3">
                        <label for="confirm-password" class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="confirm-password" name="confirm_password" placeholder="Confirm your password" required minlength="6">
                            <button type="button" class="btn btn-outline-secondary password-toggle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Terms & Conditions -->
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="terms" name="agree_terms" required>
                        <label class="form-check-label" for="terms">
                            I agree to the <a href="#" class="terms-link">Terms & Conditions</a>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-auth w-100">
                        <span class="btn-text">Register</span>
                        <span class="btn-loader"></span>
                    </button>
                    
                    <div class="form-footer">
                        Already have an account? <a href="#" class="switch-tab" data-tab="login">Login</a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <?php include_once('footer.php'); ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../assets/js/main.js"></script>

    <script>
    document.getElementById('register-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.querySelector('.btn-text').style.display = 'none';
        submitBtn.querySelector('.btn-loader').style.display = 'inline-block';
        
        // Submit the form
        this.submit();
    });

    // Password visibility toggle
    document.querySelectorAll('.password-toggle').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    // Handle tab switching
    document.querySelectorAll('.auth-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active class from all tabs
            document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Hide all form sections
            document.querySelectorAll('.form-section').forEach(section => {
                section.classList.remove('active');
            });
            
            // Show the selected form section
            const targetSection = document.getElementById(this.dataset.tab + '-section');
            if (targetSection) {
                targetSection.classList.add('active');
            }
        });
    });

    // Handle tab switching from links
    document.querySelectorAll('.switch-tab').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const tab = document.querySelector(`.auth-tab[data-tab="${this.dataset.tab}"]`);
            if (tab) {
                tab.click();
            }
        });
    });
    </script>
</body>
</html>
 