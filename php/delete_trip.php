<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id']) || !isset($_POST['session_id'])) {
    header("Location: ../dashboard.php");
    exit;
}

$sessionId = $_POST['session_id'];
$userId = $_SESSION['user_id'];

try {
    $checkStmt = $conn->prepare("SELECT user_id FROM DrivingSession WHERE session_id = ?");
    $checkStmt->execute([$sessionId]);
    $trip = $checkStmt->fetch();

    if ($trip && $trip['user_id'] == $userId) {
        $conn->beginTransaction();

        $conn->prepare("DELETE FROM RoutePoints WHERE session_id = ?")->execute([$sessionId]);
        $conn->prepare("DELETE FROM OccursOn WHERE session_id = ?")->execute([$sessionId]);
        $conn->prepare("DELETE FROM TakesPlace WHERE session_id = ?")->execute([$sessionId]);
        
        $conn->prepare("DELETE FROM DrivingSession WHERE session_id = ?")->execute([$sessionId]);

        $conn->commit();
    }
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
}

$page = $_POST['redirect_page'] ?? 'dashboard';
header("Location: ../dashboard.php?page=" . $page);
exit;
?>
