<?php
require_once 'db.php';

$results = [];

if (isset($_GET['q'])) {
  $q = trim($_GET['q']);

  if (!empty($q)) {
    try {
      $stmt = $pdo->prepare("
                SELECT suggestion 
                FROM (
                    SELECT DISTINCT name AS suggestion
                    FROM properties
                    WHERE status='approved' AND name LIKE :q1

                    UNION

                    SELECT DISTINCT city AS suggestion
                    FROM properties
                    WHERE status='approved' AND city LIKE :q2
                ) AS combined
                LIMIT 10
            ");

      $stmt->execute([
        ':q1' => "%$q%",
        ':q2' => "%$q%"
      ]);

      $results = $stmt->fetchAll(PDO::FETCH_COLUMN);

    } catch (PDOException $e) {
      echo json_encode(["error" => $e->getMessage()]);
      exit;
    }
  }
}

echo json_encode($results);