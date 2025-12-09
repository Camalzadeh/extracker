-- 1. Full session info with weather, visibility, traffic conditions, and road types
SELECT 
  ds.session_id,
  ds.start_date,
  ds.end_date,
  ds.mileage,
  wc.weather_condition AS weather,
  v.visibility AS visibility,
  GROUP_CONCAT(DISTINCT tc.traffic_condition SEPARATOR ', ') AS traffic_conditions,
  GROUP_CONCAT(DISTINCT rt.road_type SEPARATOR ', ') AS road_types
FROM DrivingSession ds
JOIN WeatherCondition wc ON ds.weather_condition_id = wc.weather_condition_id
JOIN Visibility v ON ds.visibility_id = v.visibility_id
LEFT JOIN TakesPlace tp ON ds.session_id = tp.session_id
LEFT JOIN TrafficCondition tc ON tp.traffic_condition_id = tc.traffic_condition_id
LEFT JOIN OccursOn oo ON ds.session_id = oo.session_id
LEFT JOIN RoadType rt ON oo.road_type_id = rt.road_type_id
GROUP BY ds.session_id, ds.start_date, ds.end_date, ds.mileage, wc.weather_condition, v.visibility;

-- 2. Average mileage per weather condition
SELECT 
  wc.weather_condition AS weather,
  ROUND(AVG(ds.mileage), 2) AS average_mileage
FROM DrivingSession ds
JOIN WeatherCondition wc ON ds.weather_condition_id = wc.weather_condition_id
GROUP BY wc.weather_condition;

-- 3. Count of sessions by visibility level
SELECT 
  v.visibility AS visibility_level,
  COUNT(*) AS session_count
FROM DrivingSession ds
JOIN Visibility v ON ds.visibility_id = v.visibility_id
GROUP BY v.visibility;

-- 4. Road types used in each session
SELECT 
  ds.session_id,
  GROUP_CONCAT(DISTINCT rt.road_type SEPARATOR ', ') AS road_types
FROM DrivingSession ds
LEFT JOIN OccursOn oo ON ds.session_id = oo.session_id
LEFT JOIN RoadType rt ON oo.road_type_id = rt.road_type_id
GROUP BY ds.session_id;

-- 5. Sessions with more than one traffic condition
SELECT 
  ds.session_id,
  COUNT(DISTINCT tp.traffic_condition_id) AS traffic_condition_count
FROM DrivingSession ds
LEFT JOIN TakesPlace tp ON ds.session_id = tp.session_id
GROUP BY ds.session_id
HAVING COUNT(DISTINCT tp.traffic_condition_id) > 1;

-- 6. Sessions where traffic condition was 'Heavy'
SELECT 
  ds.session_id,
  ds.start_date,
  ds.end_date,
  ds.mileage
FROM DrivingSession ds
JOIN TakesPlace tp ON ds.session_id = tp.session_id
JOIN TrafficCondition tc ON tp.traffic_condition_id = tc.traffic_condition_id
WHERE tc.traffic_condition = 'Heavy';
