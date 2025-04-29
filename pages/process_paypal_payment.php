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

// Get the amount from POST data
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
if ($amount <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid amount'
    ]);
    exit();
}

try {
    // Get PayPal access token
    $ch = curl_init(PAYPAL_API_BASE . '/v1/oauth2/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_USERPWD, PAYPAL_CLIENT_ID . ':' . PAYPAL_CLIENT_SECRET);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
    
    $result = curl_exec($ch);
    $access_token = json_decode($result)->access_token;
    
    // Create PayPal order
    $order_data = [
        'intent' => 'CAPTURE',
        'purchase_units' => [[
            'amount' => [
                'currency_code' => PAYPAL_CURRENCY,
                'value' => number_format($amount, 2, '.', '')
            ],
            'description' => 'Wedding Planning Service Payment'
        ]],
        'application_context' => [
            'return_url' => PAYPAL_SUCCESS_URL,
            'cancel_url' => PAYPAL_CANCEL_URL
        ]
    ];
    
    $ch = curl_init(PAYPAL_API_BASE . '/v2/checkout/orders');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($order_data));
    
    $result = curl_exec($ch);
    $order = json_decode($result);
    
    if (isset($order->id)) {
        // Store order ID in session for verification
        $_SESSION['paypal_order_id'] = $order->id;
        
        echo json_encode([
            'success' => true,
            'order_id' => $order->id,
            'approval_url' => $order->links[1]->href
        ]);
    } else {
        throw new Exception('Failed to create PayPal order');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 