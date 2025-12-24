<?php


class Queries {
    const CHECK_USER_EXISTS = "SELECT user_id FROM Users WHERE username = ? OR email = ?";
    const REGISTER_USER = "INSERT INTO Users (username, email, password_hash) VALUES (?, ?, ?)";
    const LOGIN_USER = "SELECT user_id, username, password_hash FROM Users WHERE username = ?";
    const GET_USER_PROFILE = "SELECT email, created_at FROM Users WHERE user_id = ?";

    const INSERT_SESSION = "
        INSERT INTO DrivingSession 
        (user_id, start_date, end_date, mileage, visibility_id, weather_condition_id) 
        VALUES (?, ?, ?, ?, ?, ?)
    ";
    
    const INSERT_OCCURS_ON = "INSERT INTO OccursOn (session_id, road_type_id) VALUES (?, ?)";
    const INSERT_TAKES_PLACE = "INSERT INTO TakesPlace (session_id, traffic_condition_id) VALUES (?, ?)";
    const INSERT_ROUTE_POINT = "INSERT INTO RoutePoints (session_id, latitude, longitude, timestamp) VALUES (?, ?, ?, ?)";
    
    const GET_ALL_TRIPS_DETAILS = "
        SELECT 
            ds.session_id, ds.start_date, ds.end_date, ds.mileage,
            wc.weather_condition, 
            v.visibility,
            GROUP_CONCAT(DISTINCT rt.road_type SEPARATOR ', ') as road_types,
            GROUP_CONCAT(DISTINCT tc.traffic_condition SEPARATOR ', ') as traffic_conditions
        FROM DrivingSession ds
        JOIN WeatherCondition wc ON ds.weather_condition_id = wc.weather_condition_id
        JOIN Visibility v ON ds.visibility_id = v.visibility_id
        LEFT JOIN OccursOn oo ON ds.session_id = oo.session_id
        LEFT JOIN RoadType rt ON oo.road_type_id = rt.road_type_id
        LEFT JOIN TakesPlace tp ON ds.session_id = tp.session_id
        LEFT JOIN TrafficCondition tc ON tp.traffic_condition_id = tc.traffic_condition_id
        WHERE ds.user_id = ? 
        GROUP BY ds.session_id
        ORDER BY ds.start_date ASC
    ";
    
    const DELETE_TRIP = "DELETE FROM DrivingSession WHERE session_id = ? AND user_id = ?";
    const DELETE_ROUTE_POINTS = "DELETE FROM RoutePoints WHERE session_id = ?";
    const DELETE_OCCURS = "DELETE FROM OccursOn WHERE session_id = ?";
    const DELETE_TAKES_PLACE = "DELETE FROM TakesPlace WHERE session_id = ?";

    const GET_ALL_ROAD_TYPES = "SELECT * FROM RoadType";
    const GET_ALL_VISIBILITIES = "SELECT * FROM Visibility";
    const GET_ALL_WEATHER_CONDITIONS = "SELECT * FROM WeatherCondition";
    const GET_ALL_TRAFFIC_CONDITIONS = "SELECT * FROM TrafficCondition";
}
