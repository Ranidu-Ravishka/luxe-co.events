<?php
session_start();
require_once('../includes/config.php');

// Check if service ID is provided
if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Service ID is required']);
    exit();
}

$service_id = (int)$_GET['id'];

// Fetch service details
$query = "SELECT s.*, sc.name as category_name 
          FROM services s 
          JOIN service_categories sc ON s.category_id = sc.id 
          WHERE s.id = ? AND s.status = 'active'";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $service_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Service not found']);
    exit();
}

$service = $result->fetch_assoc();

// Format the image path
if (!empty($service['image']) && strpos($service['image'], 'http') !== 0) {
    $service['image'] = '../' . $service['image'];
}

echo json_encode([
    'success' => true,
    'service' => $service
]);

$stmt->close();
$conn->close();
?> 