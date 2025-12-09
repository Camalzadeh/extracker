db = {
  DrivingSession: [
    {
      "session_id": 1,
      "start_date": "2025-05-17T08:00:00",
      "end_date": "2025-05-17T08:30:00",
      "mileage": 15.2,
      "visibility_id": 1,
      "weather_condition_id": 1,
      "traffic_condition_ids": [1, 2],
      "road_type_ids": [1, 2]
    },
    {
      "session_id": 2,
      "start_date": "2025-05-18T14:10:00",
      "end_date": "2025-05-18T15:05:00",
      "mileage": 18.3,
      "visibility_id": 2,
      "weather_condition_id": 2,
      "traffic_condition_ids": [2],
      "road_type_ids": [2, 3]
    }
  ],
  RoadType: [
    { "road_type_id": 1, "road_type": "Urban" },
    { "road_type_id": 2, "road_type": "Highway" },
    { "road_type_id": 3, "road_type": "Rural" },
    { "road_type_id": 4, "road_type": "Gravel" }
  ],
  TrafficCondition: [
    { "traffic_condition_id": 1, "traffic_condition": "Light" },
    { "traffic_condition_id": 2, "traffic_condition": "Moderate" },
    { "traffic_condition_id": 3, "traffic_condition": "Heavy" },
    { "traffic_condition_id": 4, "traffic_condition": "Standstill" }
  ],
  WeatherCondition: [
    { "weather_condition_id": 1, "weather_condition": "Sunny" },
    { "weather_condition_id": 2, "weather_condition": "Rainy" },
    { "weather_condition_id": 3, "weather_condition": "Foggy" },
    { "weather_condition_id": 4, "weather_condition": "Snowy" }
  ],
  VisibilityLevel: [
    { "visibility_id": 1, "visibility": "Clear" },
    { "visibility_id": 2, "visibility": "Moderate" },
    { "visibility_id": 3, "visibility": "Low" },
    { "visibility_id": 4, "visibility": "Blinding" }
  ]
};

