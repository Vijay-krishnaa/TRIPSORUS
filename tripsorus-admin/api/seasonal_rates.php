<?php
require_once '../../db.php';
header('Content-Type: application/json');

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // GET - Get seasonal rates for a season
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $seasonId = isset($_GET['season_id']) ? (int)$_GET['season_id'] : 0;
        
        if ($seasonId <= 0) {
            throw new Exception('Invalid season ID', 400);
        }

        $stmt = $pdo->prepare("
            SELECT sr.*, rp.name as rate_plan_name, rp.base_rate as original_rate
            FROM seasonal_rates sr
            JOIN rate_plans rp ON sr.rate_plan_id = rp.id
            WHERE sr.season_id = ?
        ");
        $stmt->execute([$seasonId]);
        $seasonalRates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $seasonalRates]);
    }
    
    // POST - Apply seasonal rates to rate plans
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $requiredFields = ['season_id', 'rate_plan_ids'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Missing required field: $field", 400);
            }
        }

        // Get season details
        $seasonStmt = $pdo->prepare("SELECT * FROM seasons WHERE id = ?");
        $seasonStmt->execute([$data['season_id']]);
        $season = $seasonStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$season) {
            throw new Exception('Season not found', 404);
        }

        // Begin transaction
        $pdo->beginTransaction();

        try {
            // First delete any existing seasonal rates for these rate plans in this season
            $deleteStmt = $pdo->prepare("
                DELETE FROM seasonal_rates 
                WHERE season_id = ? AND rate_plan_id IN (" . implode(',', array_fill(0, count($data['rate_plan_ids']), '?')) . ")
            ");
            $deleteStmt->execute(array_merge([$data['season_id']], $data['rate_plan_ids']));
            
            // Get base rates for the selected rate plans
            $ratePlansStmt = $pdo->prepare("
                SELECT id, base_rate FROM rate_plans 
                WHERE id IN (" . implode(',', array_fill(0, count($data['rate_plan_ids']), '?')) . ")
            ");
            $ratePlansStmt->execute($data['rate_plan_ids']);
            $ratePlans = $ratePlansStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate adjusted rates and insert
            $insertStmt = $pdo->prepare("
                INSERT INTO seasonal_rates (season_id, rate_plan_id, adjusted_rate)
                VALUES (?, ?, ?)
            ");
            
            foreach ($ratePlans as $ratePlan) {
                $baseRate = $ratePlan['base_rate'];
                $adjustment = $season['adjustment_value'] / 100;
                
                if ($season['adjustment_type'] === 'increase') {
                    $adjustedRate = $baseRate * (1 + $adjustment);
                } else {
                    $adjustedRate = $baseRate * (1 - $adjustment);
                }
                
                $insertStmt->execute([
                    $data['season_id'],
                    $ratePlan['id'],
                    round($adjustedRate, 2)
                ]);
            }
            
            $pdo->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Seasonal rates applied successfully'
            ]);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
    
    else {
        throw new Exception('Method not allowed', 405);
    }

} catch (Exception $e) {
    http_response_code($e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}