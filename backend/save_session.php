<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
include 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed"]);
    exit;
}

$json = file_get_contents("php://input");
$data = json_decode($json, true);

// 4 ID-nin hamısını yoxlayırıq
if (!isset(
    $data['startDateTime'],
    $data['endDateTime'],
    $data['mileage'],
    $data['weatherId'],
    $data['roadTypeId'],
    $data['visibilityId'], // YENİ
    $data['trafficId']     // YENİ
)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit;
}

$start = $data['startDateTime'];
$end = $data['endDateTime'];
$mileage = $data['mileage'];

// Bütün ID-ləri tam ədədə çeviririk
$weather_id = (int)$data['weatherId'];
$road_id = (int)$data['roadTypeId'];
$visibility_id = (int)$data['visibilityId'];
$traffic_id = (int)$data['trafficId'];

try {
    $conn->beginTransaction();

    // 1. DrivingSession yarat
    // DİQQƏT: Sxemə görə visibility_id və weather_condition_id bura birbaşa yazılır
    $sql = "INSERT INTO DrivingSession (start_date, end_date, mileage, visibility_id, weather_condition_id) 
            VALUES (:start, :end, :mileage, :vis, :weather)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':start' => $start,
        ':end' => $end,
        ':mileage' => $mileage,
        ':vis' => $visibility_id, // Formdan gələn real ID
        ':weather' => $weather_id
    ]);

    // Auto Increment ilə yaranan ID-ni götürürük
    $session_id = $conn->lastInsertId();

    // 2. RoadType əlaqəsi (OccursOn cədvəli)
    $stmt = $conn->prepare("INSERT INTO OccursOn (session_id, road_type_id) VALUES (:sess, :road)");
    $stmt->execute([':sess' => $session_id, ':road' => $road_id]);

    // 3. TrafficCondition əlaqəsi (TakesPlace cədvəli)
    $stmt = $conn->prepare("INSERT INTO TakesPlace (session_id, traffic_condition_id) VALUES (:sess, :traffic)");
    $stmt->execute([':sess' => $session_id, ':traffic' => $traffic_id]);

    $conn->commit();
    echo json_encode(["success" => true, "session_id" => $session_id]);

} catch (Exception $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>