<?php
require_once 'includes/config.php';

// PayPal payment details
$order_id = '0H8747496X743234R';
$transaction_id = '78J50825X7857825T'; // This will be updated when we get the actual transaction ID
$amount = 500.00;
$payment_date = date('Y-m-d H:i:s');

// Start transaction
$conn->begin_transaction();

try {
    // First, find the pending booking(s)
    $stmt = $conn->prepare("SELECT * FROM bookings WHERE status = 'pending'");
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("No pending bookings found");
    }
    
    $bookings = $result->fetch_all(MYSQLI_ASSOC);
    $booking_ids = array_column($bookings, 'id');
    
    // Update each booking status
    foreach ($bookings as $booking) {
        $stmt = $conn->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?");
        $stmt->bind_param("i", $booking['id']);
        if (!$stmt->execute()) {
            throw new Exception("Failed to update booking #{$booking['id']}");
        }
    }
    
    // Create payment record
    $stmt = $conn->prepare("
        INSERT INTO payments (
            booking_id,
            amount,
            payment_method,
            transaction_id,
            paypal_order_id,
            status,
            payment_date
        ) VALUES (?, ?, 'paypal', ?, ?, 'completed', ?)
    ");
    
    $amount_per_booking = $amount / count($booking_ids);
    
    foreach ($booking_ids as $booking_id) {
        $stmt->bind_param(
            "idsss",
            $booking_id,
            $amount_per_booking,
            $transaction_id,
            $order_id,
            $payment_date
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create payment record for booking #$booking_id");
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    echo "Success! Payment recorded and booking(s) updated:\n";
    echo "- PayPal Order ID: $order_id\n";
    echo "- Transaction ID: $transaction_id\n";
    echo "- Amount: $" . number_format($amount, 2) . "\n";
    echo "- Booking IDs: " . implode(', ', $booking_ids) . "\n";
    echo "- Payment Date: $payment_date\n";
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo "Error: " . $e->getMessage() . "\n";
}
?> 