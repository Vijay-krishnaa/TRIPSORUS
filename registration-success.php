<?php
session_start();
$user_type = $_GET['user_type'] ?? 'user';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Registration Successful</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container mt-5">
    <div class="alert alert-success text-center">
      <h4 class="alert-heading">🎉 Registration Successful!</h4>
      <p>You’ve successfully registered as a <strong><?php echo htmlspecialchars($user_type); ?></strong>.</p>

      <?php if ($user_type === 'admin'): ?>
        <p>You can now <a href="index.php">log in as an admin</a> from the homepage.</p>
      <?php else: ?>
        <p>You can now <a href="login.php">log in</a> to your dashboard.</p>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
