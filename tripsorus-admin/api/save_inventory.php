<?php
require_once '../../db.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../login.php');
    exit();
}

$adminId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if we're receiving form data or raw JSON
    $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
    
    if (strpos($contentType, 'application/json') !== false) {
        // Handle JSON input
        $input = json_decode(file_get_contents("php://input"), true);
        $updates = $input['updates'] ?? $input; // allow both {"updates":[...]} and [...]
    } else {
        // Handle form data
        $property_id = $_POST['property_id'] ?? null;
        $updates_json = $_POST['updates'] ?? null;
        
        if ($updates_json) {
            $updates = json_decode($updates_json, true);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No updates data received']);
            exit();
        }
    }

    if (!is_array($updates)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid data format', 'received' => $updates]);
        exit();
    }

    try {
        $allowedFields = ['single_rate', 'double_rate', 'total_rooms', 'booked_rooms', 'available_rooms', 'is_available'];

        foreach ($updates as $update) {
            $field = $update['field'] ?? null;
            $value = $update['value'] ?? null;

            // identifying fields
            $date       = $update['date'] ?? null;
            $roomTypeId = $update['room_type'] ?? $update['room_type_id'] ?? null;
            $propertyId = $update['property_id'] ?? $property_id ?? null;
            $mealType   = $update['meal_type'] ?? 'room_only';

            if (!$field || !in_array($field, $allowedFields)) {
                error_log("Invalid field attempted: " . print_r($update, true));
                continue; // Skip invalid updates
            }

            if (!$date || !$roomTypeId || !$propertyId) {
                error_log("Missing identifying keys, skipping update: " . print_r($update, true));
                continue;
            }

            // Check if record exists
            $checkStmt = $pdo->prepare("
                SELECT id FROM room_inventory 
                WHERE room_type_id = :room_type_id 
                  AND property_id = :property_id 
                  AND date = :date 
                  AND meal_type = :meal_type
                LIMIT 1
            ");
            $checkStmt->execute([
                ':room_type_id' => $roomTypeId,
                ':property_id'  => $propertyId,
                ':date'         => $date,
                ':meal_type'    => $mealType
            ]);

        if ($checkStmt->rowCount() > 0) {
    // Recalculate available rooms if total_rooms or booked_rooms updated
    if ($field === 'total_rooms' || $field === 'booked_rooms') {
        $updateStmt = $pdo->prepare("
            UPDATE room_inventory 
            SET $field = :value,
                available_rooms = total_rooms - booked_rooms,
                updated_at = NOW()
            WHERE room_type_id = :room_type_id 
              AND property_id = :property_id 
              AND date = :date 
              AND meal_type = :meal_type
        ");
    } else {
        $updateStmt = $pdo->prepare("
            UPDATE room_inventory 
            SET $field = :value,
                updated_at = NOW()
            WHERE room_type_id = :room_type_id 
              AND property_id = :property_id 
              AND date = :date 
              AND meal_type = :meal_type
        ");
    }
    
    $updateStmt->execute([
        ':value'        => $value,
        ':room_type_id' => $roomTypeId,
        ':property_id'  => $propertyId,
        ':date'         => $date,
        ':meal_type'    => $mealType
    ]);
}
 else {
                // Insert row with defaults
                $insertStmt = $pdo->prepare("
                    INSERT INTO room_inventory 
                    (room_type_id, property_id, date, meal_type, single_rate, double_rate, 
                     total_rooms, booked_rooms, available_rooms, is_available, created_at, updated_at)
                    VALUES 
                    (:room_type_id, :property_id, :date, :meal_type, 0, 0, 0, 0, 0, 1, NOW(), NOW())
                ");
                $insertStmt->execute([
                    ':room_type_id' => $roomTypeId,
                    ':property_id'  => $propertyId,
                    ':date'         => $date,
                    ':meal_type'    => $mealType
                ]);

                // Now update the requested field
                $updateStmt = $pdo->prepare("
                    UPDATE room_inventory 
                    SET $field = :value, updated_at = NOW()
                    WHERE room_type_id = :room_type_id 
                      AND property_id = :property_id 
                      AND date = :date 
                      AND meal_type = :meal_type
                ");
                $updateStmt->execute([
                    ':value'        => $value,
                    ':room_type_id' => $roomTypeId,
                    ':property_id'  => $propertyId,
                    ':date'         => $date,
                    ':meal_type'    => $mealType
                ]);
            }
        }

        echo json_encode(['status' => 'success', 'message' => 'Inventory updated successfully']);
    } catch (Exception $e) {
        error_log("Inventory update error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
