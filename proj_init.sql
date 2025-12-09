CREATE DATABASE hwp_project;
USE hwp_project;

CREATE TABLE experiences (
                             id INT AUTO_INCREMENT PRIMARY KEY,
                             drive_date DATE,
                             drive_time TIME,
                             km INT,
                             weather VARCHAR(50)
);