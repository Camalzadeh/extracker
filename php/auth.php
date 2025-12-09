<?php
session_start();
// db_config.php bağlantısını daxil edin
require_once 'db_config.php';

if ($conn === null) {
    // DB bağlantı xətası halında təkrar əlaqə cəhdi və ya fatal xəta mesajı
    header("Location: ../html/auth.html?error=server_error");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $action = $_POST['action'] ?? '';

    // LOGIN Prosesi (Username ilə)
    if ($action === 'login') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            header("Location: ../html/auth.html?error=login_empty");
            exit;
        }

        try {
            $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];

                // Başarılı yönlendirme (Dashboard)
                header("Location: ../html/dashboard.html");
                exit;
            } else {
                header("Location: ../html/auth.html?error=invalid_credentials");
                exit;
            }
        } catch (PDOException $e) {
            error_log("Login DB Error: " . $e->getMessage());
            header("Location: ../html/auth.html?error=server_error");
            exit;
        }
    }

    // REGISTER Prosesi
    elseif ($action === 'register') {
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
            header("Location: ../html/auth.html?error=register_empty");
            exit;
        }
        if ($password !== $confirmPassword) {
            header("Location: ../html/auth.html?error=password_mismatch");
            exit;
        }

        try {
            // İstifadəçi adı/Email mövcudluğunu yoxlamaq
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);

            if ($stmt->fetch()) {
                header("Location: ../html/auth.html?error=user_exists");
                exit;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Yeni istifadəçini daxil etmək
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hashedPassword]);

            // Başarılı qeydiyyatdan sonra Login səhifəsinə yönləndirmə
            header("Location: ../html/auth.html?success=registered");
            exit;

        } catch (PDOException $e) {
            error_log("Register DB Error: " . $e->getMessage());
            header("Location: ../html/auth.html?error=server_error");
            exit;
        }
    }
}

// Bütün digər sorğular üçün ana səhifəyə yönləndirmə
header("Location: ../html/auth.html");
exit;