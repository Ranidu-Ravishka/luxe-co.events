<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$host = 'localhost';
$dbname = 'wedding_planning';
$username = 'root';
$password = '';

// Create database connection
try {
    $conn = new mysqli($host, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Set charset to utf8mb4
    if (!$conn->set_charset("utf8mb4")) {
        throw new Exception("Error setting charset: " . $conn->error);
    }
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check if user is admin
function isAdmin() {
    return isset($_SESSION['admin']['is_logged_in']) && $_SESSION['admin']['is_logged_in'] === true;
}

// Function to get current user data
function getCurrentUser() {
    global $conn;
    if (!isLoggedIn()) {
        return null;
    }
    
    $stmt = $conn->prepare("SELECT id, username, email, full_name, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Function to get current admin data
function getCurrentAdmin() {
    return isset($_SESSION['admin']) ? $_SESSION['admin'] : null;
}

// Function to sanitize input
function sanitize($input) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($input));
}

// Function to redirect if not admin
function requireAdmin() {
    if (!isAdmin()) {
        // Get the current script path
        $current_path = $_SERVER['SCRIPT_NAME'];
        
        // If we're in the admin area, redirect to admin login
        if (strpos($current_path, '/admin/') !== false) {
            header("Location: login.php");
        } else {
            header("Location: ../admin/login.php");
        }
        exit();
    }
}

// Function to log PayPal errors
function logPayPalError($message, $data = []) {
    global $conn;
    
    // Create logs directory if it doesn't exist
    $log_dir = __DIR__ . '/../logs';
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    // Format the log message
    $log_message = date('Y-m-d H:i:s') . ' - ' . $message;
    if (!empty($data)) {
        $log_message .= ' - ' . json_encode($data);
    }
    $log_message .= PHP_EOL;
    
    // Write to log file
    $log_file = $log_dir . '/paypal_errors.log';
    file_put_contents($log_file, $log_message, FILE_APPEND);
    
    // Also log to database if payment_errors table exists
    try {
        $check_table = $conn->query("SHOW TABLES LIKE 'payment_errors'");
        if ($check_table->num_rows > 0) {
            $order_id = $data['order_id'] ?? 'N/A';
            $error_message = $message;
            $booking_ids_json = isset($data['booking_ids']) ? json_encode($data['booking_ids']) : '[]';
            $user_id = $_SESSION['user_id'] ?? 0;
            
            $stmt = $conn->prepare("
                INSERT INTO payment_errors (
                    user_id,
                    order_id,
                    error_message,
                    booking_ids,
                    created_at
                ) VALUES (?, ?, ?, ?, NOW())
            ");
            
            $stmt->bind_param(
                "isss",
                $user_id,
                $order_id,
                $error_message,
                $booking_ids_json
            );
            
            $stmt->execute();
        }
    } catch (Exception $e) {
        // Silently fail if database logging fails
        error_log("Failed to log PayPal error to database: " . $e->getMessage());
    }
}
?> 