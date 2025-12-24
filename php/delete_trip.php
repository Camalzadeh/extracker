<?php
session_start();
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/classes/TripManager.php";

if (!isset($_SESSION['user_id']) || !isset($_POST['session_id'])) {
    header("Location: ../dashboard.php");
    exit;
}

$sessionId = $_POST['session_id'];
$userId = $_SESSION['user_id'];
$page = $_POST['redirect_page'] ?? 'dashboard';

$manager = new TripManager($conn);
$manager->deleteTrip($sessionId, $userId);

header("Location: ../dashboard.php?page=" . $page);
exit;
?>
