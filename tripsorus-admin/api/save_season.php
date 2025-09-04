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
    $name = $_POST['name'];
    $seasonType = $_POST['season_type'];
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
    $adjustmentValue = $_POST['adjustment_value'];
    $adjustmentType = $_POST['adjustment_type'];
    $description = $_POST['description'] ?? null;
    $applyTo = json_decode($_POST['apply_to'], true);
    
    // Insert the season
    $stmt = $pdo->prepare("
        INSERT INTO seasons 
        (name, start_date, end_date, season_type, adjustment_type, adjustment_value, description)
        VALUES (:name, :start_date, :end_date, :season_type, :adjustment_type, :adjustment_value, :description)
    ");
    
    $stmt->execute([
        ':name' => $name,
        ':start_date' => $startDate,
        ':end_date' => $endDate,
        ':season_type' => $seasonType,
        ':adjustment_type' => $adjustmentType,
        ':adjustment_value' => $adjustmentValue,
        ':description' => $description
    ]);
    
    $seasonId = $pdo->lastInsertId();
    
    // Apply to rate plans if requested
    if (in_array('all', $applyTo)) {
        // Apply to all rate plans
        $applyStmt = $pdo->prepare("
            INSERT INTO seasonal_rates (season_id, rate_plan_id, adjusted_rate)
            SELECT :season_id, id, 
                CASE WHEN :adjustment_type = 'increase' 
                THEN base_rate * (1 + :adjustment_value / 100)
                ELSE base_rate * (1 - :adjustment_value / 100)
                END as adjusted_rate
            FROM rate_plans
        ");
        
        $applyStmt->execute([
            ':season_id' => $seasonId,
            ':adjustment_type' => $adjustmentType,
            ':adjustment_value' => $adjustmentValue / 100
        ]);
    } else {
        // Apply to specific meal plan types
        $mealPlans = [];
        if (in_array('room_only', $applyTo)) $mealPlans[] = 'room_only';
        if (in_array('breakfast', $applyTo)) $mealPlans[] = 'breakfast';
        if (in_array('full_board', $applyTo)) $mealPlans[] = 'full_board';
        
        if (!empty($mealPlans)) {
            $placeholders = implode(',', array_fill(0, count($mealPlans), '?'));
            
            $applyStmt = $pdo->prepare("
                INSERT INTO seasonal_rates (season_id, rate_plan_id, adjusted_rate)
                SELECT :season_id, id, 
                    CASE WHEN :adjustment_type = 'increase' 
                    THEN base_rate * (1 + :adjustment_value / 100)
                    ELSE base_rate * (1 - :adjustment_value / 100)
                    END as adjusted_rate
                FROM rate_plans
                WHERE meal_plan IN ($placeholders)
            ");
            
            $params = array_merge(
                [':season_id' => $seasonId, ':adjustment_type' => $adjustmentType, ':adjustment_value' => $adjustmentValue / 100],
                $mealPlans
            );
            
            $applyStmt->execute($params);
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Season created successfully']);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}