<?php
session_start();
require_once('../includes/config.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to view booking details']);
    exit();
}

// Check if booking ID is provided
if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
    exit();
}

$booking_id = (int)$_GET['id'];

// Fetch booking details
$booking_query = "SELECT b.*, s.name as service_name, s.price, s.image, sc.name as category_name 
                 FROM bookings b
                 JOIN services s ON b.service_id = s.id
                 JOIN service_categories sc ON s.category_id = sc.id
                 WHERE b.id = ? AND b.user_id = ?";
$stmt = $conn->prepare($booking_query);
$stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
$stmt->execute();
$booking_result = $stmt->get_result();

if ($booking_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Booking not found or unauthorized']);
    exit();
}

$booking = $booking_result->fetch_assoc();

echo json_encode([
    'success' => true,
    'booking' => $booking
]);

$stmt->close();
$conn->close();
?> 