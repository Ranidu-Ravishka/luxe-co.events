<?php
require_once 'includes/config.php';

try {
    // Create paypal_processing table
    $sql = "CREATE TABLE IF NOT EXISTS paypal_processing (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id VARCHAR(255) NOT NULL UNIQUE,
        user_id INT,
        booking_ids TEXT,
        status ENUM('processing', 'completed', 'failed') DEFAULT 'processing',
        attempts INT DEFAULT 1,
        last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        completed_at TIMESTAMP NULL,
        transaction_id VARCHAR(255),
        error_message TEXT,
        FOREIGN KEY (user_id) REFERENCES users(id),
        INDEX idx_order_status (order_id, status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($sql)) {
        echo "PayPal processing table created successfully\n";
    } else {
        throw new Exception("Error creating paypal_processing table: " . $conn->error);
    }
    
} catch (Exception $e) {
    die("Setup failed: " . $e->getMessage() . "\n");
}

echo "Setup completed successfully\n";
?> 