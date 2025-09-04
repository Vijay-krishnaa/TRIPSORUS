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

header('Content-Type: application/json');

try {
    $stmt = $pdo->prepare("SELECT DISTINCT city FROM properties WHERE admin_id = ? ORDER BY city");
    $stmt->execute([$adminId]);
    $cities = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode([
        'success' => true,
        'cities' => $cities
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}