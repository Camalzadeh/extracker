<?php
// class.inc.php

class DrivingExperience {
    private $db;

    public function __construct($dbConn) {
        $this->db = $dbConn;
    }

    public function saveData($date, $km, $time, $weather) {
        // Data Validity (Sadə yoxlama) [cite: 33]
        if (empty($date) || empty($km)) {
            return "Tarix və KM vacibdir!";
        }

        // Security: SQL Injection qarşısını almaq üçün Prepare() istifadəsi [cite: 29, 34]
        try {
            $sql = "INSERT INTO experiences (drive_date, km, drive_time, weather) VALUES (:date, :km, :time, :weather)";
            $stmt = $this->db->prepare($sql);

            // Dəyərləri bağlayırıq (Binding)
            $stmt->bindParam(':date', $date);
            $stmt->bindParam(':km', $km);
            $stmt->bindParam(':time', $time);
            $stmt->bindParam(':weather', $weather);

            $stmt->execute();
            return "Məlumat uğurla yadda saxlanıldı!";
        } catch (PDOException $e) {
            return "Xəta: " . $e->getMessage();
        }
    }

    public function getAllData() {
        $sql = "SELECT * FROM experiences ORDER BY drive_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>