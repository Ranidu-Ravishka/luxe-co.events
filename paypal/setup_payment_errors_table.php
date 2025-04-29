<?php
require_once 'includes/config.php';

try {
    // Create payment_errors table
    $sql = "CREATE TABLE IF NOT EXISTS payment_errors (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        order_id VARCHAR(255),
        error_message TEXT,
        booking_ids TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($sql)) {
        echo "Payment errors table created successfully\n";
    } else {
        throw new Exception("Error creating payment_errors table: " . $conn->error);
    }
    
    // Create index on order_id for faster lookups
    $sql = "CREATE INDEX idx_order_id ON payment_errors(order_id)";
    $conn->query($sql);
    
} catch (Exception $e) {
    die("Setup failed: " . $e->getMessage() . "\n");
}

echo "Setup completed successfully\n";
?> 