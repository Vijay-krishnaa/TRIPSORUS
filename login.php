<?php
session_start();
require_once 'db.php'; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['loginEmail'] ?? '');
    $password = $_POST['loginPassword'] ?? '';

    if (empty($email) || empty($password)) {
        echo "<script>alert('Both email and password are required'); window.history.back();</script>";
        exit;
    }
    $stmt = $pdo->prepare("SELECT * FROM user WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['user_type'] = $user['user_type'];
        if ($user['user_type'] === 'super_admin') {
            echo "<script>alert('Login successful'); window.location.href='super_admin/index.php';</script>";
        } elseif ($user['user_type'] === 'admin') {
            echo "<script>alert('Login successful'); window.location.href='tripsorus-admin/index.php';</script>";
        } else {
            echo "<script>alert('Login successful'); window.location.href='index.php';</script>";
        }
        exit;
    } else {
        echo "<script>alert('Invalid email or password'); window.history.back();</script>";
        exit;
    }
}
?>