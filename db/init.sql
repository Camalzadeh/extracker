DROP TABLE IF EXISTS RoutePoints;
DROP TABLE IF EXISTS OccursOn;
DROP TABLE IF EXISTS TakesPlace;
DROP TABLE IF EXISTS RoadType;
DROP TABLE IF EXISTS TrafficCondition;
DROP TABLE IF EXISTS DrivingSession;
DROP TABLE IF EXISTS Visibility;
DROP TABLE IF EXISTS WeatherCondition;
DROP TABLE IF EXISTS Users;

CREATE TABLE Users
(
    user_id       INT          NOT NULL AUTO_INCREMENT,
    username      VARCHAR(50)  NOT NULL,
    email         VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id)
);

CREATE TABLE RoadType
(
    road_type_id INT         NOT NULL,
    road_type    VARCHAR(50) NOT NULL,
    PRIMARY KEY (road_type_id)
);

CREATE TABLE Visibility
(
    visibility_id INT         NOT NULL,
    visibility    VARCHAR(50) NOT NULL,
    PRIMARY KEY (visibility_id)
);

CREATE TABLE WeatherCondition
(
    weather_condition_id INT         NOT NULL,
    weather_condition    VARCHAR(50) NOT NULL,
    PRIMARY KEY (weather_condition_id)
);

CREATE TABLE TrafficCondition
(
    traffic_condition_id INT         NOT NULL,
    traffic_condition    VARCHAR(50) NOT NULL,
    PRIMARY KEY (traffic_condition_id)
);

CREATE TABLE DrivingSession
(
    session_id           INT       NOT NULL AUTO_INCREMENT,
    user_id              INT       NOT NULL,
    start_date           TIMESTAMP NOT NULL,
    end_date             TIMESTAMP NOT NULL,
    mileage              FLOAT,
    visibility_id        INT       NOT NULL,
    weather_condition_id INT       NOT NULL,
    PRIMARY KEY (session_id),

    FOREIGN KEY (user_id) REFERENCES Users (user_id),
    FOREIGN KEY (visibility_id) REFERENCES Visibility (visibility_id),
    FOREIGN KEY (weather_condition_id) REFERENCES WeatherCondition (weather_condition_id)
);


CREATE TABLE OccursOn
(
    session_id   INT NOT NULL,
    road_type_id INT NOT NULL,
    FOREIGN KEY (session_id) REFERENCES DrivingSession (session_id),
    FOREIGN KEY (road_type_id) REFERENCES RoadType (road_type_id)
);

CREATE TABLE TakesPlace
(
    session_id           INT NOT NULL,
    traffic_condition_id INT NOT NULL,
    FOREIGN KEY (session_id) REFERENCES DrivingSession (session_id),
    FOREIGN KEY (traffic_condition_id) REFERENCES TrafficCondition (traffic_condition_id)
);

CREATE TABLE RoutePoints
(
    point_id   INT NOT NULL AUTO_INCREMENT,
    session_id INT NOT NULL,
    latitude   DOUBLE NOT NULL,
    longitude  DOUBLE NOT NULL,
    timestamp  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (point_id),
    FOREIGN KEY (session_id) REFERENCES DrivingSession (session_id)
);

INSERT INTO Users (username, email, password_hash)
VALUES ('testuser', 'test@extracker.com', '$2y$10$wE9N6gXbL8jTz0ZzZlG1Q.W.yP1zN7E6F2jC4B5P2C0F2D1A1');

INSERT INTO Visibility
VALUES (1, 'Clear');
INSERT INTO Visibility
VALUES (2, 'Low');

INSERT INTO WeatherCondition
VALUES (1, 'Sunny');
INSERT INTO WeatherCondition
VALUES (2, 'Rainy');

INSERT INTO TrafficCondition
VALUES (1, 'Light');
INSERT INTO TrafficCondition
VALUES (2, 'Heavy');

INSERT INTO RoadType
VALUES (1, 'Urban');
INSERT INTO RoadType
VALUES (2, 'Highway');

INSERT INTO DrivingSession
VALUES (1, 1,'2025-05-17 08:00:00', '2025-05-17 08:30:00', 15.2, 1, 1);

INSERT INTO TakesPlace
VALUES (1, 1);
INSERT INTO TakesPlace
VALUES (1, 2);

INSERT INTO OccursOn
VALUES (1, 1);
INSERT INTO OccursOn
VALUES (1, 2);

INSERT INTO Visibility
VALUES (3, 'Moderate');
INSERT INTO WeatherCondition
VALUES (3, 'Foggy');
INSERT INTO TrafficCondition
VALUES (3, 'Moderate');
INSERT INTO RoadType
VALUES (3, 'Rural');


INSERT INTO DrivingSession
VALUES (2,1, '2025-05-18 14:10:00', '2025-05-18 15:05:00', 18.3, 3, 3);

INSERT INTO TakesPlace
VALUES (2, 3);
INSERT INTO TakesPlace
VALUES (2, 2);

INSERT INTO OccursOn
VALUES (2, 3);
INSERT INTO OccursOn
VALUES (2, 2);
