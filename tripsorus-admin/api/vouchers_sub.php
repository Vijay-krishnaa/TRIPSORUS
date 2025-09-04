<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
require_once '../../db.php';
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$pathInfo = $_SERVER['PATH_INFO'] ?? '';  
$request = explode('/', trim($pathInfo, '/'));
$entity = array_shift($request);
$id = array_shift($request);

try {
    switch ($method) {
        case 'GET':
            if ($entity == 'vouchers') {
                if ($id) {
                    getVoucher($id);
                } else {
                    getVouchers();
                }
            } elseif ($entity == 'promotions') {
                if ($id) {
                    getPromotion($id);
                } else {
                    getPromotions();
                }
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint not found']);
            }
            break;
            
        case 'POST':
            if ($entity == 'vouchers') {
                createVouchers();
            } elseif ($entity == 'promotions') {
                createPromotion();
            } elseif ($entity == 'validate-voucher') {
                validateVoucher();
            } elseif ($entity == 'apply-promotions') {
                applyPromotions();
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint not found']);
            }
            break;
            
        case 'PUT':
            if ($entity == 'vouchers' && $id) {
                updateVoucher($id);
            } elseif ($entity == 'promotions' && $id) {
                updatePromotion($id);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint not found']);
            }
            break;
            
        case 'DELETE':
            if ($entity == 'vouchers' && $id) {
                deleteVoucher($id);
            } elseif ($entity == 'promotions' && $id) {
                deletePromotion($id);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint not found']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

function getVouchers() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT * FROM vouchers ORDER BY created_at DESC");
        $vouchers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($vouchers);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch vouchers: ' . $e->getMessage()]);
    }
}
function getVoucher($id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM vouchers WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $voucher = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($voucher) {
            echo json_encode($voucher);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Voucher not found']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch voucher: ' . $e->getMessage()]);
    }
}
function createVouchers() {
    global $pdo;
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || !isset($data['vouchers'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid data']);
        return;
    }
    try {
        $pdo->beginTransaction();  
        $stmt = $pdo->prepare("
            INSERT INTO vouchers 
            (code, discount_type, discount_value, expiry_date, max_usage, status)
            VALUES (:code, :type, :value, :expiry, :max_usage, :status)
        ");
        foreach ($data['vouchers'] as $voucher) {
            $stmt->execute([
                ':code' => $voucher['code'],
                ':type' => $voucher['discountType'],
                ':value' => $voucher['discountValue'],
                ':expiry' => $voucher['expiryDate'],
                ':max_usage' => $voucher['maxUsage'],
                ':status' => 'active'
            ]);
        }
        
        $pdo->commit();
        
        http_response_code(201);
        echo json_encode(['success' => true, 'message' => 'Vouchers created successfully']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create vouchers: ' . $e->getMessage()]);
    }
}
function updateVoucher($id) {
    global $pdo;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid data']);
        return;
    }
    
    try {
        $fields = [];
        $params = [':id' => $id];
        
        if (isset($data['code'])) {
            $fields[] = 'code = :code';
            $params[':code'] = $data['code'];
        }
        
        if (isset($data['discountType'])) {
            $fields[] = 'discount_type = :type';
            $params[':type'] = $data['discountType'];
        }
        
        if (isset($data['discountValue'])) {
            $fields[] = 'discount_value = :value';
            $params[':value'] = $data['discountValue'];
        }
        
        if (isset($data['expiryDate'])) {
            $fields[] = 'expiry_date = :expiry';
            $params[':expiry'] = $data['expiryDate'];
        }
        
        if (isset($data['maxUsage'])) {
            $fields[] = 'max_usage = :max_usage';
            $params[':max_usage'] = $data['maxUsage'];
        }
        
        if (isset($data['usedCount'])) {
            $fields[] = 'used_count = :used_count';
            $params[':used_count'] = $data['usedCount'];
        }
        
        if (isset($data['status'])) {
            $fields[] = 'status = :status';
            $params[':status'] = $data['status'];
        }
        
        if (empty($fields)) {
            http_response_code(400);
            echo json_encode(['error' => 'No fields to update']);
            return;
        }
        
        $sql = "UPDATE vouchers SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Voucher updated successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Voucher not found']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update voucher: ' . $e->getMessage()]);
    }
}
function deleteVoucher($id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM vouchers WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Voucher deleted successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Voucher not found']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete voucher: ' . $e->getMessage()]);
    }
}

