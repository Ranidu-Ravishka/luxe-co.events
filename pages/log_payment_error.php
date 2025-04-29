<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/paypal_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['order_id'])) {
    http_response_code(400);
    exit('Invalid input');
}

try {
    // Log the error
    logPayPalError('Payment Error Logged', [
        'user_id' => $_SESSION['user_id'],
        'order_id' => $input['order_id'],
        'error' => $input['error'] ?? 'Unknown error',
        'booking_ids' => $input['booking_ids'] ?? [],
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
    // Store error in database
    $stmt = $conn->prepare("
        INSERT INTO payment_errors (
            user_id,
            order_id,
            error_message,
            booking_ids,
            created_at
        ) VALUES (?, ?, ?, ?, NOW())
    ");
    
    $booking_ids_json = json_encode($input['booking_ids'] ?? []);
    $stmt->bind_param(
        "isss",
        $_SESSION['user_id'],
        $input['order_id'],
        $input['error'],
        $booking_ids_json
    );
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Failed to store error in database');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 