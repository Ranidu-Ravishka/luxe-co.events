<?php
session_start();
require_once '../includes/stripe_config.php';
require_once '../vendor/autoload.php';

// Set your Stripe secret key
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'html' => '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong>Authentication Required!</strong> Please log in to complete payment.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>'
    ]);
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'html' => '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong>Invalid Request!</strong> Please try again.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>'
    ]);
    exit();
}

// Get and validate payment data
$is_bulk_payment = isset($_POST['is_bulk_payment']) && $_POST['is_bulk_payment'] === '1';
$booking_ids = $is_bulk_payment ? (isset($_POST['booking_ids']) ? $_POST['booking_ids'] : []) : [isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0];
$card_holder = isset($_POST['card_holder']) ? trim($_POST['card_holder']) : '';
$card_number = isset($_POST['card_number']) ? preg_replace('/\D/', '', $_POST['card_number']) : '';
$expiry_month = isset($_POST['expiry_month']) ? $_POST['expiry_month'] : '';
$expiry_year = isset($_POST['expiry_year']) ? $_POST['expiry_year'] : '';
$cvv = isset($_POST['cvv']) ? preg_replace('/\D/', '', $_POST['cvv']) : '';
$save_card = isset($_POST['save_card']) ? true : false;

// Validate required fields
if (empty($booking_ids) || !$card_holder || !$card_number || !$expiry_month || !$expiry_year || !$cvv) {
    echo json_encode(['success' => false, 'message' => 'All payment fields are required']);
    exit();
}

// Validate card number
if (strlen($card_number) !== 16) {
    echo json_encode(['success' => false, 'message' => 'Invalid card number']);
    exit();
}

// Validate CVV
if (strlen($cvv) < 3 || strlen($cvv) > 4) {
    echo json_encode(['success' => false, 'message' => 'Invalid CVV']);
    exit();
}

// Validate expiry date
$current_month = (int)date('m');
$current_year = (int)date('Y');
$expiry_month = (int)$expiry_month;
$expiry_year = (int)$expiry_year;

if ($expiry_year < $current_year || ($expiry_year == $current_year && $expiry_month < $current_month)) {
    echo json_encode(['success' => false, 'message' => 'Card has expired']);
    exit();
}

try {
    // Get the payment amount from the session or POST data
    $amount = isset($_POST['amount']) ? $_POST['amount'] : 0;
    $description = isset($_POST['description']) ? $_POST['description'] : 'Wedding Planning Service';
    
    // Create a PaymentIntent with the order amount and currency
    $payment_intent = \Stripe\PaymentIntent::create([
        'amount' => $amount * 100, // Amount in cents
        'currency' => STRIPE_CURRENCY,
        'description' => $description,
        'payment_method_types' => ['card'],
    ]);

    // Return the client secret
    echo json_encode([
        'clientSecret' => $payment_intent->client_secret,
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 