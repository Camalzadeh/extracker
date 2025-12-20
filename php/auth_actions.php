<?php
session_start();
require_once "db.php";

$action = $_POST['action'] ?? '';

if ($action === 'register') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // Basic Validation
    if ($password !== $confirm) {
        $_SESSION['error'] = "Passwords do not match!";
        header("Location: ../index.php");
        exit;
    }

    // Check if user exists
    $stmt = $conn->prepare("SELECT user_id FROM Users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        $_SESSION['error'] = "Username or Email already exists!";
        header("Location: ../index.php");
        exit;
    }

    // Insert User
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO Users (username, email, password_hash) VALUES (?, ?, ?)");
    
    try {
        $stmt->execute([$username, $email, $hash]);
        $_SESSION['user_id'] = $conn->lastInsertId();
        $_SESSION['username'] = $username;
        header("Location: ../dashboard.php");
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = "Registration failed: " . $e->getMessage();
        header("Location: ../index.php");
        exit;
    }

} elseif ($action === 'login') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, username, password_hash FROM Users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        header("Location: ../dashboard.php");
        exit;
    } else {
        $_SESSION['error'] = "Invalid username or password!";
        header("Location: ../index.php");
        exit;
    }
} else {
    header("Location: ../index.php");
    exit;
}
