<?php
require_once '../includes/config.php';

// Default admin credentials
$username = 'admin';
$email = 'admin@wedding.com';
$password = 'admin123'; // Change this to your desired password
$full_name = 'System Admin';
$role = 'admin';

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Check if admin already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Insert new admin user
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $email, $hashed_password, $full_name, $role);
    
    if ($stmt->execute()) {
        echo "Admin account created successfully!<br>";
        echo "Username: " . htmlspecialchars($username) . "<br>";
        echo "Password: " . htmlspecialchars($password) . "<br>";
        echo "<a href='login.php'>Go to Login</a>";
    } else {
        echo "Error creating admin account: " . $stmt->error;
    }
} else {
    echo "Admin account already exists!<br>";
    echo "<a href='login.php'>Go to Login</a>";
}

$stmt->close(); 