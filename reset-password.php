<?php
require 'db.php';
$token = $_GET['token'] ?? '';
if (empty($token)) {
  die("No token provided.");
}

$error = '';
$success = '';

try {
  $stmt = $pdo->prepare("
        SELECT pr.user_id, pr.used_at, u.email 
        FROM password_resets pr
        JOIN user u ON pr.user_id = u.id
        WHERE pr.token = ? AND pr.expires_at > UTC_TIMESTAMP()
    ");
  $stmt->execute([$token]);
  $resetRequest = $stmt->fetch();

  if (!$resetRequest || $resetRequest['used_at'] !== null) {
    die("Invalid or expired token.");
  }
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password !== $confirm) {
      $error = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
      $error = "Password must be at least 8 characters long.";
    } else {
      $hashed = password_hash($password, PASSWORD_DEFAULT);
      $pdo->prepare("UPDATE user SET password = ? WHERE id = ?")
        ->execute([$hashed, $resetRequest['user_id']]);
      $pdo->prepare("UPDATE password_resets SET used_at = UTC_TIMESTAMP() WHERE token = ?")
        ->execute([$token]);

      $success = "Password reset successfully! Redirecting to login...";
      header("refresh:2;url=index.php");
    }
  }

} catch (PDOException $e) {
  error_log("Reset password error: " . $e->getMessage());
  die("An error occurred. Please try again.");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Reset Password</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <div class="container mt-5">
    <div class="card mx-auto" style="max-width: 400px;">
      <div class="card-body">
        <h4 class="card-title text-center mb-3">Reset Your Password</h4>

        <?php if ($error): ?>
          <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
          <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (!$success): ?>
          <form method="POST">
            <div class="mb-3">
              <label>New Password</label>
              <input type="password" name="password" class="form-control" required minlength="8">
            </div>
            <div class="mb-3">
              <label>Confirm Password</label>
              <input type="password" name="confirm_password" class="form-control" required minlength="8">
            </div>
            <button type="submit" class="btn btn-primary w-100">Reset Password</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>