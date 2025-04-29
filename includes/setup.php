<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/config.php';

// Admin account details
$admin = [
    'username' => 'admin',
    'password' => 'admin123',
    'email' => 'admin@wedding.com',
    'full_name' => 'System Administrator',
    'role' => 'admin'
];

try {
    // First, check if the users table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'users'");
    if ($table_check->num_rows == 0) {
        // Create users table if it doesn't exist
        $conn->query("CREATE TABLE IF NOT EXISTS users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            role ENUM('admin', 'user') DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        echo "<div style='color: green;'>Users table created successfully.</div>";
    }

    // Check if admin_users table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'admin_users'");
    if ($table_check->num_rows == 0) {
        // Create admin_users table if it doesn't exist
        $conn->query("CREATE TABLE IF NOT EXISTS admin_users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            is_active TINYINT(1) DEFAULT 1,
            last_login DATETIME DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        echo "<div style='color: green;'>Admin users table created successfully.</div>";
    }

    // Check if services table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'services'");
    if ($table_check->num_rows == 0) {
        // Create services table if it doesn't exist
        $conn->query("CREATE TABLE IF NOT EXISTS services (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            image VARCHAR(255),
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        echo "<div style='color: green;'>Services table created successfully.</div>";
    }

    // Check if bookings table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'bookings'");
    if ($table_check->num_rows == 0) {
        // Create bookings table if it doesn't exist
        $conn->query("CREATE TABLE IF NOT EXISTS bookings (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            service_id INT NOT NULL,
            booking_date DATE NOT NULL,
            event_date DATE NOT NULL,
            guest_count INT NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL,
            status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (service_id) REFERENCES services(id)
        )");
        echo "<div style='color: green;'>Bookings table created successfully.</div>";
    }

    // Check if wedding_planners table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'wedding_planners'");
    if ($table_check->num_rows == 0) {
        // Create wedding_planners table if it doesn't exist
        $conn->query("CREATE TABLE IF NOT EXISTS wedding_planners (
            id INT PRIMARY KEY AUTO_INCREMENT,
            full_name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            phone VARCHAR(20) NOT NULL,
            bio TEXT,
            experience_years INT,
            specialties TEXT,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        echo "<div style='color: green;'>Wedding planners table created successfully.</div>";
    }

    // Check if planner_assignments table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'planner_assignments'");
    if ($table_check->num_rows == 0) {
        // Create planner_assignments table if it doesn't exist
        $conn->query("CREATE TABLE IF NOT EXISTS planner_assignments (
            id INT PRIMARY KEY AUTO_INCREMENT,
            planner_id INT NOT NULL,
            booking_id INT NOT NULL,
            status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
            assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (planner_id) REFERENCES wedding_planners(id),
            FOREIGN KEY (booking_id) REFERENCES bookings(id)
        )");
        echo "<div style='color: green;'>Planner assignments table created successfully.</div>";
    }

    // Generate password hash
    $hashed_password = password_hash($admin['password'], PASSWORD_DEFAULT);

    // Create/Update admin in users table
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $admin['username'], $admin['email']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        // Create new admin account in users table
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", 
            $admin['username'], 
            $admin['email'], 
            $hashed_password, 
            $admin['full_name'], 
            $admin['role']
        );
        
        if ($stmt->execute()) {
            echo "<div style='color: green; margin: 20px 0;'>Admin account created in users table successfully!</div>";
        } else {
            throw new Exception("Error creating admin account in users table: " . $stmt->error);
        }
    } else {
        // Update existing admin account in users table
        $stmt = $conn->prepare("UPDATE users SET password = ?, role = 'admin' WHERE username = ?");
        $stmt->bind_param("ss", $hashed_password, $admin['username']);
        
        if ($stmt->execute()) {
            echo "<div style='color: green; margin: 20px 0;'>Admin account updated in users table successfully!</div>";
        } else {
            throw new Exception("Error updating admin account in users table: " . $stmt->error);
        }
    }

    // Create/Update admin in admin_users table
    $stmt = $conn->prepare("SELECT id FROM admin_users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $admin['username'], $admin['email']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        // Create new admin account in admin_users table
        $stmt = $conn->prepare("INSERT INTO admin_users (username, email, password, full_name, is_active) VALUES (?, ?, ?, ?, 1)");
        $stmt->bind_param("ssss", 
            $admin['username'], 
            $admin['email'], 
            $hashed_password, 
            $admin['full_name']
        );
        
        if ($stmt->execute()) {
            echo "<div style='color: green; margin: 20px 0;'>Admin account created in admin_users table successfully!</div>";
        } else {
            throw new Exception("Error creating admin account in admin_users table: " . $stmt->error);
        }
    } else {
        // Update existing admin account in admin_users table
        $stmt = $conn->prepare("UPDATE admin_users SET password = ?, is_active = 1 WHERE username = ?");
        $stmt->bind_param("ss", $hashed_password, $admin['username']);
        
        if ($stmt->execute()) {
            echo "<div style='color: green; margin: 20px 0;'>Admin account updated in admin_users table successfully!</div>";
        } else {
            throw new Exception("Error updating admin account in admin_users table: " . $stmt->error);
        }
    }

    // Verify the admin accounts
    echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4 style='margin-bottom: 15px;'>Admin Account Details:</h4>";
    echo "<strong>Username:</strong> " . htmlspecialchars($admin['username']) . "<br>";
    echo "<strong>Password:</strong> " . htmlspecialchars($admin['password']) . "<br>";
    echo "<strong>Email:</strong> " . htmlspecialchars($admin['email']) . "<br>";
    echo "<strong>Full Name:</strong> " . htmlspecialchars($admin['full_name']) . "<br>";
    echo "</div>";

    echo "<div style='margin: 20px 0;'>";
    echo "<a href='login.php' style='display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Go to Login Page</a>";
    echo "</div>";

} catch (Exception $e) {
    die("<div style='color: red; margin: 20px 0;'>Error: " . $e->getMessage() . "</div>");
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
}
?> 