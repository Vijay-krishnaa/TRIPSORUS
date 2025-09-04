<?php
require_once '../../db.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit();
}

$adminId = $_SESSION['user_id'];
$propertyId = $_GET['id'] ?? null;

header('Content-Type: application/json');

if (!$propertyId) {
    echo json_encode([
        'success' => false,
        'message' => 'Property ID is required'
    ]);
    exit();
}

try {
    // First check if the property belongs to the admin
    $stmt = $pdo->prepare("SELECT id FROM properties WHERE id = ? AND admin_id = ?");
    $stmt->execute([$propertyId, $adminId]);
    
    if ($stmt->rowCount() === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Property not found or access denied'
        ]);
        exit();
    }
    
    // Delete the property (you might want to implement soft delete instead)
    $stmt = $pdo->prepare("DELETE FROM properties WHERE id = ?");
    $stmt->execute([$propertyId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Property deleted successfully'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}