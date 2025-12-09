<?php
header('Content-Type: application/json');
include 'db_config.php';

$response = [
    'weatherOptions' => [],
    'roadConditionOptions' => [],
    'visibilityOptions' => [], // YENİ
    'trafficOptions' => [],    // YENİ
    'success' => false,
    'message' => ''
];

if (!$conn) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

try {
    // 1. Weather
    $stmt = $conn->query("SELECT weather_condition_id as id, weather_condition as label FROM WeatherCondition");
    $response['weatherOptions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. RoadType
    $stmt = $conn->query("SELECT road_type_id as id, road_type as label FROM RoadType");
    $response['roadConditionOptions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Visibility (YENİ)
    $stmt = $conn->query("SELECT visibility_id as id, visibility as label FROM Visibility");
    $response['visibilityOptions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. TrafficCondition (YENİ)
    $stmt = $conn->query("SELECT traffic_condition_id as id, traffic_condition as label FROM TrafficCondition");
    $response['trafficOptions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['success'] = true;

} catch (PDOException $e) {
    $response['message'] = "DB Error: " . $e->getMessage();
}

echo json_encode($response);
?>