function validateVoucher() {
    global $pdo;
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || !isset($data['voucherCode']) || !isset($data['bookingTotal'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid data. voucherCode and bookingTotal are required']);
        return;
    }
    $voucherCode = $data['voucherCode'];
    $bookingTotal = floatval($data['bookingTotal']);
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM vouchers WHERE code = :code AND status = 'active'");
        $stmt->execute([':code' => $voucherCode]);
        $voucher = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$voucher) {
            echo json_encode([
                'valid' => false,
                'message' => 'Voucher code not found'
            ]);
            return;
        }
        if (strtotime($voucher['expiry_date']) < time()) {
            echo json_encode([
                'valid' => false,
                'message' => 'Voucher has expired'
            ]);
            return;
        }
        if ($voucher['used_count'] >= $voucher['max_usage']) {
            echo json_encode([
                'valid' => false,
                'message' => 'Voucher usage limit reached'
            ]);
            return;
        }
        $discountAmount = 0;
        if ($voucher['discount_type'] == 'percentage') {
            $discountAmount = $bookingTotal * ($voucher['discount_value'] / 100);
        } else {
            $discountAmount = min($bookingTotal, $voucher['discount_value']);
        }
        $newTotal = $bookingTotal - $discountAmount;
        $updateStmt = $pdo->prepare("UPDATE vouchers SET used_count = used_count + 1 WHERE id = :id");
        $updateStmt->execute([':id' => $voucher['id']]);
        
        echo json_encode([
            'valid' => true,
            'discountAmount' => round($discountAmount, 2),
            'newTotal' => round($newTotal, 2),
            'voucher' => $voucher
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to validate voucher: ' . $e->getMessage()]);
    }
}

function getPromotions() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT * FROM promotions ORDER BY created_at DESC");
        $promotions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($promotions);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch promotions: ' . $e->getMessage()]);
    }
}
function getPromotion($id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM promotions WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $promotion = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($promotion) {
            echo json_encode($promotion);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Promotion not found']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch promotion: ' . $e->getMessage()]);
    }
}

