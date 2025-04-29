<?php
require_once 'includes/config.php';

$order_id = '3EC93945PF675135H';
$capture_id = '78J50825X7857825T';

// Check payments table
$sql = "SELECT p.*, b.status as booking_status, b.total_amount as booking_amount 
        FROM payments p 
        LEFT JOIN bookings b ON p.booking_id = b.id 
        WHERE p.transaction_id = ? OR p.paypal_order_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $capture_id, $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<h2>Found Payment Records:</h2>";
    while ($row = $result->fetch_assoc()) {
        echo "<pre>";
        print_r($row);
        echo "</pre>";
    }
} else {
    echo "<h2>No Payment Records Found</h2>";
    echo "<p>The payment was successful in PayPal but not recorded in the database.</p>";
    
    // Check for pending bookings that might be related
    $sql = "SELECT * FROM bookings WHERE status = 'pending' ORDER BY created_at DESC LIMIT 5";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        echo "<h3>Recent Pending Bookings:</h3>";
        while ($row = $result->fetch_assoc()) {
            echo "<pre>";
            print_r($row);
            echo "</pre>";
        }
    }
}

// Check for any failed payment attempts
$sql = "SELECT * FROM payment_errors WHERE order_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<h2>Found Error Records:</h2>";
    while ($row = $result->fetch_assoc()) {
        echo "<pre>";
        print_r($row);
        echo "</pre>";
    }
}
?> 