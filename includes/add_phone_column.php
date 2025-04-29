<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

try {
    // Add phone column if it doesn't exist
    $sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(20) DEFAULT NULL";
    
    if ($conn->query($sql)) {
        echo "Phone column added successfully or already exists.";
    } else {
        throw new Exception("Error adding phone column: " . $conn->error);
    }
    
    // Verify the column was added
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'phone'");
    if ($result->num_rows > 0) {
        echo "\nPhone column exists in users table.";
    } else {
        echo "\nWarning: Phone column was not found after attempting to add it.";
    }
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

$conn->close();
?> 