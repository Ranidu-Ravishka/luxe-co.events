<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/paypal_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Authentication required'
    ]);
    exit();
}

// Get order ID from request
$order_id = isset($_POST['order_id']) ? $_POST['order_id'] : '';
$booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;

if (empty($order_id) || empty($booking_id)) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing order ID or booking ID'
    ]);
    exit();
}

// Start transaction
$conn->begin_transaction();

try {
    // Check if booking exists and belongs to user
    $check_booking = $conn->prepare("SELECT id, total_amount FROM bookings WHERE id = ? AND user_id = ?");
    $check_booking->bind_param("ii", $booking_id, $_SESSION['user_id']);
    $check_booking->execute();
    $booking_result = $check_booking->get_result();
    $booking = $booking_result->fetch_assoc();
    
    if (!$booking) {
        throw new Exception('Invalid booking ID');
    }
    
    // Generate a transaction ID
    $transaction_id = 'FP' . time() . rand(1000, 9999);
    
    // Update booking status to confirmed
    $update_booking = $conn->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?");
    $update_booking->bind_param("i", $booking_id);
    if (!$update_booking->execute()) {
        throw new Exception('Failed to update booking status');
    }
    
    // Create payment record
    $insert_payment = $conn->prepare("
        INSERT INTO payments (
            booking_id, 
            amount, 
            payment_method, 
            transaction_id, 
            status, 
            payment_date,
            paypal_order_id
        ) VALUES (?, ?, 'paypal', ?, 'completed', NOW(), ?)
    ");
    
    $insert_payment->bind_param(
        "idss",
        $booking_id,
        $booking['total_amount'],
        $transaction_id,
        $order_id
    );
    
    if (!$insert_payment->execute()) {
        throw new Exception('Failed to create payment record');
    }
    
    // Update or create processing record
    $check_processing = $conn->prepare("SELECT id FROM paypal_processing WHERE order_id = ?");
    $check_processing->bind_param("s", $order_id);
    $check_processing->execute();
    $processing_result = $check_processing->get_result();
    
    if ($processing_result->num_rows > 0) {
        // Update existing record
        $update_processing = $conn->prepare("
            UPDATE paypal_processing 
            SET status = 'completed',
                completed_at = NOW(),
                transaction_id = ?
            WHERE order_id = ?
        ");
        $update_processing->bind_param("ss", $transaction_id, $order_id);
        $update_processing->execute();
    } else {
        // Create new record
        $insert_processing = $conn->prepare("
            INSERT INTO paypal_processing (
                order_id, 
                user_id, 
                booking_ids,
                status,
                transaction_id,
                completed_at
            ) VALUES (?, ?, ?, 'completed', ?, NOW())
        ");
        $booking_ids_json = json_encode([$booking_id]);
        $insert_processing->bind_param("siss", $order_id, $_SESSION['user_id'], $booking_ids_json, $transaction_id);
        $insert_processing->execute();
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Payment processed successfully',
        'transaction_id' => $transaction_id
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => 'Failed to process payment: ' . $e->getMessage()
    ]);
}
?> 