<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'wedding_planning';

try {
    // Create connection
    $conn = new mysqli($host, $username, $password);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Create database if not exists
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    if ($conn->query($sql) === TRUE) {
        echo "Database created successfully or already exists<br>";
    } else {
        throw new Exception("Error creating database: " . $conn->error);
    }
    
    // Select the database
    $conn->select_db($dbname);
    
    // Disable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
    
    // Drop users table if exists
    $sql = "DROP TABLE IF EXISTS users";
    if ($conn->query($sql) === TRUE) {
        echo "Old users table dropped successfully<br>";
    }
    
    // Create users table
    $sql = "CREATE TABLE users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        role ENUM('admin', 'user') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if ($conn->query($sql) === TRUE) {
        echo "Users table created successfully<br>";
        
        // Create default admin user
        $admin_username = 'admin';
        $admin_email = 'admin@wedding.com';
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        $admin_fullname = 'System Administrator';
        
        $sql = "INSERT INTO users (username, email, password, full_name, role) 
                VALUES (?, ?, ?, ?, 'admin')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $admin_username, $admin_email, $admin_password, $admin_fullname);
        
        if ($stmt->execute()) {
            echo "Default admin user created successfully<br>";
            echo "Admin credentials:<br>";
            echo "Username: admin<br>";
            echo "Password: admin123<br>";
        } else {
            throw new Exception("Error creating admin user: " . $stmt->error);
        }
    } else {
        throw new Exception("Error creating users table: " . $conn->error);
    }
    
    // Re-enable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    
    // Verify users table structure
    $result = $conn->query("DESCRIBE users");
    if ($result === FALSE) {
        throw new Exception("Error verifying users table: " . $conn->error);
    }
    
    echo "<br>Users table structure:<br>";
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "<br>";
    }
    
} catch (Exception $e) {
    die("Setup failed: " . $e->getMessage());
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?> 