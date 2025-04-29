<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/config.php';

// Check if already logged in as admin
if (isset($_SESSION['admin']['is_logged_in']) && $_SESSION['admin']['is_logged_in'] === true) {
    header("Location: index.php");
    exit();
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        try {
            // First try admin_users table
            $stmt = $conn->prepare("SELECT id, username, password, full_name, is_active FROM admin_users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $admin = $result->fetch_assoc();
                
                // Check if account is active
                if ($admin['is_active'] != 1) {
                    $error = 'This admin account has been deactivated';
                }
                // Verify password
                else if (password_verify($password, $admin['password'])) {
                    // Update last login time
                    $update_stmt = $conn->prepare("UPDATE admin_users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
                    $update_stmt->bind_param("i", $admin['id']);
                    $update_stmt->execute();
                    $update_stmt->close();
                    
                    // Set admin session variables
                    $_SESSION['admin'] = [
                        'id' => $admin['id'],
                        'username' => $admin['username'],
                        'full_name' => $admin['full_name'],
                        'is_logged_in' => true,
                        'table' => 'admin_users'
                    ];
                    
                    header("Location: index.php");
                    exit();
                } else {
                    $error = 'Invalid password';
                }
            } else {
                // Try users table if not found in admin_users
                $stmt = $conn->prepare("SELECT id, username, password, full_name, role FROM users WHERE username = ? AND role = 'admin'");
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 1) {
                    $admin = $result->fetch_assoc();
                    
                    // Verify password
                    if (password_verify($password, $admin['password'])) {
                        // Set admin session variables
                        $_SESSION['admin'] = [
                            'id' => $admin['id'],
                            'username' => $admin['username'],
                            'full_name' => $admin['full_name'],
                            'is_logged_in' => true,
                            'table' => 'users'
                        ];
                        
                        header("Location: index.php");
                        exit();
                    } else {
                        $error = 'Invalid password';
                    }
                } else {
                    $error = 'Admin account not found';
                }
            }
            $stmt->close();
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Wedding Planning System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --dark-bg: #212529;
            --darker-bg: #1a1d20;
            --primary-color: #4e73df;
            --primary-dark: #2e59d9;
            --secondary-color: #858796;
            --card-bg: #2c3034;
            --text-primary: #ffffff;
            --text-secondary: #d1d3e2;
        }

        body {
            background-color: var(--dark-bg);
            background-image: linear-gradient(180deg, var(--dark-bg) 10%, var(--darker-bg) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .login-container {
            max-width: 400px;
            margin: 0 auto;
            width: 100%;
        }

        .card {
            border: none;
            background-color: var(--card-bg);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.2);
        }

        .card-body {
            background-color: var(--card-bg);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .form-control {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: var(--text-primary);
        }

        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.25);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
            color: var(--text-primary);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .reset-link {
            color: var(--primary-color);
            transition: color 0.15s ease-in-out;
        }

        .reset-link:hover {
            color: var(--primary-dark);
        }

        .back-link {
            color: rgba(255, 255, 255, 0.8);
            transition: color 0.15s ease-in-out;
        }

        .back-link:hover {
            color: #fff;
        }

        h3 {
            color: var(--text-primary);
            font-weight: 700;
        }

        .form-label {
            color: var(--text-secondary);
            font-weight: 600;
        }

        .alert {
            border: none;
            border-left: 4px solid;
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--text-primary);
        }

        .alert-danger {
            border-left-color: #e74a3b;
            background-color: rgba(231, 74, 59, 0.1);
        }

        /* Dark theme form autofill override */
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 30px var(--card-bg) inset !important;
            -webkit-text-fill-color: var(--text-primary) !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="card">
                <div class="card-body p-5">
                    <h3 class="text-center mb-4">Admin Login</h3>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="" autocomplete="off">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="username" 
                                   name="username" 
                                   required 
                                   autocomplete="off"
                                   placeholder="Enter admin username">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   required
                                   autocomplete="off"
                                   placeholder="Enter your password">
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mb-3">Login</button>
                        <div class="text-center">
                            <a href="setup.php" class="reset-link text-decoration-none">Reset Admin Account</a>
                        </div>
                    </form>
                </div>
            </div>
            <div class="text-center mt-3">
                <a href="../index.php" class="back-link text-decoration-none">
                    <i class="fas fa-arrow-left me-1"></i> Back to Website
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
</body>
</html> 