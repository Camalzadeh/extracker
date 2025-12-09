<?php
// LandingPage.php
session_start(); // [cite: 3]

// Köhnə sessiyanı təmizləyirik (PDF tələbi: session-destroy) [cite: 5]
session_unset();
session_destroy();

session_start(); // Yeni sessiya
$_SESSION['user'] = "Eric"; // "Hello Eric" üçün hazırlıq [cite: 6]
?>

<!DOCTYPE html>
<html>
<head>
    <title>HWP MockUp - Giriş</title> </head>
<body>
<h1>Xoş gəlmisiniz!</h1>
<p>Zəhmət olmasa davam etmək üçün aşağıdakı linkə daxil olun.</p>

<a href="dashboard.php">Dashboard-a keçid</a>
</body>
</html>