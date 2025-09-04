<?php
require_once '../../db.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $seasonalRateId = $input['seasonal_rate_id'];
    
    // Delete seasonal rate
    $stmt = $pdo->prepare("DELETE FROM seasonal_rates WHERE id = :id");
    $stmt->execute([':id' => $seasonalRateId]);
    
    echo json_encode(['success' => true, 'message' => 'Seasonal rate removed successfully']);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}