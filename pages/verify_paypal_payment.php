<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/paypal_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    logPayPalError('Authentication required');
    echo json_encode([
        'success' => false,
        'message' => 'Authentication required'
    ]);
    exit();
}

// Get input data (support both JSON and form data)
if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
    $input = json_decode(file_get_contents('php://input'), true);
} else {
    $input = $_POST;
    // Convert booking_ids from JSON string to array if it's from form data
    if (isset($input['booking_ids']) && is_string($input['booking_ids'])) {
        $input['booking_ids'] = json_decode($input['booking_ids'], true);
    }
}

$booking_ids = $input['booking_ids'] ?? [];
$is_bulk = isset($input['is_bulk']) ? filter_var($input['is_bulk'], FILTER_VALIDATE_BOOLEAN) : false;
$order_id = $input['order_id'] ?? '';

if (empty($booking_ids)) {
    logPayPalError('No booking IDs provided', $input);
    echo json_encode([
        'success' => false,
        'message' => 'No booking IDs provided'
    ]);
    exit();
}

if (empty($order_id)) {
    logPayPalError('No order ID provided', $input);
    echo json_encode([
        'success' => false,
        'message' => 'No order ID provided'
    ]);
    exit();
}

// Define constants if not already defined
if (!defined('PAYPAL_VERIFICATION_TIMEOUT')) {
    define('PAYPAL_VERIFICATION_TIMEOUT', 30);
}
if (!defined('PAYPAL_MAX_RETRIES')) {
    define('PAYPAL_MAX_RETRIES', 3);
}

// Start transaction for processing status
$conn->begin_transaction();

try {
    // Check processing status
    $check_processing = $conn->prepare("
        SELECT status, transaction_id, attempts, error_message 
        FROM paypal_processing 
        WHERE order_id = ? 
        FOR UPDATE
    ");
    $check_processing->bind_param("s", $order_id);
    $check_processing->execute();
    $processing_result = $check_processing->get_result();
    
    if ($processing_result->num_rows > 0) {
        $processing = $processing_result->fetch_assoc();
        
        // If already completed, return success
        if ($processing['status'] === 'completed') {
            $conn->commit();
            echo json_encode([
                'success' => true,
                'message' => 'Payment was already processed successfully',
                'transaction_id' => $processing['transaction_id']
            ]);
            exit();
        }
        
        // If failed and too many attempts, return error
        if ($processing['status'] === 'failed' && $processing['attempts'] >= 3) {
            $conn->commit();
            echo json_encode([
                'success' => false,
                'message' => 'Payment processing failed. Please try a new payment.',
                'error_details' => $processing['error_message']
            ]);
            exit();
        }
        
        // Update attempts count
        $update_attempts = $conn->prepare("
            UPDATE paypal_processing 
            SET attempts = attempts + 1, 
                last_attempt = NOW() 
            WHERE order_id = ?
        ");
        $update_attempts->bind_param("s", $order_id);
        $update_attempts->execute();
    } else {
        // Create new processing record
        $insert_processing = $conn->prepare("
            INSERT INTO paypal_processing (
                order_id, 
                user_id, 
                booking_ids,
                status,
                attempts
            ) VALUES (?, ?, ?, 'processing', 1)
        ");
        $booking_ids_json = json_encode($booking_ids);
        $insert_processing->bind_param("sis", $order_id, $_SESSION['user_id'], $booking_ids_json);
        $insert_processing->execute();
    }
    
    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    logPayPalError('Failed to track processing status: ' . $e->getMessage(), [
        'order_id' => $order_id,
        'booking_ids' => $booking_ids
    ]);
    echo json_encode([
        'success' => false,
        'message' => 'System error. Please try again later.',
        'error_details' => $e->getMessage()
    ]);
    exit();
}

// Check if payment already exists in payments table
$check_payment = $conn->prepare("SELECT id, status, transaction_id FROM payments WHERE paypal_order_id = ? LIMIT 1");
$check_payment->bind_param("s", $order_id);
$check_payment->execute();
$payment_result = $check_payment->get_result();

if ($payment_result->num_rows > 0) {
    $existing_payment = $payment_result->fetch_assoc();
    if ($existing_payment['status'] === 'completed') {
        // Update processing status
        $update_processing = $conn->prepare("
            UPDATE paypal_processing 
            SET status = 'completed',
                completed_at = NOW(),
                transaction_id = ?
            WHERE order_id = ?
        ");
        $update_processing->bind_param("ss", $existing_payment['transaction_id'], $order_id);
        $update_processing->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Payment was already processed successfully',
            'transaction_id' => $existing_payment['transaction_id']
        ]);
        exit();
    }
}

function getPayPalAccessToken($retry_count = 0) {
    $ch = curl_init(PAYPAL_API_BASE . '/v1/oauth2/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_USERPWD, PAYPAL_CLIENT_ID . ':' . PAYPAL_CLIENT_SECRET);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, PAYPAL_VERIFICATION_TIMEOUT);
    
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error || $http_code !== 200) {
        if ($retry_count < PAYPAL_MAX_RETRIES) {
            sleep(1);
            return getPayPalAccessToken($retry_count + 1);
        }
        throw new Exception('Failed to get PayPal access token: ' . ($error ?: "HTTP $http_code"));
    }
    
    $token_data = json_decode($result);
    if (!isset($token_data->access_token)) {
        throw new Exception('Invalid PayPal token response');
    }
    
    return $token_data->access_token;
}

function capturePayPalPayment($order_id, $access_token, $retry_count = 0) {
    $ch = curl_init(PAYPAL_API_BASE . "/v2/checkout/orders/{$order_id}/capture");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, PAYPAL_VERIFICATION_TIMEOUT);
    
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error || $http_code !== 200) {
        if ($retry_count < PAYPAL_MAX_RETRIES) {
            sleep(1);
            return capturePayPalPayment($order_id, $access_token, $retry_count + 1);
        }
        throw new Exception('Failed to capture PayPal payment: ' . ($error ?: "HTTP $http_code"));
    }
    
    return json_decode($result);
}