function createPromotion() {
    global $pdo;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['promotion'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid data']);
        return;
    }
    
    $promotion = $data['promotion'];
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO promotions 
            (name, type, discount_type, discount_value, min_stay_days, days_before_checkin, additional_discount, status)
            VALUES (:name, :type, :discount_type, :discount_value, :min_stay_days, :days_before_checkin, :additional_discount, :status)
        ");
        
        $stmt->execute([
            ':name' => $promotion['name'],
            ':type' => $promotion['type'],
            ':discount_type' => $promotion['discountType'],
            ':discount_value' => $promotion['discountValue'],
            ':min_stay_days' => $promotion['minStayDays'] ?? null,
            ':days_before_checkin' => $promotion['daysBeforeCheckin'] ?? null,
            ':additional_discount' => $promotion['additionalDiscount'] ?? 0,
            ':status' => 'active'
        ]);
        
        $promotionId = $pdo->lastInsertId();
        
        http_response_code(201);
        echo json_encode([
            'success' => true, 
            'message' => 'Promotion created successfully',
            'id' => $promotionId
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create promotion: ' . $e->getMessage()]);
    }
}

function updatePromotion($id) {
    global $pdo;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid data']);
        return;
    }
    
    try {
        $fields = [];
        $params = [':id' => $id];
        
        if (isset($data['name'])) {
            $fields[] = 'name = :name';
            $params[':name'] = $data['name'];
        }
        
        if (isset($data['type'])) {
            $fields[] = 'type = :type';
            $params[':type'] = $data['type'];
        }
        
        if (isset($data['discountType'])) {
            $fields[] = 'discount_type = :discount_type';
            $params[':discount_type'] = $data['discountType'];
        }
        
        if (isset($data['discountValue'])) {
            $fields[] = 'discount_value = :discount_value';
            $params[':discount_value'] = $data['discountValue'];
        }
        
        if (isset($data['minStayDays'])) {
            $fields[] = 'min_stay_days = :min_stay_days';
            $params[':min_stay_days'] = $data['minStayDays'];
        }
        
        if (isset($data['daysBeforeCheckin'])) {
            $fields[] = 'days_before_checkin = :days_before_checkin';
            $params[':days_before_checkin'] = $data['daysBeforeCheckin'];
        }
        
        if (isset($data['additionalDiscount'])) {
            $fields[] = 'additional_discount = :additional_discount';
            $params[':additional_discount'] = $data['additionalDiscount'];
        }
        
        if (isset($data['status'])) {
            $fields[] = 'status = :status';
            $params[':status'] = $data['status'];
        }
        
        if (empty($fields)) {
            http_response_code(400);
            echo json_encode(['error' => 'No fields to update']);
            return;
        }
        
        $sql = "UPDATE promotions SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Promotion updated successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Promotion not found']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update promotion: ' . $e->getMessage()]);
    }
}

function deletePromotion($id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM promotions WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Promotion deleted successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Promotion not found']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete promotion: ' . $e->getMessage()]);
    }
}

function applyPromotions() {
    global $pdo;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['bookingDate']) || !isset($data['checkInDate']) || !isset($data['stayDuration'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid data. bookingDate, checkInDate, and stayDuration are required']);
        return;
    }
    $bookingDate = $data['bookingDate'];
    $checkInDate = $data['checkInDate'];
    $stayDuration = intval($data['stayDuration']);
    $isLoggedIn = isset($data['isLoggedIn']) ? (bool)$data['isLoggedIn'] : false;
    
    try {
        $daysBeforeCheckin = floor((strtotime($checkInDate) - strtotime($bookingDate)) / (60 * 60 * 24));
        $stmt = $pdo->query("SELECT * FROM promotions WHERE status = 'active'");
        $allPromotions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $applicablePromotions = [];
        
        foreach ($allPromotions as $promotion) {
            $isApplicable = false;
            
            switch ($promotion['type']) {
                case 'last-minute':
                    if ($daysBeforeCheckin <= 3) {
                        $isApplicable = true;
                    }
                    break;
                    
                case 'early-bird':
                    if ($daysBeforeCheckin >= 5) {
                        $isApplicable = true;
                    }
                    break;
                    
                case 'long-stay':
                    if ($stayDuration >= $promotion['min_stay_days']) {
                        $isApplicable = true;
                    }
                    break;
            }
            
            if ($isApplicable) {
                $totalDiscount = $promotion['discount_value'];
                if ($isLoggedIn && $promotion['additional_discount'] > 0) {
                    $totalDiscount += $promotion['additional_discount'];
                }
                
                $applicablePromotions[] = [
                    'id' => $promotion['id'],
                    'name' => $promotion['name'],
                    'type' => $promotion['type'],
                    'discountType' => $promotion['discount_type'],
                    'discountValue' => $totalDiscount,
                    'originalDiscount' => $promotion['discount_value'],
                    'additionalDiscount' => $isLoggedIn ? $promotion['additional_discount'] : 0
                ];
            }
        }
        
        echo json_encode([
            'applicablePromotions' => $applicablePromotions,
            'daysBeforeCheckin' => $daysBeforeCheckin,
            'stayDuration' => $stayDuration
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to apply promotions: ' . $e->getMessage()]);
    }
}
?>