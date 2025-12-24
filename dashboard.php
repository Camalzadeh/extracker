<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start();
require_once "php/db.php";
require_once "php/classes/TripManager.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$userId = $_SESSION['user_id'];
$username = $_SESSION['username'];
$page = $_GET['page'] ?? 'dashboard';

$manager = new TripManager($conn);

$trips = $manager->getAllTrips((string)$userId);
$userProfile = $manager->getUserProfile((string)$userId);

$roadTypes = $manager->getRoadTypes();
$visibilities = $manager->getVisibilities();
$weathers = $manager->getWeatherConditions();
$trafficConditions = $manager->getTrafficConditions();

$weatherStats = [];
$roadStats = [];
$dateStats = [];
$visStats = [];
$trafficStats = [];

foreach ($trips as $trip) {
    $w = $trip['weather_condition'];
    $weatherStats[$w] = ($weatherStats[$w] ?? 0) + 1;

    $rList = $trip['road_types'] ? explode(', ', $trip['road_types']) : ['Unknown'];
    foreach ($rList as $r) {
        $roadStats[$r] = ($roadStats[$r] ?? 0) + 1;
    }
    
    $date = date('Y-m-d', strtotime($trip['start_date']));
    if (!isset($dateStats[$date])) $dateStats[$date] = 0;
    $dateStats[$date] += $trip['mileage'];

    $v = $trip['visibility'];
    $visStats[$v] = ($visStats[$v] ?? 0) + 1;

    $tList = $trip['traffic_conditions'] ? explode(', ', $trip['traffic_conditions']) : ['Unknown'];
    foreach ($tList as $t) {
        $trafficStats[$t] = ($trafficStats[$t] ?? 0) + 1;
    }
}

$weatherLabels = json_encode(array_keys($weatherStats));
$weatherData = json_encode(array_values($weatherStats));
$roadLabels = json_encode(array_keys($roadStats));
$roadData = json_encode(array_values($roadStats));
$visLabels = json_encode(array_keys($visStats ?? []));
$visData = json_encode(array_values($visStats ?? []));
$trafficLabels = json_encode(array_keys($trafficStats ?? []));
$trafficData = json_encode(array_values($trafficStats ?? []));

$lineLabels = [];
$lineData = [];
$cumul = 0;
foreach ($dateStats as $date => $miles) {
    $cumul += $miles;
    $lineLabels[] = $date;
    $lineData[] = $cumul;
}
$lineLabelsJson = json_encode($lineLabels);
$lineDataJson = json_encode($lineData);

$totalDistance = array_sum(array_column($trips, 'mileage'));
$totalTrips = count($trips);

$userEmail = $userProfile['email'] ?? 'user@example.com';
$memberSince = $userProfile['created_at'] ? date('M Y', strtotime($userProfile['created_at'])) : 'Jan 2025';

$totalDurationSeconds = 0;
$i = 0;
$count = count($trips);
if ($count > 0) {
    do {
        $t = $trips[$i];
        $start = strtotime($t['start_date']);
        $end = strtotime($t['end_date']);
        $totalDurationSeconds += ($end - $start);
        $i++;
    } while ($i < $count);
}

$totalHours = $totalDurationSeconds > 0 ? $totalDurationSeconds / 3600 : 0;
$avgSpeed = $totalHours > 0 ? $totalDistance / $totalHours : 0;
$fuelSaved = $totalDistance * 0.12; 

$badges = [];
$nightDrives = array_filter($trips, fn($t) => in_array($t['visibility'], ['Low', 'Moderate'])); 
if (count($nightDrives) > 0) {
    $badges[] = ['icon' => 'moon', 'color' => 'bg-dark', 'title' => strtoupper('Night Rider'), 'desc' => 'Experienced in low visibility driving.'];
}
$rainDrives = array_filter($trips, fn($t) => $t['weather_condition'] === 'Rainy');
if (count($rainDrives) > 0) {
    $badges[] = ['icon' => 'thunderstorm', 'color' => 'bg-primary', 'title' => strtoupper('Rain Master'), 'desc' => 'Safely navigated rainy conditions.'];
}
if ($totalDistance > 50) {

    $badges[] = ['icon' => 'ribbon', 'color' => 'bg-warning', 'title' => 'Marathon Driver', 'desc' => 'Logged over 50km of total driving.'];
}
if (empty($badges)) {
    $badges[] = ['icon' => 'school', 'color' => 'bg-info', 'title' => 'Learner Driver', 'desc' => 'Keep driving to earn badges!'];
}

