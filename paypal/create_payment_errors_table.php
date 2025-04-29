<?php
require_once 'includes/config.php';

// SQL to create payment_errors table
$sql = "CREATE TABLE IF NOT EXISTS payment_errors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    order_id VARCHAR(255) NOT NULL,
    error_message TEXT,
    booking_ids TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

if ($conn->query($sql) === TRUE) {
    echo "payment_errors table created successfully\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

// Add paypal_order_id column to payments table if it doesn't exist
$sql = "SHOW COLUMNS FROM payments LIKE 'paypal_order_id'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    $sql = "ALTER TABLE payments ADD COLUMN paypal_order_id VARCHAR(255) NULL";
    if ($conn->query($sql) === TRUE) {
        echo "paypal_order_id column added to payments table successfully\n";
    } else {
        echo "Error adding column: " . $conn->error . "\n";
    }
} else {
    echo "paypal_order_id column already exists in payments table\n";
}
?> 