<?php
require_once '../../db.php';
session_start();

header('Content-Type: application/json');

try {
    // Ensure admin is logged in
    if (!isset($_SESSION['user_id'])) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized: Please login as admin'
        ]);
        exit();
    }

    $adminId = $_SESSION['user_id'];

    // Get query parameters
    $search = $_GET['search'] ?? '';
    $type = $_GET['type'] ?? '';
    $city = $_GET['city'] ?? '';
    $page = (int)($_GET['page'] ?? 1);
    $perPage = (int)($_GET['per_page'] ?? 5);

    $offset = ($page - 1) * $perPage;

    // Build query with admin filter + active status
    $query = "SELECT 
                p.id,
                CONCAT('#PR-', LPAD(p.id, 4, '0')) as property_id,
                p.name as property_name,
                p.type as property_type,
                p.description,
                CONCAT(p.city, ', ', p.country) as location,
                p.city,
                'active' as status,
                (SELECT MIN(price) FROM room_types WHERE property_id = p.id) as min_price,
                (SELECT COUNT(*) FROM room_types WHERE property_id = p.id) as room_count,
                (SELECT image_path FROM property_images WHERE property_id = p.id LIMIT 1) as main_image
              FROM properties p
              WHERE p.admin_id = :admin_id";   // ✅ Only show this admin’s properties

    $params = [':admin_id' => $adminId];
    
    if (!empty($search)) {
        $query .= " AND (p.name LIKE :search OR p.description LIKE :search OR p.city LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    if (!empty($type)) {
        $query .= " AND p.type = :type";
        $params[':type'] = $type;
    }
    
    if (!empty($city)) {
        $query .= " AND p.city = :city";
        $params[':city'] = $city;
    }

    // Count query
    $countQuery = "SELECT COUNT(*) as total FROM ($query) as count_query";
    $countStmt = $pdo->prepare($countQuery);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $totalResult = $countStmt->fetch(PDO::FETCH_ASSOC);
    $totalProperties = $totalResult['total'];

    // Main query with pagination
    $query .= " ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($query);

    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $properties,
        'pagination' => [
            'total' => $totalProperties,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($totalProperties / $perPage)
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
