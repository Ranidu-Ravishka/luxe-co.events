<?php
require_once 'includes/config.php';
require_once 'includes/paypal_config.php';

// Order ID to check
$order_id = '3EC93945PF675135H';

// Database connection is already established in config.php
// No need to create a new connection

// Check payment_errors table
$stmt = $conn->prepare("SELECT * FROM payment_errors WHERE order_id = ?");
$stmt->bind_param("s", $order_id);
$stmt->execute();
$result = $stmt->get_result();

echo "<h2>Payment Error Details</h2>";
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<pre>";
        print_r($row);
        echo "</pre>";
    }
} else {
    echo "<p>No payment errors found for order ID: $order_id</p>";
}

// Check payments table
$stmt = $conn->prepare("SELECT * FROM payments WHERE paypal_order_id = ?");
$stmt->bind_param("s", $order_id);
$stmt->execute();
$result = $stmt->get_result();

echo "<h2>Payment Records</h2>";
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<pre>";
        print_r($row);
        echo "</pre>";
    }
} else {
    echo "<p>No payment records found for order ID: $order_id</p>";
}

// Check bookings table
$stmt = $conn->prepare("SELECT * FROM bookings WHERE id IN (SELECT booking_id FROM payments WHERE paypal_order_id = ?)");
$stmt->bind_param("s", $order_id);
$stmt->execute();
$result = $stmt->get_result();

echo "<h2>Related Bookings</h2>";
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<pre>";
        print_r($row);
        echo "</pre>";
    }
} else {
    echo "<p>No related bookings found for order ID: $order_id</p>";
}

// Check if payment_errors table exists
$result = $conn->query("SHOW TABLES LIKE 'payment_errors'");
echo "<h2>Database Tables</h2>";
if ($result->num_rows > 0) {
    echo "<p>payment_errors table exists</p>";
} else {
    echo "<p>payment_errors table does not exist</p>";
}

// Check if payments table has paypal_order_id column
$result = $conn->query("SHOW COLUMNS FROM payments LIKE 'paypal_order_id'");
if ($result->num_rows > 0) {
    echo "<p>payments table has paypal_order_id column</p>";
} else {
    echo "<p>payments table does not have paypal_order_id column</p>";
}

// Check PayPal configuration
echo "<h2>PayPal Configuration</h2>";
echo "<p>Mode: " . PAYPAL_MODE . "</p>";
echo "<p>API Base: " . PAYPAL_API_BASE . "</p>";
echo "<p>Success URL: " . PAYPAL_SUCCESS_URL . "</p>";
echo "<p>Cancel URL: " . PAYPAL_CANCEL_URL . "</p>";

// Check if the order exists in PayPal
echo "<h2>PayPal Order Check</h2>";
try {
    // Get PayPal access token
    $ch = curl_init(PAYPAL_API_BASE . '/v1/oauth2/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_USERPWD, PAYPAL_CLIENT_ID . ':' . PAYPAL_CLIENT_SECRET);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For development only
    
    $result = curl_exec($ch);
    if (!$result) {
        throw new Exception('Failed to get PayPal access token: ' . curl_error($ch));
    }
    curl_close($ch);
    
    $token_data = json_decode($result);
    if (!isset($token_data->access_token)) {
        throw new Exception('Invalid PayPal token response');
    }
    $access_token = $token_data->access_token;
    
    // Get order details
    $ch = curl_init(PAYPAL_API_BASE . "/v2/checkout/orders/{$order_id}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For development only
    
    $result = curl_exec($ch);
    if (!$result) {
        throw new Exception('Failed to get PayPal order details: ' . curl_error($ch));
    }
    curl_close($ch);
    
    $order_details = json_decode($result);
    echo "<pre>";
    print_r($order_details);
    echo "</pre>";
} catch (Exception $e) {
    echo "<p>Error checking PayPal order: " . $e->getMessage() . "</p>";
}
?> 