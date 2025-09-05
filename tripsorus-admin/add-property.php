<?php
require_once '../db.php';
header('Content-Type: application/json');
session_start();
error_log("Session data: " . print_r($_SESSION, true));
error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
error_log("Files data: " . print_r($_FILES, true));
error_log("Post data: " . print_r($_POST, true));

function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function uploadFiles($files, $targetDir = 'uploads/') {
    $uploadedPaths = [];
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $maxFileSize = 5 * 1024 * 1024;
    
    try {
        if (!file_exists($targetDir)) {
            if (!mkdir($targetDir, 0755, true)) {
                throw new Exception("Failed to create directory: $targetDir");
            }
        }

        if (is_array($files['name'])) {
            $fileCount = count($files['name']);
            for ($i = 0; $i < $fileCount; $i++) {
                if (empty($files['tmp_name'][$i])) continue;

                if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                    throw new Exception("Upload error: " . $files['error'][$i]);
                }

                $fileInfo = getimagesize($files['tmp_name'][$i]);
                if ($fileInfo === false) {
                    throw new Exception("File is not a valid image");
                }

                if ($files['size'][$i] > $maxFileSize) {
                    throw new Exception("File size exceeds maximum limit of 5MB");
                }

                $fileName = basename($files['name'][$i]);
                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                if (!in_array($fileExt, $allowedExtensions)) {
                    throw new Exception("Invalid file extension: $fileExt");
                }

                $newFileName = uniqid('img_', true) . '.' . $fileExt;
                $targetPath = $targetDir . $newFileName;

                if (!move_uploaded_file($files['tmp_name'][$i], $targetPath)) {
                    throw new Exception("Failed to move uploaded file");
                }

                $uploadedPaths[] = $targetPath;
            }
        } else {
      
            if (empty($files['tmp_name'])) return $uploadedPaths;

            if ($files['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("Upload error: " . $files['error']);
            }

            $fileInfo = getimagesize($files['tmp_name']);
            if ($fileInfo === false) {
                throw new Exception("File is not a valid image");
            }

            if ($files['size'] > $maxFileSize) {
                throw new Exception("File size exceeds maximum limit of 5MB");
            }

            $fileName = basename($files['name']);
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            if (!in_array($fileExt, $allowedExtensions)) {
                throw new Exception("Invalid file extension: $fileExt");
            }

            $newFileName = uniqid('img_', true) . '.' . $fileExt;
            $targetPath = $targetDir . $newFileName;

            if (!move_uploaded_file($files['tmp_name'], $targetPath)) {
                throw new Exception("Failed to move uploaded file");
            }

            $uploadedPaths[] = $targetPath;
        }

        return $uploadedPaths;
    } catch (Exception $e) {
        foreach ($uploadedPaths as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }
        throw $e;
    }
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed', 405);
    }
    error_log("Checking authentication: user_id=" . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set'));
    error_log("User role: " . (isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'not set'));
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Authentication required: Please log in first', 401);
    }
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
        throw new Exception('Permission denied: Only administrators can create properties', 403);
    }
    $adminId = $_SESSION['user_id'];
    error_log("Admin ID: $adminId");

    $requiredFields = [
        'property_name' => 'Property name',
        'property_type' => 'Property type',
        'description' => 'Description',
        'address' => 'Address',
        'city' => 'City',
        'country' => 'Country'
    ];

    $missingFields = [];
    foreach ($requiredFields as $field => $name) {
        if (empty($_POST[$field])) {
            $missingFields[] = $name;
        }
    }

    if (!empty($missingFields)) {
        throw new Exception('Missing required fields: ' . implode(', ', $missingFields), 400);
    }

    $validPropertyTypes = ['hotel', 'resort', 'villa', 'apartment', 'guesthouse'];
    if (!in_array($_POST['property_type'], $validPropertyTypes)) {
        throw new Exception('Invalid property type', 400);
    }

    $pdo->beginTransaction();

 $propertyStmt = $pdo->prepare("
    INSERT INTO properties (
        admin_id, name, type, description, address, city, 
        country, map_link, amenities, checkin_time, checkout_time, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
");
$checkInTime = !empty($_POST['checkin_time']) ? sanitizeInput($_POST['checkin_time']) : '14:00';
$checkOutTime = !empty($_POST['checkout_time']) ? sanitizeInput($_POST['checkout_time']) : '12:00';


if (!preg_match('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/', $checkInTime)) {
    $checkInTime = '14:00';
}

if (!preg_match('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/', $checkOutTime)) {
    $checkOutTime = '12:00';
}
  $propertyAmenities = !empty($_POST['amenities']) && is_array($_POST['amenities']) ? 
    implode(',', array_map('sanitizeInput', $_POST['amenities'])) : '';
$propertyStmt->execute([
    $adminId,
    sanitizeInput($_POST['property_name']),
    sanitizeInput($_POST['property_type']),
    sanitizeInput($_POST['description']),
    sanitizeInput($_POST['address']),
    sanitizeInput($_POST['city']),
    sanitizeInput($_POST['country']),
    !empty($_POST['map_link']) ? filter_var($_POST['map_link'], FILTER_SANITIZE_URL) : null,
    $propertyAmenities,
    $checkInTime,
    $checkOutTime
]);

    $propertyId = $pdo->lastInsertId();
    error_log("Property created with ID: $propertyId");

    if (!empty($_FILES['property_main_image']) && !empty($_FILES['property_main_image']['tmp_name'])) {
        try {
            $propertyDir = "uploads/properties/{$propertyId}/";
            
            $mainImage = uploadFiles($_FILES['property_main_image'], $propertyDir);
            
            if (!empty($mainImage)) {
                $imageStmt = $pdo->prepare("
                    INSERT INTO property_images (property_id, image_path, is_main, created_at) 
                    VALUES (?, ?, 1, NOW())
                ");
                $imageStmt->execute([$propertyId, $mainImage[0]]);
            }
        } catch (Exception $e) {
            throw new Exception("Property main image upload failed: " . $e->getMessage());
        }
    }

    if (!empty($_FILES['propertyImages']) && !empty($_FILES['propertyImages']['tmp_name'][0])) {
        try {
            $propertyDir = "uploads/properties/{$propertyId}/";
            
            $propertyImages = uploadFiles($_FILES['propertyImages'], $propertyDir);
            
            $imageStmt = $pdo->prepare("
                INSERT INTO property_images (property_id, image_path, is_main, created_at) 
                VALUES (?, ?, 0, NOW())
            ");
            
            foreach ($propertyImages as $imagePath) {
                $imageStmt->execute([$propertyId, $imagePath]);
            }
        } catch (Exception $e) {
            error_log("Property additional images upload failed: " . $e->getMessage());
        }
    }

    if (!empty($_POST['room_types']) && is_array($_POST['room_types'])) {
        $roomStmt = $pdo->prepare("
            INSERT INTO room_types (
                property_id, name, max_guests, quantity, 
                price, discount_price, size, description, amenities, bed_size, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $roomImageStmt = $pdo->prepare("
            INSERT INTO room_images (room_type_id, image_path, is_main, created_at) 
            VALUES (?, ?, ?, NOW())
        ");

        foreach ($_POST['room_types'] as $index => $roomType) {
            $roomRequiredFields = [
                'name' => 'Room name',
                'max_guests' => 'Max guests',
                'quantity' => 'Quantity',
                'price' => 'Price',
                'bed_size' => 'Bed size'
            ];

            $missingRoomFields = [];
            foreach ($roomRequiredFields as $field => $name) {
                if (empty($roomType[$field])) {
                    $missingRoomFields[] = $name;
                }
            }

            if (!empty($missingRoomFields)) {
                error_log("Skipping room type $index - missing fields: " . implode(', ', $missingRoomFields));
                continue;
            }

            $roomAmenities = !empty($roomType['amenities']) && is_array($roomType['amenities']) ? 
                implode(',', array_map('sanitizeInput', $roomType['amenities'])) : '';

            $roomStmt->execute([
                $propertyId,
                sanitizeInput($roomType['name']),
                filter_var($roomType['max_guests'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]),
                filter_var($roomType['quantity'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]),
                filter_var($roomType['price'], FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0]]),
                !empty($roomType['discount_price']) ? 
                    filter_var($roomType['discount_price'], FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0]]) : null,
                !empty($roomType['size']) ? 
                    filter_var($roomType['size'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) : null,
                !empty($roomType['description']) ? sanitizeInput($roomType['description']) : '',
                $roomAmenities,
                sanitizeInput($roomType['bed_size'])
            ]);

            $roomId = $pdo->lastInsertId();

            if (!empty($_FILES['room_types']['name'][$index]['main_image'])) {
                try {
                    $roomMainImage = [
                        'name' => $_FILES['room_types']['name'][$index]['main_image'],
                        'type' => $_FILES['room_types']['type'][$index]['main_image'],
                        'tmp_name' => $_FILES['room_types']['tmp_name'][$index]['main_image'],
                        'error' => $_FILES['room_types']['error'][$index]['main_image'],
                        'size' => $_FILES['room_types']['size'][$index]['main_image']
                    ];
                    
                    $mainImagePath = uploadFiles(
                        $roomMainImage,
                        "uploads/properties/{$propertyId}/rooms/{$roomId}/"
                    );
                    
                    if (!empty($mainImagePath)) {
                        $roomImageStmt->execute([$roomId, $mainImagePath[0], 1]);
                    }
                } catch (Exception $e) {
                    error_log("Room main image upload failed for room $roomId: " . $e->getMessage());
                }
            }

            if (!empty($_FILES['room_types']['name'][$index]['images'])) {
                try {
                    $roomImages = [
                        'name' => $_FILES['room_types']['name'][$index]['images'],
                        'type' => $_FILES['room_types']['type'][$index]['images'],
                        'tmp_name' => $_FILES['room_types']['tmp_name'][$index]['images'],
                        'error' => $_FILES['room_types']['error'][$index]['images'],
                        'size' => $_FILES['room_types']['size'][$index]['images']
                    ];
                    
                    $additionalImages = uploadFiles(
                        $roomImages,
                        "uploads/properties/{$propertyId}/rooms/{$roomId}/"
                    );
                    
                    foreach ($additionalImages as $imagePath) {
                        $roomImageStmt->execute([$roomId, $imagePath, 0]);
                    }
                } catch (Exception $e) {
                    error_log("Room additional images upload failed for room $roomId: " . $e->getMessage());
                    
                }
            }
        }
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Property added successfully',
        'property_id' => $propertyId,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'error_code' => $e->getCode()
    ]);
    error_log("Database error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    
} catch (Exception $e) {
    http_response_code($e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => $e->getCode()
    ]);
    error_log("Error: " . $e->getMessage());
}