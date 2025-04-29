<?php
require_once 'includes/config.php';

// Check bookings table structure
echo "Bookings Table Structure:\n";
$result = $conn->query("SHOW COLUMNS FROM bookings");
while ($row = $result->fetch_assoc()) {
    print_r($row);
    echo "\n";
}

echo "\nPayments Table Structure:\n";
$result = $conn->query("SHOW COLUMNS FROM payments");
while ($row = $result->fetch_assoc()) {
    print_r($row);
    echo "\n";
}

// Check for pending bookings
echo "\nPending Bookings:\n";
$result = $conn->query("SELECT * FROM bookings WHERE status = 'pending'");
while ($row = $result->fetch_assoc()) {
    print_r($row);
    echo "\n";
}
?> 