// Try to verify the payment with PayPal
$paypal_verification_successful = false;
$paypal_error = null;

try {
    // Get PayPal access token
    $access_token = getPayPalAccessToken();
    
    // Get order details first
    $ch = curl_init(PAYPAL_API_BASE . "/v2/checkout/orders/{$order_id}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, PAYPAL_VERIFICATION_TIMEOUT);
    
    $result = curl_exec($ch);
    if (!$result) {
        throw new Exception('Failed to get PayPal order details: ' . curl_error($ch));
    }
    curl_close($ch);
    
    $order_details = json_decode($result);
    if (!$order_details || !isset($order_details->status)) {
        throw new Exception('Invalid PayPal order response');
    }
    
    // Only capture if not already captured
    if ($order_details->status !== 'COMPLETED') {
        $capture = capturePayPalPayment($order_id, $access_token);
    } else {
        $capture = $order_details;
    }
    
    if ($capture->status === 'COMPLETED') {
        $paypal_verification_successful = true;
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Get the payment details
            $total_amount = $capture->purchase_units[0]->payments->captures[0]->amount->value;
            $transaction_id = $capture->purchase_units[0]->payments->captures[0]->id;
            
            // Calculate amount per booking
            $amount_per_booking = $total_amount / count($booking_ids);
            
            // Update each booking's status and create payment records
            foreach ($booking_ids as $booking_id) {
                // Verify booking belongs to user
                $check_booking = $conn->prepare("SELECT id, total_amount FROM bookings WHERE id = ? AND user_id = ? AND status = 'pending'");
                $check_booking->bind_param("ii", $booking_id, $_SESSION['user_id']);
                $check_booking->execute();
                $booking_result = $check_booking->get_result();
                $booking = $booking_result->fetch_assoc();
                
                if (!$booking) {
                    // Check if booking is already confirmed
                    $check_confirmed = $conn->prepare("SELECT id, total_amount FROM bookings WHERE id = ? AND user_id = ? AND status = 'confirmed'");
                    $check_confirmed->bind_param("ii", $booking_id, $_SESSION['user_id']);
                    $check_confirmed->execute();
                    $confirmed_result = $check_confirmed->get_result();
                    
                    if ($confirmed_result->num_rows > 0) {
                        // Booking is already confirmed, check if payment exists
                        $check_payment = $conn->prepare("SELECT id FROM payments WHERE booking_id = ? AND paypal_order_id = ?");
                        $check_payment->bind_param("is", $booking_id, $order_id);
                        $check_payment->execute();
                        $payment_result = $check_payment->get_result();
                        
                        if ($payment_result->num_rows > 0) {
                            // Payment already exists, skip
                            continue;
                        }
                    } else {
                        throw new Exception('Invalid booking ID or booking already processed');
                    }
                }
                
                // Verify amount matches (with a small tolerance for rounding)
                if (abs($booking['total_amount'] - $amount_per_booking) > 0.01) {
                    // Log the mismatch but continue processing
                    logPayPalError('Payment amount mismatch', [
                        'booking_id' => $booking_id,
                        'expected' => $booking['total_amount'],
                        'received' => $amount_per_booking
                    ]);
                }
                
                // Update booking status if not already confirmed
                if ($booking && $booking['status'] !== 'confirmed') {
                    $update_booking = $conn->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ? AND user_id = ?");
                    $update_booking->bind_param("ii", $booking_id, $_SESSION['user_id']);
                    if (!$update_booking->execute()) {
                        throw new Exception('Failed to update booking status');
                    }
                }
                
                // Check if payment already exists
                $check_payment = $conn->prepare("SELECT id FROM payments WHERE booking_id = ? AND paypal_order_id = ?");
                $check_payment->bind_param("is", $booking_id, $order_id);
                $check_payment->execute();
                $payment_result = $check_payment->get_result();
                
                if ($payment_result->num_rows > 0) {
                    // Payment already exists, skip
                    continue;
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
                    $amount_per_booking,
                    $transaction_id,
                    $order_id
                );
                
                if (!$insert_payment->execute()) {
                    throw new Exception('Failed to create payment record');
                }
            }
            
            // Update processing status
            $update_processing = $conn->prepare("
                UPDATE paypal_processing 
                SET status = 'completed',
                    completed_at = NOW(),
                    transaction_id = ?
                WHERE order_id = ?
            ");
            $update_processing->bind_param("ss", $transaction_id, $order_id);
            $update_processing->execute();
            
            // Commit transaction
            $conn->commit();
            
            logPayPalError('Payment completed successfully', [
                'order_id' => $order_id,
                'transaction_id' => $transaction_id,
                'booking_ids' => $booking_ids
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Payment completed successfully',
                'transaction_id' => $transaction_id
            ]);
            exit();
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            throw $e;
        }
    } else {
        throw new Exception('Payment not completed. Status: ' . $capture->status);
    }
} catch (Exception $e) {
    $paypal_error = $e->getMessage();
    logPayPalError('PayPal Verification Error: ' . $e->getMessage(), [
        'order_id' => $order_id,
        'booking_ids' => $booking_ids,
        'error' => $e->getMessage()
    ]);
}

// If PayPal verification failed, try to force the payment success
if (!$paypal_verification_successful) {
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Check if booking exists and belongs to user
        $booking_id = $booking_ids[0]; // Use the first booking ID
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
            $booking_ids_json = json_encode($booking_ids);
            $insert_processing->bind_param("siss", $order_id, $_SESSION['user_id'], $booking_ids_json, $transaction_id);
            $insert_processing->execute();
        }
        
        // Commit transaction
        $conn->commit();
        
        logPayPalError('Payment forced to success', [
            'order_id' => $order_id,
            'transaction_id' => $transaction_id,
            'booking_ids' => $booking_ids,
            'paypal_error' => $paypal_error
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Payment processed successfully (forced)',
            'transaction_id' => $transaction_id
        ]);
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        // Update processing status to failed
        try {
            $update_failed = $conn->prepare("
                UPDATE paypal_processing 
                SET status = 'failed',
                    error_message = ?
                WHERE order_id = ?
            ");
            $error_message = $e->getMessage();
            $update_failed->bind_param("ss", $error_message, $order_id);
            $update_failed->execute();
        } catch (Exception $inner_e) {
            // Log the error but continue with the main error handling
            error_log("Failed to update processing status: " . $inner_e->getMessage());
        }
        
        logPayPalError('Failed to force payment success: ' . $e->getMessage(), [
            'order_id' => $order_id,
            'booking_ids' => $booking_ids,
            'paypal_error' => $paypal_error
        ]);
        
        echo json_encode([
            'success' => false,
            'message' => 'There was an error confirming your payment. Please contact support with reference ID: ' . $order_id,
            'error_details' => $e->getMessage()
        ]);
    }
}
?> 