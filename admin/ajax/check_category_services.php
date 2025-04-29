<?php
require_once '../../includes/config.php';
require_once '../includes/auth_check.php';

header('Content-Type: application/json');

if (!isset($_POST['category_id'])) {
    echo json_encode(['error' => 'Category ID is required']);
    exit;
}

$category_id = (int)$_POST['category_id'];

try {
    $stmt = $conn->prepare("SELECT COUNT(*) as service_count FROM services WHERE category_id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'service_count' => $data['service_count']
    ]);
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage()
    ]);
} 