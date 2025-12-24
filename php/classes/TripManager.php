<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/Queries.php';

class TripManager {
    private $conn;

    public function __construct(PDO $pdo) {
        $this->conn = $pdo;
    }

    public function __destruct() {
        $this->conn = null;
    }

    public function getAllTrips(string $userId): array {
        $stmt = $this->conn->prepare(Queries::GET_ALL_TRIPS_DETAILS);
        $stmt->execute([$userId]);
        
        $trips = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $trips[] = $row;
        }
        return $trips;
    }

    public function getUserProfile(string $userId) {
        $stmt = $this->conn->prepare(Queries::GET_USER_PROFILE);
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getRoadTypes(): array {
        return $this->conn->query(Queries::GET_ALL_ROAD_TYPES)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getVisibilities(): array {
        return $this->conn->query(Queries::GET_ALL_VISIBILITIES)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getWeatherConditions(): array {
        return $this->conn->query(Queries::GET_ALL_WEATHER_CONDITIONS)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTrafficConditions(): array {
        return $this->conn->query(Queries::GET_ALL_TRAFFIC_CONDITIONS)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteTrip(string $sessionId, string $userId): bool {
        try {
            $this->conn->beginTransaction();

            $stmt1 = $this->conn->prepare(Queries::DELETE_ROUTE_POINTS);
            $stmt1->execute([$sessionId]);
            
            $stmt2 = $this->conn->prepare(Queries::DELETE_OCCURS);
            $stmt2->execute([$sessionId]);
            
            $stmt3 = $this->conn->prepare(Queries::DELETE_TAKES_PLACE);
            $stmt3->execute([$sessionId]);
            
            $stmt = $this->conn->prepare(Queries::DELETE_TRIP);
            $result = $stmt->execute([$sessionId, $userId]);
            
            $this->conn->commit();
            return $result;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return false;
        }
    }

    public function saveTrip(array $data, string $userId): bool {
        try {
            $this->conn->beginTransaction();

            $startTime = $data['start_time'];
            $endTime = $data['end_time'];
            $distance = $data['distance'] ?? 0;
            $visibilityId = $data['visibility'];
            $weatherId = $data['weather'];
            
            $stmt = $this->conn->prepare(Queries::INSERT_SESSION);
            $stmt->execute([$userId, $startTime, $endTime, $distance, $visibilityId, $weatherId]);
            $sessionId = $this->conn->lastInsertId();

            $roadData = $data['road_type'];
            if (!is_array($roadData)) $roadData = [$roadData];
            
            $stmtOccurs = $this->conn->prepare(Queries::INSERT_OCCURS_ON);
            foreach ($roadData as $rId) {
                $stmtOccurs->execute([$sessionId, $rId]);
            }

            $trafficData = $data['traffic'];
            if (!is_array($trafficData)) $trafficData = [$trafficData];

            $stmtTakes = $this->conn->prepare(Queries::INSERT_TAKES_PLACE);
            foreach ($trafficData as $tId) {
                $stmtTakes->execute([$sessionId, $tId]);
            }

            if (isset($data['route_points']) && !empty($data['route_points']) && $data['route_points'] !== '[]') {
                $routePointsJson = $data['route_points'];
                $routePoints = json_decode($routePointsJson, true);

                if (is_array($routePoints)) {
                    $pointStmt = $this->conn->prepare(Queries::INSERT_ROUTE_POINT);
                    foreach ($routePoints as $point) {
                        $ts = $point['timestamp'] ?? date('Y-m-d H:i:s');
                        $pointStmt->execute([$sessionId, $point['lat'], $point['lng'], $ts]);
                    }
                }
            }

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        }
    }
}
?>
