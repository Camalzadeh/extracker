db.DrivingSession.aggregate([
  {
    $lookup: {
      from: "WeatherCondition",
      localField: "weather_condition_id",
      foreignField: "weather_condition_id",
      as: "weather"
    }
  },
  { $unwind: "$weather" },
  {
    $lookup: {
      from: "VisibilityLevel",
      localField: "visibility_id",
      foreignField: "visibility_id",
      as: "visibility"
    }
  },
  { $unwind: "$visibility" },
  {
    $lookup: {
      from: "RoadType",
      let: { road_ids: "$road_type_ids" },
      pipeline: [
        { $match: { $expr: { $in: ["$road_type_id", "$$road_ids"] } } }
      ],
      as: "road_types"
    }
  },
  {
    $lookup: {
      from: "TrafficCondition",
      let: { traffic_ids: "$traffic_condition_ids" },
      pipeline: [
        { $match: { $expr: { $in: ["$traffic_condition_id", "$$traffic_ids"] } } }
      ],
      as: "traffic_conditions"
    }
  },
  {
    $project: {
      _id: 0,
      session_id: 1,
      start_date: 1,
      end_date: 1,
      mileage: 1,
      weather: "$weather.weather_condition",
      visibility: "$visibility.visibility",
      road_types: "$road_types.road_type",
      traffic_conditions: "$traffic_conditions.traffic_condition"
    }
  }
]);

