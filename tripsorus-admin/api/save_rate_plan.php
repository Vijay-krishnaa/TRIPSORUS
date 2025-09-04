<?php
require_once '../../db.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
    $adminId = $_SESSION['user_id'];
    $roomTypeId = $_POST['room_type_id'];
    
    // Verify the room type belongs to the admin
    $verifyStmt = $pdo->prepare("
        SELECT rt.* FROM room_types rt
        JOIN properties p ON rt.property_id = p.id
        WHERE rt.id = :room_type_id AND p.admin_id = :admin_id
    ");
    $verifyStmt->execute([':room_type_id' => $roomTypeId, ':admin_id' => $adminId]);
    
    if ($verifyStmt->rowCount() === 0) {
        throw new Exception('Room type not found or access denied');
    }
    
    // Insert the rate plan
    $stmt = $pdo->prepare("
        INSERT INTO rate_plans 
        (admin_id, room_type_id, name, meal_plan, base_rate, extra_adult_rate, extra_child_rate, inventory_allocation, description)
        VALUES (:admin_id, :room_type_id, :name, :meal_plan, :base_rate, :extra_adult_rate, :extra_child_rate, :inventory_allocation, :description)
    ");
    
    $stmt->execute([
        ':admin_id' => $adminId,
        ':room_type_id' => $roomTypeId,
        ':name' => $_POST['name'],
        ':meal_plan' => $_POST['meal_plan'],
        ':base_rate' => $_POST['base_rate'],
        ':extra_adult_rate' => $_POST['extra_adult_rate'],
        ':extra_child_rate' => $_POST['extra_child_rate'],
        ':inventory_allocation' => $_POST['inventory_allocation'],
        ':description' => $_POST['description'] ?? null
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Rate plan created successfully']);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}