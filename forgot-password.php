<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<script>alert('Please provide a valid email address.'); window.history.back();</script>";
    exit;
  }

  try {
    $stmt = $pdo->prepare("SELECT id, first_name FROM user WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
      $token = bin2hex(random_bytes(50));
      $expires = gmdate("Y-m-d H:i:s", time() + 3600); // 1 hour
      $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$user['id']]);
      $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)")
        ->execute([$user['id'], $token, $expires]);

      $resetLink = "http://localhost/TRIPSORUS/reset-password.php?token=$token";

      $mail = new PHPMailer(true);
      $mail->isSMTP();
      $mail->Host = "smtpout.secureserver.net";
      $mail->SMTPAuth = true;
      $mail->Username = "noreply@tripsorus.com";
      $mail->Password = "bablupd@1996";
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port = 587;
      $mail->setFrom("noreply@tripsorus.com", "TRIPSORUS");
      $mail->addAddress($email, $user['first_name']);
      $mail->isHTML(true);
      $mail->Subject = "Reset Your Password - TRIPSORUS";
      $mail->Body = "
                <h2>Password Reset Request</h2>
                <p>Hi {$user['first_name']},</p>
                <p>Click the link below to reset your password:</p>
                <a href='$resetLink'>Reset Password</a>
                <p>This link is valid for 1 hour.</p>
                <p>If you didn't request this, please ignore this email.</p>
            ";
      $mail->SMTPOptions = [
        'ssl' => [
          'verify_peer' => false,
          'verify_peer_name' => false,
          'allow_self_signed' => true
        ]
      ];
      $mail->send();
    }
    echo "<script>
            alert('A reset link has been sent succesfully.');
            window.location.href = 'index.php';
        </script>";
    exit;

  } catch (Exception $e) {
    error_log('Forgot password error: ' . $e->getMessage());
    echo "<script>alert('An error occurred. Please try again later.'); window.history.back();</script>";
    exit;
  }
}
?>