<?php
require_once '../../db.php';
header('Content-Type: application/json');

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $seasonId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($seasonId > 0) {
            $stmt = $pdo->prepare("SELECT * FROM seasons WHERE id = ?");
            $stmt->execute([$seasonId]);
            $season = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$season) {
                throw new Exception('Season not found', 404);
            }
            echo json_encode(['success' => true, 'data' => $season]);
        } else {
        
            $stmt = $pdo->query("SELECT * FROM seasons ORDER BY start_date");
            $seasons = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $seasons]);
        }
    }
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $requiredFields = ['name', 'start_date', 'end_date', 'season_type', 'adjustment_type', 'adjustment_value'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Missing required field: $field", 400);
            }
        }

        // Validate dates
        $startDate = new DateTime($data['start_date']);
        $endDate = new DateTime($data['end_date']);
        
        if ($startDate > $endDate) {
            throw new Exception('End date must be after start date', 400);
        }

        $stmt = $pdo->prepare("
            INSERT INTO seasons (
                name, start_date, end_date, season_type, 
                adjustment_type, adjustment_value, description
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['name'],
            $data['start_date'],
            $data['end_date'],
            $data['season_type'],
            $data['adjustment_type'],
            $data['adjustment_value'],
            $data['description'] ?? null
        ]);

        $seasonId = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Season created successfully',
            'id' => $seasonId
        ]);
    }
    
    // PUT - Update season
    elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        $seasonId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($seasonId <= 0) {
            throw new Exception('Invalid season ID', 400);
        }

        // Validate dates
        $startDate = new DateTime($data['start_date']);
        $endDate = new DateTime($data['end_date']);
        
        if ($startDate > $endDate) {
            throw new Exception('End date must be after start date', 400);
        }

        $stmt = $pdo->prepare("
            UPDATE seasons SET
                name = ?,
                start_date = ?,
                end_date = ?,
                season_type = ?,
                adjustment_type = ?,
                adjustment_value = ?,
                description = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $data['name'],
            $data['start_date'],
            $data['end_date'],
            $data['season_type'],
            $data['adjustment_type'],
            $data['adjustment_value'],
            $data['description'] ?? null,
            $seasonId
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Season updated successfully'
        ]);
    }
    
    // DELETE - Delete season
    elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $seasonId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($seasonId <= 0) {
            throw new Exception('Invalid season ID', 400);
        }

        // Check if season exists
        $checkStmt = $pdo->prepare("SELECT id FROM seasons WHERE id = ?");
        $checkStmt->execute([$seasonId]);
        
        if ($checkStmt->rowCount() === 0) {
            throw new Exception('Season not found', 404);
        }

        // Delete from seasonal_rates first due to foreign key constraint
        $pdo->prepare("DELETE FROM seasonal_rates WHERE season_id = ?")->execute([$seasonId]);
        
        // Then delete the season
        $pdo->prepare("DELETE FROM seasons WHERE id = ?")->execute([$seasonId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Season deleted successfully'
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