<div align="center">
  <img src="docs/logo.png" alt="Extracker Logo" width="120">
  <h1>Extracker - Driving Experience Tracker</h1>
</div>

**Extracker** is a comprehensive web application designed to help drivers log their trips, analyze their driving habits, and earn achievements based on their driving history.

## Features

*   **User Authentication**: Secure login and registration system.
*   **Interactive Dashboard**:
    *   Visual analytics using **Chart.js** (Weather, Road Types, Traffic, Visibility).
    *   Key metrics: Average Speed, Fuel Saved, Total Drive Time.
    *   Trip history table with filtering capabilities.
*   **Trip Logging**:
    *   **Manual Entry**: Log past trips with detailed conditions (Weather, Traffic, Road Type).
    *   **Live Tracking**: (Simulation) Interface for real-time trip recording.
*   **Robust Data Validation**: Comprehensive checks on frontend and backend to ensure data integrity (time logic, mandatory fields).
*   **Gamification**: Earn badges like "Night Rider", "Rain Master", and "Marathon Driver" based on your driving stats.
*   **Profile System**: View personal statistics and driving timeline.
*   **Responsive Design**: Built with Bootstrap 5, fully functional on mobile and desktop.

## Technology Stack

*   **Frontend**: HTML5, CSS3, JavaScript (jQuery), Bootstrap 5
*   **Backend**: PHP 8.2+
*   **Database**: MySQL
*   **Libraries**: Chart.js, DataTables, Ionicons
*   **CI/CD**: GitHub Actions (Deploys to AlwaysData via SFTP)

## Project Structure

*   **assets/**: Images and static resources (e.g., logos).
*   **css/**: Custom stylesheets (auth.css, style.css).
*   **js/**: JavaScript files for dashboard logic and tracking (dashboard.js, tracker.js).
*   **html_views/**: HTML templates separating presentation from logic.
*   **php/**: Backend logic following MVC and Service-Repository patterns:
    *   **config/**: DB connection, `bootstrap.php` (Error Handling), and configuration files.
    *   **controllers/**: `AuthController`, `TripController`.
    *   **models/**: Entity classes (`User`, `DrivingSession`, etc.).
    *   **repositories/**: Database access layer (`UserRepository`, `DrivingSessionRepository`).
    *   **services/**: Business logic layer (`AuthService`, `TripService`).
    *   **utils/**: Utility classes.
    *   **router.php**: Central request dispatcher.
*   **db/**: Database documentation and SQL initialization scripts.
*   **index.php**: Main entry point for Authentication (Controller).
*   **dashboard.php**: Main entry point for Dashboard (Controller).

## Installation & Setup

1.  **Clone the Repository**
    ```bash
    git clone https://github.com/Camalzadeh/extracker.git
    cd extracker
    ```

2.  **Database Setup**
    *   Create a MySQL database (e.g., extracker_db).
    *   Import the database/pdm_sql.sql file into your database.

3.  **Configuration**
    *   Create a `php/config/config.inc.php` file based on your environment.
    *   Example content:
        ```php
        <?php
        return [
            'db_host' => 'localhost',
            'db_name' => 'extracker_db',
            'db_user' => 'root',
            'db_pass' => '',
            'environment' => 'development'
        ];
        ?>
        ```

4.  **Run Locally**
    *   Use a local server like XAMPP, WAMP, or PHP's built-in server:
        ```bash
        php -S localhost:8000
        ```
    *   Open http://localhost:8000 in your browser.

## CI/CD Pipeline

This project uses **GitHub Actions** for continuous integration and deployment.

*   **Workflow file**: .github/workflows/ci-cd.yml
*   **Process**:
    1.  **Syntax Check**: Checks all PHP files for syntax errors.
    2.  **Deploy**: If syntax checks pass, files are deployed to **AlwaysData** hosting via SFTP.
    3.  **Secrets**: Sensitive data (DB Config, FTP credentials) are managed via GitHub Secrets.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.