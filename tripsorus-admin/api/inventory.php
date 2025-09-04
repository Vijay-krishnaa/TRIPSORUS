<?php
require_once '../../db.php';
header('Content-Type: application/json');

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // GET - Get inventory for a room type and date range
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $roomTypeId = isset($_GET['room_type_id']) ? (int)$_GET['room_type_id'] : 0;
        $startDate = $_GET['start_date'] ?? date('Y-m-d');
        $endDate = $_GET['end_date'] ?? date('Y-m-d', strtotime('+7 days'));
        
        if ($roomTypeId <= 0) {
            throw new Exception('Invalid room type ID', 400);
        }

        // Validate dates
        if (!strtotime($startDate) || !strtotime($endDate)) {
            throw new Exception('Invalid date format. Use YYYY-MM-DD', 400);
        }
        
        if ($startDate > $endDate) {
            throw new Exception('End date must be after start date', 400);
        }

        $stmt = $pdo->prepare("
            SELECT * FROM room_inventory 
            WHERE room_type_id = ? AND date BETWEEN ? AND ?
            ORDER BY date ASC
        ");
        $stmt->execute([$roomTypeId, $startDate, $endDate]);
        $inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $inventory]);
    }
    
    // POST - Bulk update inventory
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['updates']) || !is_array($data['updates'])) {
            throw new Exception('Invalid inventory data', 400);
        }

        // Begin transaction
        $pdo->beginTransaction();

        try {
            foreach ($data['updates'] as $update) {
                $requiredFields = ['room_type_id', 'date', 'total_rooms', 'rate_1', 'rate_2'];
                foreach ($requiredFields as $field) {
                    if (!isset($update[$field])) {
                        throw new Exception("Missing required field: $field", 400);
                    }
                }

                // Validate date
                if (!strtotime($update['date'])) {
                    throw new Exception('Invalid date format. Use YYYY-MM-DD', 400);
                }

                $stmt = $pdo->prepare("
                    INSERT INTO room_inventory (
                        room_type_id, date, total_rooms, booked_rooms, 
                        rate_1, rate_2, is_available
                    ) VALUES (?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        total_rooms = VALUES(total_rooms),
                        rate_1 = VALUES(rate_1),
                        rate_2 = VALUES(rate_2),
                        is_available = VALUES(is_available)
                ");
                
                $stmt->execute([
                    $update['room_type_id'],
                    $update['date'],
                    $update['total_rooms'],
                    $update['booked_rooms'] ?? 0,
                    $update['rate_1'],
                    $update['rate_2'],
                    $update['is_available'] ?? true
                ]);
            }
            
            $pdo->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Inventory updated successfully'
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