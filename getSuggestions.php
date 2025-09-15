<?php
require_once 'db.php';

$results = [];

if (isset($_GET['q'])) {
  $q = trim($_GET['q']);

  if (!empty($q)) {
    try {
      $stmt = $pdo->prepare("
                SELECT DISTINCT name AS suggestion 
                FROM properties
                WHERE status='approved' AND name LIKE :q1
                UNION
                SELECT DISTINCT city AS suggestion
                FROM properties
                WHERE status='approved' AND city LIKE :q2
                UNION
                SELECT DISTINCT address AS suggestion
                FROM properties
                WHERE status='approved' AND address LIKE :q3
                LIMIT 10
            ");

      $stmt->execute([
        ':q1' => "%$q%",
        ':q2' => "%$q%",
        ':q3' => "%$q%"
      ]);

      $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
      echo json_encode(["error" => $e->getMessage()]);
      exit;
    }
  }
}

echo json_encode($results);