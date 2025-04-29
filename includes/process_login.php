<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $errors = [];
    
    // Validate inputs
    if (empty($email)) {
        $errors[] = "Email is required";
    }
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    if (empty($errors)) {
        // Check user credentials - explicitly exclude admin users
        $sql = "SELECT * FROM users WHERE email = ? AND role != 'admin'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                // Only clear user-related session data
                $admin_data = array();
                if (isset($_SESSION['admin_id'])) $admin_data['admin_id'] = $_SESSION['admin_id'];
                if (isset($_SESSION['admin_username'])) $admin_data['admin_username'] = $_SESSION['admin_username'];
                if (isset($_SESSION['admin_fullname'])) $admin_data['admin_fullname'] = $_SESSION['admin_fullname'];
                if (isset($_SESSION['is_admin'])) $admin_data['is_admin'] = $_SESSION['is_admin'];
                
                // Clear session but preserve admin data
                session_unset();
                
                // Restore admin session data if it existed
                foreach ($admin_data as $key => $value) {
                    $_SESSION[$key] = $value;
                }
                
                // Set user session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['logged_in'] = true;
                
                // Set a cookie for persistent login if "Remember me" is checked
                if (isset($_POST['remember_me']) && $_POST['remember_me'] == 'on') {
                    $token = bin2hex(random_bytes(32));
                    setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/');
                    
                    // Store the token in the database
                    $update_sql = "UPDATE users SET remember_token = ? WHERE id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("si", $token, $user['id']);
                    $update_stmt->execute();
                }
                
                // Redirect to account page for regular users
                header("Location: ../pages/account.php");
                exit();
            } else {
                $errors[] = "Invalid password";
            }
        } else {
            $errors[] = "Email not found";
        }
        $stmt->close();
    }
    
    if (!empty($errors)) {
        $_SESSION['login_errors'] = $errors;
        $_SESSION['login_email'] = $email;
        header("Location: login & registration.php");
        exit();
    }
} else {
    header("Location: login & registration.php");
    exit();
}
?> 