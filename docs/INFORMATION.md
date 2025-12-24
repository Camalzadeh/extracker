# Extracker - Professional Driving Session Tracker

## Application Overview
Extracker is a production-ready PHP application designed to track and analyze driving sessions. It allows users to log manual trips or use real-time GPS tracking to record driving history. The application provides analytical insights into driving habits, environmental conditions, and fuel efficiency.

## Core Features
1.  **User Authentication**: Secure registration and login system using hashed passwords.
2.  **Dashboard**: A responsive command center displaying key metrics (Total Distance, Trips, Time) and graphical charts.
3.  **Live Tracking**: Real-time GPS-based trip recording using JavaScript Geolocation API.
4.  **Manual Entry**: Form-based entry for past trips with detailed environmental data.
5.  **Analytics**: Visual charts representing weather, road, and traffic conditions affecting drives.
6.  **Profile Management**: User profile section with aggregated stats and detailed history management.

## Technical Architecture
*   **Backend**: Native PHP 8.x (No frameworks), adhering to strict separation of concerns.
*   **Database**: MySQL with PDO, utilizing transactions for data integrity.
*   **Architecture**: 
    *   `classes/Queries.php`: Centralized SQL repository.
    *   `classes/TripManager.php`: Business logic encapsulation.
*   **Frontend**: HTML5, CSS3, JavaScript (jQuery, Chart.js), fully responsive Design.
*   **Security**: Prepared statements (SQLi protection), XSS escaping, Session management.

## Purpose
The code demonstrates a robust, "vanilla" implementation of a complex CRUD application, emphasizing core programming concepts (control structures, strict typing, OOP) without relying on external dependencies for logic.
