<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();

        $userId = $_SESSION['user_id'];
        $startTime = $_POST['start_time'];
        $endTime = $_POST['end_time'];
        $distance = $_POST['distance'] ?? 0;
        
        // Validation: Prevent empty live trips
        $tripType = $_POST['trip_type'] ?? 'manual';
        if ($tripType === 'live' && (float)$distance <= 0) {
            throw new Exception("Cannot save a live trip with 0 km distance.");
        }
        $visibilityId = $_POST['visibility'];
        $weatherId = $_POST['weather'];
        $trafficId = $_POST['traffic'];
        $roadId = $_POST['road_type'];
        
        $stmt = $conn->prepare("
            INSERT INTO DrivingSession 
            (user_id, start_date, end_date, mileage, visibility_id, weather_condition_id) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $startTime, $endTime, $distance, $visibilityId, $weatherId]);
        $sessionId = $conn->lastInsertId();

        $roadData = $_POST['road_type'];
        if (!is_array($roadData)) {
            $roadData = [$roadData];
        }
        
        $stmtOccurs = $conn->prepare("INSERT INTO OccursOn (session_id, road_type_id) VALUES (?, ?)");
        foreach ($roadData as $rId) {
            $stmtOccurs->execute([$sessionId, $rId]);
        }

        $trafficData = $_POST['traffic'];
        if (!is_array($trafficData)) {
            $trafficData = [$trafficData];
        }

        $stmtTakes = $conn->prepare("INSERT INTO TakesPlace (session_id, traffic_condition_id) VALUES (?, ?)");
        foreach ($trafficData as $tId) {
            $stmtTakes->execute([$sessionId, $tId]);
        }

        $routePointsJson = $_POST['route_points'] ?? '[]';
        $routePoints = json_decode($routePointsJson, true);

        if (is_array($routePoints)) {
            $pointStmt = $conn->prepare("INSERT INTO RoutePoints (session_id, latitude, longitude, timestamp) VALUES (?, ?, ?, ?)");
            foreach ($routePoints as $point) {
                // Ensure timestamp exists, or use current
                $ts = $point['timestamp'] ?? date('Y-m-d H:i:s');
                $pointStmt->execute([$sessionId, $point['lat'], $point['lng'], $ts]);
            }
        }

        $conn->commit();
        header("Location: ../dashboard.php");
        exit;

    } catch (Exception $e) {
        $conn->rollBack();
        die("Error saving trip: " . $e->getMessage());
    }
}
?>
