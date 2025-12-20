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
        $startTime = $_POST['start_time']; // Matches start_date in DB
        $endTime = $_POST['end_time'];     // Matches end_date in DB
        $distance = $_POST['distance'] ?? 0;
        $visibilityId = $_POST['visibility'];
        $weatherId = $_POST['weather'];
        $trafficId = $_POST['traffic'];
        $roadId = $_POST['road_type'];
        // Note: 'notes' field is not supported in the current user schema, so we skip it.
        
        // 1. Save Session to DrivingSession
        // Schema: session_id, user_id, start_date, end_date, mileage, visibility_id, weather_condition_id
        $stmt = $conn->prepare("
            INSERT INTO DrivingSession 
            (user_id, start_date, end_date, mileage, visibility_id, weather_condition_id) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $startTime, $endTime, $distance, $visibilityId, $weatherId]);
        $sessionId = $conn->lastInsertId();

        // 2. Save Road Type to OccursOn
        // Schema: session_id, road_type_id
        $stmtOccurs = $conn->prepare("INSERT INTO OccursOn (session_id, road_type_id) VALUES (?, ?)");
        $stmtOccurs->execute([$sessionId, $roadId]);

        // 3. Save Traffic Condition to TakesPlace
        // Schema: session_id, traffic_condition_id
        $stmtTakes = $conn->prepare("INSERT INTO TakesPlace (session_id, traffic_condition_id) VALUES (?, ?)");
        $stmtTakes->execute([$sessionId, $trafficId]);

        // 4. Save Route Points (passed as JSON string)
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
