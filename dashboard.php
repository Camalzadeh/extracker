<?php
session_start();
require_once "php/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$userId = $_SESSION['user_id'];
$username = $_SESSION['username'];
$page = $_GET['page'] ?? 'dashboard';

// Fetch Dropdown Data
$roadTypes = $conn->query("SELECT * FROM RoadType")->fetchAll();
$visibilities = $conn->query("SELECT * FROM Visibility")->fetchAll();
$weathers = $conn->query("SELECT * FROM WeatherCondition")->fetchAll();
$trafficConditions = $conn->query("SELECT * FROM TrafficCondition")->fetchAll();

// Fetch Summary Stats
$statsStmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_trips, 
        SUM(mileage) as total_distance 
    FROM DrivingSession 
    WHERE user_id = ?
");
$statsStmt->execute([$userId]);
$stats = $statsStmt->fetch();

// Fetch Recent Trips
$tripsStmt = $conn->prepare("
    SELECT 
        ds.session_id,
        ds.start_date,
        ds.user_id,
        ds.mileage,
        wc.weather_condition,
        rt.road_type
    FROM DrivingSession ds
    JOIN WeatherCondition wc ON ds.weather_condition_id = wc.weather_condition_id
    LEFT JOIN OccursOn oo ON ds.session_id = oo.session_id
    LEFT JOIN RoadType rt ON oo.road_type_id = rt.road_type_id
    WHERE ds.user_id = ? 
    ORDER BY ds.start_date DESC
");
$tripsStmt->execute([$userId]);
$trips = $tripsStmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Extracker Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <!-- Ionicons for icons -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>
<body>

<div class="app-container">
    <nav class="sidebar">
        <h2>Extracker</h2>
        <ul class="nav-links">
            <li><a href="?page=dashboard" class="<?php echo $page=='dashboard'?'active':''; ?>"><ion-icon name="speedometer-outline"></ion-icon> <span class="nav-text">Dashboard</span></a></li>
            <li><a href="?page=add_trip" class="<?php echo $page=='add_trip'?'active':''; ?>"><ion-icon name="add-circle-outline"></ion-icon> <span class="nav-text">Add Trip</span></a></li>
            <li><a href="?page=profile" class="<?php echo $page=='profile'?'active':''; ?>"><ion-icon name="person-outline"></ion-icon> <span class="nav-text">Profile</span></a></li>
            <li><a href="php/logout.php" style="color: #ef4444;"><ion-icon name="log-out-outline"></ion-icon> <span class="nav-text">Logout</span></a></li>
        </ul>
    </nav>

    <main class="main-content">
        
        <!-- DASHBOARD VIEW -->
        <?php if ($page === 'dashboard'): ?>
        <div class="header">
            <h1>Welcome, <?php echo htmlspecialchars($username); ?></h1>
        </div>

        <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <div class="card">
                <h3>Total Trips</h3>
                <p style="font-size: 2rem; font-weight: bold; color: var(--primary-color);"><?php echo $stats['total_trips']; ?></p>
            </div>
            <div class="card">
                <h3>Total Distance</h3>
                <p style="font-size: 2rem; font-weight: bold; color: var(--accent-color);"><?php echo number_format($stats['total_distance'], 1); ?> <span style="font-size: 1rem;">km</span></p>
            </div>
        </div>

        <div class="card">
            <h3>Recent Activity</h3>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Distance</th>
                        <th>Weather</th>
                        <th>Road</th>
                        <th>Started</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($trips as $trip): ?>
                    <tr>
                        <td><?php echo date('M d, Y', strtotime($trip['start_date'])); ?></td>
                        <td><?php echo $trip['mileage']; ?> km</td>
                        <td><?php echo $trip['weather_condition']; ?></td>
                        <td><?php echo $trip['road_type'] ?? 'Unknown'; ?></td>
                        <td><?php echo date('H:i', strtotime($trip['start_date'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- ADD TRIP VIEW -->
        <?php if ($page === 'add_trip'): ?>
        <div class="header">
            <h1>New Driving Session</h1>
        </div>

        <div class="card">
            <!-- Tabs -->
            <div style="display: flex; gap: 1rem; margin-bottom: 2rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
                <button id="tab-live" class="btn btn-primary active-tab" onclick="switchTab('live')">Live Tracking</button>
                <button id="tab-manual" class="btn" onclick="switchTab('manual')" style="background: transparent; border: 1px solid var(--border-color); color: var(--text-color);">Manual Entry</button>
            </div>

            <!-- LIVE TRACKER SECTION -->
            <div id="section-live" style="display: block;">
                <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
                    <!-- Tracker Controls -->
                    <div style="flex: 1; min-width: 300px; text-align: center; border-right: 1px solid var(--border-color); padding-right: 2rem;">
                        <h3>Live GPS Tracker</h3>
                        <div class="tracker-icon" style="font-size: 4rem; margin: 1rem 0; color: var(--primary-color);">
                            <ion-icon name="location-outline"></ion-icon>
                        </div>
                        <div id="distance-display" style="font-size: 3rem; font-weight: 800; margin-bottom: 1rem;">0.00 km</div>
                        <p id="status-display" style="margin-bottom: 1.5rem; color: var(--text-muted);">Ready to start</p>
                        
                        <button id="start-btn" class="btn btn-primary" style="width: 100%; font-size: 1.2rem;">Start Tracking</button>
                        <button id="stop-btn" class="btn btn-danger" style="display: none; width: 100%; font-size: 1.2rem;">Stop & Save Location</button>
                    </div>

                    <!-- Live Form -->
                    <div style="flex: 2; min-width: 300px;">
                        <form action="php/save_trip.php" method="POST" id="live-form">
                            <input type="hidden" name="trip_type" value="live">
                            <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                            <input type="hidden" id="route-points" name="route_points" value="">
                            <input type="hidden" id="distance-input" name="distance" value="0">
                            <input type="hidden" id="start-time" name="start_time" value="">
                            <input type="hidden" id="end-time" name="end_time" value="">

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div class="form-group">
                                    <label>Road Type</label>
                                    <select name="road_type" class="form-control" required>
                                        <?php foreach($roadTypes as $opt): ?>
                                            <option value="<?php echo $opt['road_type_id']; ?>"><?php echo $opt['road_type']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Traffic Condition</label>
                                    <select name="traffic" class="form-control" required>
                                        <?php foreach($trafficConditions as $opt): ?>
                                            <option value="<?php echo $opt['traffic_condition_id']; ?>"><?php echo $opt['traffic_condition']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Weather</label>
                                    <select name="weather" class="form-control" required>
                                        <?php foreach($weathers as $opt): ?>
                                            <option value="<?php echo $opt['weather_condition_id']; ?>"><?php echo $opt['weather_condition']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Visibility</label>
                                    <select name="visibility" class="form-control" required>
                                        <?php foreach($visibilities as $opt): ?>
                                            <option value="<?php echo $opt['visibility_id']; ?>"><?php echo $opt['visibility']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary" style="width: 100%;">Save Experience</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- MANUAL ENTRY SECTION -->
            <div id="section-manual" style="display: none;">
                <form action="php/save_trip.php" method="POST">
                    <input type="hidden" name="trip_type" value="manual">
                    <input type="hidden" name="user_id" value="<?php echo $userId; ?>">

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label>Start Time</label>
                            <input type="datetime-local" name="start_time" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>End Time</label>
                            <input type="datetime-local" name="end_time" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Distance (km)</label>
                            <input type="number" step="0.1" name="distance" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Road Type</label>
                            <select name="road_type" class="form-control" required>
                                <?php foreach($roadTypes as $opt): ?>
                                    <option value="<?php echo $opt['road_type_id']; ?>"><?php echo $opt['road_type']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Traffic Condition</label>
                            <select name="traffic" class="form-control" required>
                                <?php foreach($trafficConditions as $opt): ?>
                                    <option value="<?php echo $opt['traffic_condition_id']; ?>"><?php echo $opt['traffic_condition']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Weather</label>
                            <select name="weather" class="form-control" required>
                                <?php foreach($weathers as $opt): ?>
                                    <option value="<?php echo $opt['weather_condition_id']; ?>"><?php echo $opt['weather_condition']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Visibility</label>
                            <select name="visibility" class="form-control" required>
                                <?php foreach($visibilities as $opt): ?>
                                    <option value="<?php echo $opt['visibility_id']; ?>"><?php echo $opt['visibility']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Save Manual Entry</button>
                </form>
            </div>

        </div>

        <script src="js/tracker.js"></script>
        <script>
            function switchTab(tab) {
                document.getElementById('section-live').style.display = tab === 'live' ? 'block' : 'none';
                document.getElementById('section-manual').style.display = tab === 'manual' ? 'block' : 'none';
                
                // Update button styles
                const btnLive = document.getElementById('tab-live');
                const btnManual = document.getElementById('tab-manual');
                
                if (tab === 'live') {
                    btnLive.classList.add('btn-primary');
                    btnLive.style.background = ''; // Use CSS default
                    btnLive.style.border = 'none';
                    btnLive.style.color = 'white';
                    
                    btnManual.classList.remove('btn-primary');
                    btnManual.style.background = 'transparent';
                    btnManual.style.border = '1px solid var(--border-color)';
                    btnManual.style.color = 'var(--text-color)';
                } else {
                    btnManual.classList.add('btn-primary');
                    btnManual.style.background = '';
                    btnManual.style.border = 'none';
                    btnManual.style.color = 'white';
                    
                    btnLive.classList.remove('btn-primary');
                    btnLive.style.background = 'transparent';
                    btnLive.style.border = '1px solid var(--border-color)';
                    btnLive.style.color = 'var(--text-color)';
                }
            }
        </script>
        <?php endif; ?>

        <!-- PROFILE VIEW -->
        <?php if ($page === 'profile'): ?>
        <div class="header">
            <h1>My Profile</h1>
        </div>
        <div class="card">
            <h3>Account Details</h3>
            <p><strong>Username:</strong> <?php echo htmlspecialchars($username); ?></p>
            <p><strong>Total Trips:</strong> <?php echo $stats['total_trips']; ?></p>
            <p><strong>Total Mileage:</strong> <?php echo $stats['total_distance']; ?> km</p>
        </div>
        <?php endif; ?>

    </main>
</div>

</body>
</html>
