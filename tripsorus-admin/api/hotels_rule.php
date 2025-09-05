<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../../db.php'; 

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Get input data
$input = json_decode(file_get_contents('php://input'), true);

// API endpoints
$request = $_GET['request'] ?? '';

try {
    switch ($method) {
        case 'GET':
            if ($request === 'categories') {
                // Get all categories
                $stmt = $pdo->query("SELECT * FROM rule_categories ORDER BY name");
                $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($categories);
            } elseif ($request === 'rules') {
                // Get all rules with their items
                $stmt = $pdo->query("
                    SELECT r.*, c.name as category_name, c.slug as category_slug,
                           GROUP_CONCAT(ri.content ORDER BY ri.item_order SEPARATOR '|||') as items
                    FROM rules r
                    JOIN rule_categories c ON r.category_id = c.id
                    LEFT JOIN rule_items ri ON r.id = ri.rule_id
                    GROUP BY r.id
                    ORDER BY c.name, r.title
                ");
                $rules = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Format the items as arrays
                foreach ($rules as &$rule) {
                    $rule['items'] = $rule['items'] ? explode('|||', $rule['items']) : [];
                }
                
                echo json_encode($rules);
            } else {
                echo json_encode(['error' => 'Invalid request']);
            }
            break;
            
        case 'POST':
            if ($request === 'add_rule') {
                // Add a new rule with items
                if (!isset($input['category_id'], $input['title'], $input['items'])) {
                    echo json_encode(['error' => 'Missing required fields']);
                    exit;
                }
                
                $pdo->beginTransaction();
                
                try {
                    // Insert the rule
                    $stmt = $pdo->prepare("INSERT INTO rules (category_id, title) VALUES (?, ?)");
                    $stmt->execute([$input['category_id'], $input['title']]);
                    $ruleId = $pdo->lastInsertId();
                    
                    // Insert rule items
                    $stmt = $pdo->prepare("INSERT INTO rule_items (rule_id, content, item_order) VALUES (?, ?, ?)");
                    $order = 0;
                    foreach ($input['items'] as $item) {
                        if (!empty(trim($item))) {
                            $stmt->execute([$ruleId, trim($item), $order++]);
                        }
                    }
                    
                    $pdo->commit();
                    echo json_encode(['success' => true, 'rule_id' => $ruleId]);
                } catch (Exception $e) {
                    $pdo->rollBack();
                    echo json_encode(['error' => 'Failed to add rule: ' . $e->getMessage()]);
                }
            } else {
                echo json_encode(['error' => 'Invalid request']);
            }
            break;
            
        case 'DELETE':
            if ($request === 'delete_rule' && isset($_GET['id'])) {
                // Delete a rule
                $stmt = $pdo->prepare("DELETE FROM rules WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['error' => 'Rule not found']);
                }
            } else {
                echo json_encode(['error' => 'Invalid request']);
            }
            break;
            
        default:
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}