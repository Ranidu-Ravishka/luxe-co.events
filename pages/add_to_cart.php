<?php
session_start();
require_once('../includes/config.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to add items to cart']);
    exit();
}

// Check if service ID is provided
if (!isset($_POST['service_id'])) {
    echo json_encode(['success' => false, 'message' => 'Service ID is required']);
    exit();
}

$service_id = (int)$_POST['service_id'];
$user_id = $_SESSION['user_id'];

// Verify service exists
$check_service = "SELECT id FROM services WHERE id = ?";
$stmt = $conn->prepare($check_service);
$stmt->bind_param("i", $service_id);
$stmt->execute();
$service_exists = $stmt->get_result()->num_rows > 0;

if (!$service_exists) {
    echo json_encode(['success' => false, 'message' => 'Service not found']);
    exit();
}

// Check if service is already in cart
$check_cart = "SELECT id FROM cart WHERE user_id = ? AND service_id = ?";
$stmt = $conn->prepare($check_cart);
$stmt->bind_param("ii", $user_id, $service_id);
$stmt->execute();
$already_in_cart = $stmt->get_result()->num_rows > 0;

if ($already_in_cart) {
    echo json_encode(['success' => false, 'message' => 'Service already in cart']);
    exit();
}

// Add to cart
$insert_query = "INSERT INTO cart (user_id, service_id, event_date, created_at) VALUES (?, ?, NOW(), NOW())";
$stmt = $conn->prepare($insert_query);
$stmt->bind_param("ii", $user_id, $service_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Service added to cart successfully']);
} else {
    error_log("Error adding to cart: " . $stmt->error);
    echo json_encode(['success' => false, 'message' => 'Failed to add service to cart']);
}

$stmt->close();
$conn->close();
?> 