$pageTitle = 'Dashboard';
switch ($page) {
    case 'add_trip':
        $pageTitle = 'Add New Trip';
        break;
    case 'profile':
        $pageTitle = 'My User Profile';
        break;
    case 'dashboard':
    default:
        $pageTitle = 'Dashboard Overview';
        break;
}
?>
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Extracker - <?php echo $pageTitle; ?></title>
    <link rel="icon" type="image/png" href="assets/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<div class="d-flex" id="wrapper">
    <div id="sidebar-wrapper">
        <div class="sidebar-heading text-center py-4 primary-text fs-4 fw-bold text-uppercase border-bottom">
            <img src="assets/logo.png" alt="Logo" class="logo-img">
            <span class="ms-2 brand-text">Extracker</span>
        </div>
        <div class="list-group list-group-flush my-3">
            <a href="?page=dashboard" class="list-group-item list-group-item-action bg-transparent second-text <?php echo $page=='dashboard'?'active':''; ?>">
                <ion-icon name="speedometer-outline" class="me-2"></ion-icon> <span class="link-text">Dashboard</span>
            </a>
            <a href="?page=add_trip" class="list-group-item list-group-item-action bg-transparent second-text <?php echo $page=='add_trip'?'active':''; ?>">
                <ion-icon name="add-circle-outline" class="me-2"></ion-icon> <span class="link-text">Add Trip</span>
            </a>
            <a href="?page=profile" class="list-group-item list-group-item-action bg-transparent second-text <?php echo $page=='profile'?'active':''; ?>">
                <ion-icon name="person-outline" class="me-2"></ion-icon> <span class="link-text">Profile</span>
            </a>
            <a href="php/logout.php" class="list-group-item list-group-item-action bg-transparent text-danger fw-bold mt-auto">
                <ion-icon name="log-out-outline" class="me-2"></ion-icon> <span class="link-text">Logout</span>
            </a>
        </div>
    </div>

    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-white py-4 px-4 border-bottom d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <ion-icon name="menu-outline" id="menu-toggle" class="primary-text fs-3 me-3" style="cursor: pointer;"></ion-icon>
                <h2 class="fs-2 m-0"><?php echo $pageTitle; ?></h2>
            </div>
            
            <div class="d-flex align-items-center">
                 <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                        <ion-icon name="person-circle-outline" class="fs-4 align-middle"></ion-icon> <?php echo htmlspecialchars($username); ?>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                        <li><a class="dropdown-item" href="?page=profile">Profile</a></li>
                        <li><a class="dropdown-item" href="php/logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container-fluid" style="padding: 20px;">
            
            <?php if ($page === 'dashboard'): ?>
            
            <div class="analytics-strip">
                <div class="analytics-item">
                    <div class="icon-box bg-light-blue"><ion-icon name="speedometer"></ion-icon></div>
                    <div>
                        <h4><?php echo number_format($avgSpeed, 1); ?> km/h</h4>
                        <span>Avg Speed</span>
                    </div>
                </div>
                <div class="analytics-item">
                    <div class="icon-box bg-light-green"><ion-icon name="leaf"></ion-icon></div>
                    <div>
                        <h4><?php echo number_format($fuelSaved, 1); ?> L</h4>
                        <span>Est. Fuel Saved</span>
                    </div>
                </div>
                <div class="analytics-item">
                    <div class="icon-box bg-light-orange"><ion-icon name="time"></ion-icon></div>
                    <div>
                        <h4><?php echo number_format($totalHours, 1); ?> h</h4>
                        <span>Total Drive Time</span>
                    </div>
                </div>
            </div>

            <div class="summary-cards">
                <div class="card stat-card">
                    <h3>Total Distance</h3>
                    <p><?php echo number_format($totalDistance, 1); ?> km</p>
                </div>
                <div class="card stat-card">
                    <h3>Total Trips</h3>
                    <p><?php echo $totalTrips; ?></p>
                </div>
                <div class="card stat-card">
                    <h3>Profile</h3>
                    <p><?php echo htmlspecialchars($username); ?></p>
                </div>
            </div>

            <div class="charts-container">
                <div class="chart-wrapper"><canvas id="weatherChart"></canvas></div>
                <div class="chart-wrapper"><canvas id="roadChart"></canvas></div>
                <div class="chart-wrapper"><canvas id="visibilityChart"></canvas></div>
                <div class="chart-wrapper"><canvas id="trafficChart"></canvas></div>
                <div class="chart-wrapper wide"><canvas id="experiencesChart"></canvas></div>
            </div>

            <div class="table-container">
                <h2>Driving History</h2>
                <table id="experiencesTable" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Mileage</th>
                            <th>Visibility</th>
                            <th>Weather</th>
                            <th>Traffic</th>
                            <th>Road Type</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach(array_reverse($trips) as $trip): ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($trip['start_date'])); ?></td>
                            <td><?php echo number_format($trip['mileage'], 1); ?> km</td>
                            <td><?php echo $trip['visibility']; ?></td>
                            <td><?php echo $trip['weather_condition']; ?></td>
                            <td><?php echo $trip['traffic_conditions'] ?? 'None'; ?></td>
                            <td><?php echo $trip['road_types'] ?? 'None'; ?></td>
                            <td>
                                <form action="php/delete_trip.php" method="POST" onsubmit="return confirm('Are you sure?');" style="display:inline;">
                                    <input type="hidden" name="session_id" value="<?php echo $trip['session_id']; ?>">
                                    <input type="hidden" name="redirect_page" value="dashboard">
                                    <button type="submit" class="btn-sm btn-danger border-0"><ion-icon name="trash"></ion-icon></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    initCharts(
                        <?php echo $weatherLabels; ?>, <?php echo $weatherData; ?>,
                        <?php echo $roadLabels; ?>, <?php echo $roadData; ?>,
                        <?php echo $lineLabelsJson; ?>, <?php echo $lineDataJson; ?>,
                        <?php echo $visLabels; ?>, <?php echo $visData; ?>,
                        <?php echo $trafficLabels; ?>, <?php echo $trafficData; ?>
                    );
                });
            </script>
            <?php elseif ($page === 'add_trip'): ?>
            <div class="card">
                <h2>New Driving Session</h2>
                <div class="tabs">
                    <button class="tab-btn active" onclick="showTab('manual')">Manual Entry</button>
                    <button class="tab-btn" onclick="showTab('live')">Live Tracking</button>
                </div>

                <div id="tab-manual" class="tab-content">
                    <form action="php/save_trip.php" method="POST" onsubmit="return validateManualForm()">
                        <input type="hidden" name="trip_type" value="manual">
                        <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                        
                        <div class="form-row">
                            <div class="form-group col-half"><label>Start Time</label><input type="datetime-local" name="start_time" class="form-control" required></div>
                            <div class="form-group col-half"><label>End Time</label><input type="datetime-local" name="end_time" class="form-control" required></div>
                        </div>
                        <div class="form-group"><label>Distance (km)</label><input type="number" step="0.1" name="distance" class="form-control" required></div>
                        
                        <div class="form-row">
                            <div class="form-group col-half">
                                <label>Weather</label>
                                <select name="weather" class="form-control" required><?php foreach($weathers as $w) echo "<option value='{$w['weather_condition_id']}'>{$w['weather_condition']}</option>"; ?></select>
                            </div>
                            <div class="form-group col-half">
                                <label>Visibility</label>
                                <select name="visibility" class="form-control" required><?php foreach($visibilities as $v) echo "<option value='{$v['visibility_id']}'>{$v['visibility']}</option>"; ?></select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-half">
                                <label>Road Type</label>
                                <div class="checkbox-group">
                                    <?php foreach($roadTypes as $r): ?>
                                    <label class="checkbox-label"><input type="checkbox" name="road_type[]" value="<?php echo $r['road_type_id']; ?>"> <?php echo $r['road_type']; ?></label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="form-group col-half">
                                <label>Traffic</label>
                                <div class="checkbox-group">
                                    <?php foreach($trafficConditions as $t): ?>
                                    <label class="checkbox-label"><input type="checkbox" name="traffic[]" value="<?php echo $t['traffic_condition_id']; ?>"> <?php echo $t['traffic_condition']; ?></label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div id="manual-error-msg" class="text-danger text-center fw-bold mb-3" style="display:none;"></div>
                        <button type="submit" class="btn btn-primary full-width">Save Manual Trip</button>
                    </form>
                </div>

                <div id="tab-live" class="tab-content" style="display:none;">
                    <div class="tracker-ui">
                        <div class="tracker-status">
                            <ion-icon name="navigate-circle-outline" style="font-size: 64px; color: #36A2EB;"></ion-icon>
                            <h1 id="distance-display">0.00 km</h1>
                            <p id="status-display">Ready to track</p>
                        </div>
                        <div class="tracker-controls">
                            <button id="start-btn" type="button" class="btn btn-primary">Start Tracking</button>
                            <button id="stop-btn" type="button" class="btn btn-danger" style="display:none;">Stop & Save</button>
                        </div>
                    </div>
                    
                    <form action="php/save_trip.php" method="POST" id="live-form" style="margin-top:20px;">
                        <input type="hidden" name="trip_type" value="live">
                        <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                        <input type="hidden" id="route-points" name="route_points">
                        <input type="hidden" id="distance-input" name="distance">
                        <input type="hidden" id="start-time" name="start_time">
                        <input type="hidden" id="end-time" name="end_time">
                        
                        <div class="form-row">
                            <div class="form-group col-half">
                                <label>Weather</label>
                                <select name="weather" class="form-control" required><?php foreach($weathers as $w) echo "<option value='{$w['weather_condition_id']}'>{$w['weather_condition']}</option>"; ?></select>
                            </div>
                            <div class="form-group col-half">
                                <label>Visibility</label>
                                <select name="visibility" class="form-control" required><?php foreach($visibilities as $v) echo "<option value='{$v['visibility_id']}'>{$v['visibility']}</option>"; ?></select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-half">
                                <label>Road Type (Select all that apply)</label>
                                <div class="checkbox-group">
                                    <?php foreach($roadTypes as $r): ?>
                                    <label class="checkbox-label"><input type="checkbox" name="road_type[]" value="<?php echo $r['road_type_id']; ?>"> <?php echo $r['road_type']; ?></label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="form-group col-half">
                                <label>Traffic (Select all that apply)</label>
                                <div class="checkbox-group">
                                    <?php foreach($trafficConditions as $t): ?>
                                    <label class="checkbox-label"><input type="checkbox" name="traffic[]" value="<?php echo $t['traffic_condition_id']; ?>"> <?php echo $t['traffic_condition']; ?></label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary full-width">Save Live Trip</button>
                    </form>
                </div>
            </div>
            <script src="js/tracker.js"></script>

            <?php elseif ($page === 'profile'): ?>
            <div class="profile-container">
                <div class="profile-header">
                    <div class="profile-cover"></div>
                    <div class="profile-info">
                        <div class="profile-avatar">
                            <ion-icon name="person-circle" style="font-size: 100px; color: #fff; background: #007bff; border-radius: 50%;"></ion-icon>
                        </div>
                        <div class="profile-text">
                            <h2><?php echo htmlspecialchars($username); ?></h2>
                            <p class="text-muted"><?php echo htmlspecialchars($userEmail); ?> | Member since <?php echo $memberSince; ?></p>
                            <span class="badge bg-success">Level <?php echo floor($totalDistance / 10) + 1; ?> Driver</span>
                        </div>
                    </div>
                </div>

                <div class="profile-stats-row">
                    <div class="profile-stat-card">
                        <ion-icon name="speedometer" class="stat-icon text-primary"></ion-icon>
                        <h3><?php echo number_format($totalDistance, 1); ?></h3>
                        <p>Total Km</p>
                    </div>
                    <div class="profile-stat-card">
                        <ion-icon name="car-sport" class="stat-icon text-warning"></ion-icon>
                        <h3><?php echo $totalTrips; ?></h3>
                        <p>Trips Completed</p>
                    </div>
                    <div class="profile-stat-card">
                        <ion-icon name="time" class="stat-icon text-info"></ion-icon>
                        <h3><?php echo number_format($totalHours, 0); ?>h</h3>
                        <p>Drive Time</p>
                    </div>
                    <div class="profile-stat-card">
                        <ion-icon name="trophy" class="stat-icon text-danger"></ion-icon>
                        <h3><?php echo count($badges); ?></h3>
                        <p>Badges Earned</p>
                    </div>
                </div>

                <div class="experience-section">
                    <h3><ion-icon name="star" class="me-2 text-warning"></ion-icon>Earned Badges & Recent Activity</h3>
                    <div class="experience-timeline">
                        <?php foreach($badges as $badge): ?>
                        <div class="experience-item">
                            <div class="exp-icon <?php echo $badge['color']; ?>"><ion-icon name="<?php echo $badge['icon']; ?>"></ion-icon></div>
                            <div class="exp-content">
                                <h4><?php echo $badge['title']; ?></h4>
                                <span class="exp-date">Awarded based on trips</span>
                                <p><?php echo $badge['desc']; ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <?php foreach(array_reverse($trips) as $trip): ?>
                        <div class="experience-item">
                            <div class="exp-icon bg-secondary"><ion-icon name="navigate"></ion-icon></div>
                            <div class="exp-content">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4>Trip on <?php echo date('M d', strtotime($trip['start_date'])); ?></h4>
                                        <span class="exp-date"><?php echo date('h:i A', strtotime($trip['start_date'])); ?> - <?php echo date('h:i A', strtotime($trip['end_date'])); ?></span>
                                    </div>
                                    <form action="php/delete_trip.php" method="POST" onsubmit="return confirm('Delete this trip?');">
                                        <input type="hidden" name="session_id" value="<?php echo $trip['session_id']; ?>">
                                        <input type="hidden" name="redirect_page" value="profile">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><ion-icon name="trash"></ion-icon></button>
                                    </form>
                                </div>
                                <p>
                                    Drove <strong><?php echo number_format($trip['mileage'], 1); ?> km</strong> in 
                                    <?php echo $trip['weather_condition']; ?> weather.
                                    <small class="text-muted">
                                        Visibility: <?php echo $trip['visibility']; ?>
                                        <?php if($trip['road_types']) echo " | Roads: " . $trip['road_types']; ?>
                                        <?php if($trip['traffic_conditions']) echo " | Traffic: " . $trip['traffic_conditions']; ?>
                                    </small>
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?> 
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="js/dashboard.js"></script>
</body>
</html>
