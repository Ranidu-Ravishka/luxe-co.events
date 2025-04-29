<?php
session_start();
require_once 'config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create a log file for debugging
$log_file = __DIR__ . '/registration_log.txt';
file_put_contents($log_file, "Registration attempt at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
file_put_contents($log_file, "POST data: " . print_r($_POST, true) . "\n", FILE_APPEND);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $agree_terms = isset($_POST['agree_terms']);
    
    // Log received data
    file_put_contents($log_file, "Received data:\n", FILE_APPEND);
    file_put_contents($log_file, "Full Name: $full_name\n", FILE_APPEND);
    file_put_contents($log_file, "Email: $email\n", FILE_APPEND);
    
    // Generate username from full name and random number
    $username = strtolower(str_replace(' ', '', $full_name)) . rand(100, 999);
    
    $errors = [];
    
    // Validate inputs
    if (empty($full_name)) {
        $errors[] = "Full name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (!$agree_terms) {
        $errors[] = "You must agree to the Terms & Conditions";
    }
    
    file_put_contents($log_file, "Validation errors: " . (!empty($errors) ? implode(", ", $errors) : "none") . "\n", FILE_APPEND);
    
    if (empty($errors)) {
        try {
            // Verify database connection
            if ($conn->connect_error) {
                throw new Exception("Database connection failed: " . $conn->connect_error);
            }
            file_put_contents($log_file, "Database connection successful\n", FILE_APPEND);
            
            // First, create the users table if it doesn't exist
            $create_table_sql = "CREATE TABLE IF NOT EXISTS users (
                id INT PRIMARY KEY AUTO_INCREMENT,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                full_name VARCHAR(100) NOT NULL,
                role ENUM('admin', 'user') DEFAULT 'user',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )";
            
            if (!$conn->query($create_table_sql)) {
                throw new Exception("Error creating users table: " . $conn->error);
            }
            
            // Check if email already exists
            $check_email = "SELECT * FROM users WHERE email = ?";
            $stmt = $conn->prepare($check_email);
            if ($stmt === false) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }
            
            $stmt->bind_param("s", $email);
            if (!$stmt->execute()) {
                throw new Exception("Email check failed: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $errors[] = "Email already exists";
                file_put_contents($log_file, "Email already exists: $email\n", FILE_APPEND);
            }
            $stmt->close();
            
            if (empty($errors)) {
                // Check if username exists and generate new one if it does
                $check_username = "SELECT * FROM users WHERE username = ?";
                $stmt = $conn->prepare($check_username);
                if ($stmt === false) {
                    throw new Exception("Prepare statement failed: " . $conn->error);
                }
                
                $stmt->bind_param("s", $username);
                if (!$stmt->execute()) {
                    throw new Exception("Username check failed: " . $stmt->error);
                }
                
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $username = strtolower(str_replace(' ', '', $full_name)) . rand(1000, 9999);
                    file_put_contents($log_file, "Generated new username: $username\n", FILE_APPEND);
                }
                $stmt->close();
                
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert user into database
                $sql = "INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, 'user')";
                $stmt = $conn->prepare($sql);
                if ($stmt === false) {
                    throw new Exception("Prepare insert statement failed: " . $conn->error);
                }
                
                $stmt->bind_param("ssss", $username, $email, $hashed_password, $full_name);
                if ($stmt->execute()) {
                    file_put_contents($log_file, "User registered successfully\n", FILE_APPEND);
                    $_SESSION['registration_success'] = "Registration successful! Your username is: " . $username;
                    header("Location: login & registration.php");
                    exit();
                } else {
                    throw new Exception("Insert failed: " . $stmt->error);
                }
                $stmt->close();
            }
        } catch (Exception $e) {
            $errors[] = "An error occurred: " . $e->getMessage();
            file_put_contents($log_file, "Error: " . $e->getMessage() . "\n", FILE_APPEND);
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['registration_errors'] = $errors;
        $_SESSION['registration_data'] = [
            'full_name' => $full_name,
            'email' => $email
        ];
        file_put_contents($log_file, "Registration failed with errors: " . implode(", ", $errors) . "\n", FILE_APPEND);
        header("Location: login & registration.php");
        exit();
    }
} else {
    header("Location: login & registration.php");
    exit();
}
?> 