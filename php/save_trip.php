<?php
session_start();
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/classes/TripManager.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    
    if ($tripType === 'live' && (float)$distance < 0) {
        die("Invalid distance value.");
    }
    
    $manager = new TripManager($conn);
    try {
        $manager->saveTrip($_POST, $userId);
        header("Location: ../dashboard.php");
        exit;
    } catch (Exception $e) {
        die("Error saving trip: " . $e->getMessage());
    }
}
?>
