<?php
session_start();
require_once 'db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $password   = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $user_type  = $_POST['user_type'] ?? 'user';

    // Basic validations
    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
        echo "<script>alert('All fields are required'); window.history.back();</script>";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format'); window.history.back();</script>";
        exit;
    }

    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match'); window.history.back();</script>";
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO user (first_name, last_name, email, password, user_type, phone) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$first_name, $last_name, $email, $hashed_password, $user_type, $phone]);

    
        echo "<script>
            alert('Signup successful! Please log in with your credentials.');
            window.location.href = 'index.php';
        </script>";
        exit;

    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            echo "<script>alert('Duplicate email detected. Please use a different email.'); window.history.back();</script>";
        } else {
            echo "<script>alert('Registration failed: " . $e->getMessage() . "'); window.history.back();</script>";
        }
        exit;
    }
}
?>
