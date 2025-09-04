<?php
require_once '../../db.php';
header('Content-Type: application/json');

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // GET - List rate plans for a room type
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $roomTypeId = isset($_GET['room_type_id']) ? (int)$_GET['room_type_id'] : 0;
        
        if ($roomTypeId <= 0) {
            throw new Exception('Invalid room type ID', 400);
        }

        $stmt = $pdo->prepare("SELECT * FROM rate_plans WHERE room_type_id = ?");
        $stmt->execute([$roomTypeId]);
        $ratePlans = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $ratePlans]);
    }
    
    // POST - Create new rate plan
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $requiredFields = ['room_type_id', 'name', 'base_rate'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Missing required field: $field", 400);
            }
        }

        $stmt = $pdo->prepare("
            INSERT INTO rate_plans (
                room_type_id, name, meal_plan, base_rate, 
                extra_adult_rate, extra_child_rate, inventory_allocation, description
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['room_type_id'],
            $data['name'],
            $data['meal_plan'] ?? null,
            $data['base_rate'],
            $data['extra_adult_rate'] ?? 0,
            $data['extra_child_rate'] ?? 0,
            $data['inventory_allocation'] ?? null,
            $data['description'] ?? null
        ]);

        $ratePlanId = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Rate plan created successfully',
            'id' => $ratePlanId
        ]);
    }
    
    // PUT - Update rate plan
    elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        $ratePlanId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($ratePlanId <= 0) {
            throw new Exception('Invalid rate plan ID', 400);
        }

        $stmt = $pdo->prepare("
            UPDATE rate_plans SET
                name = ?,
                meal_plan = ?,
                base_rate = ?,
                extra_adult_rate = ?,
                extra_child_rate = ?,
                inventory_allocation = ?,
                description = ?,
                is_active = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $data['name'],
            $data['meal_plan'] ?? null,
            $data['base_rate'],
            $data['extra_adult_rate'] ?? 0,
            $data['extra_child_rate'] ?? 0,
            $data['inventory_allocation'] ?? null,
            $data['description'] ?? null,
            $data['is_active'] ?? true,
            $ratePlanId
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Rate plan updated successfully'
        ]);
    }
    
    // DELETE - Delete rate plan
    elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $ratePlanId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($ratePlanId <= 0) {
            throw new Exception('Invalid rate plan ID', 400);
        }

        // Check if rate plan exists
        $checkStmt = $pdo->prepare("SELECT id FROM rate_plans WHERE id = ?");
        $checkStmt->execute([$ratePlanId]);
        
        if ($checkStmt->rowCount() === 0) {
            throw new Exception('Rate plan not found', 404);
        }

        // Delete from seasonal_rates first due to foreign key constraint
        $pdo->prepare("DELETE FROM seasonal_rates WHERE rate_plan_id = ?")->execute([$ratePlanId]);
        
        // Then delete the rate plan
        $pdo->prepare("DELETE FROM rate_plans WHERE id = ?")->execute([$ratePlanId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Rate plan deleted successfully'
        ]);
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