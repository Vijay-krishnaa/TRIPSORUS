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
    $seasonId = $input['season_id'];
    $ratePlanId = $input['rate_plan_id'];
    
    // Get season details
    $seasonStmt = $pdo->prepare("SELECT * FROM seasons WHERE id = :season_id");
    $seasonStmt->execute([':season_id' => $seasonId]);
    $season = $seasonStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$season) {
        throw new Exception('Season not found');
    }
    
    // Get rate plan details
    $ratePlanStmt = $pdo->prepare("SELECT * FROM rate_plans WHERE id = :rate_plan_id");
    $ratePlanStmt->execute([':rate_plan_id' => $ratePlanId]);
    $ratePlan = $ratePlanStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$ratePlan) {
        throw new Exception('Rate plan not found');
    }
    
    // Calculate adjusted rate
    if ($season['adjustment_type'] === 'increase') {
        $adjustedRate = $ratePlan['base_rate'] * (1 + ($season['adjustment_value'] / 100));
    } else {
        $adjustedRate = $ratePlan['base_rate'] * (1 - ($season['adjustment_value'] / 100));
    }
    
    // Insert seasonal rate
    $stmt = $pdo->prepare("
        INSERT INTO seasonal_rates (season_id, rate_plan_id, adjusted_rate)
        VALUES (:season_id, :rate_plan_id, :adjusted_rate)
    ");
    
    $stmt->execute([
        ':season_id' => $seasonId,
        ':rate_plan_id' => $ratePlanId,
        ':adjusted_rate' => $adjustedRate
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Seasonal rate added successfully']);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}