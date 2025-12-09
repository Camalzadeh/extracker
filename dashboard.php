<?php
// dashboard.php
session_start(); // [cite: 7]
include 'DB.inc.php'; // [cite: 11]
include 'class.inc.php';

// "Hello Eric" mesajı [cite: 6]
$user = isset($_SESSION['user']) ? $_SESSION['user'] : 'Qonaq';

// Obyekt yaradılır (PDO instance istifadə edərək) [cite: 12]
$app = new DrivingExperience($conn);
$message = "";

// Form göndərildikdə (Submit) [cite: 18]
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date = $_POST['date'];
    $km = $_POST['km'];
    $time = $_POST['time'];
    $weather = $_POST['weather'];

    $message = $app->saveData($date, $km, $time, $weather);
}

// Məlumatları cədvəl üçün çəkirik
$dataList = $app->getAllData();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <style>
        table, th, td { border: 1px solid black; border-collapse: collapse; padding: 5px; }
    </style>
</head>
<body>
<h1>Hello <?php echo htmlspecialchars($user); ?></h1> <?php if($message): ?>
    <p style="color: green;"><?php echo $message; ?></p>
<?php endif; ?>

<h3>New Entry</h3> <form method="post" action="">
    <label>Date:</label><br>
    <input type="date" name="date" required> <br>

    <label>Time:</label><br>
    <input type="time" name="time"> <br>

    <label>Km:</label><br>
    <input type="number" name="km" placeholder="km" required>
    <br>

    <label>Weather:</label><br> <select name="weather">
        <option value="Sunny">Günəşli</option>
        <option value="Rainy">Yağışlı</option>
        <option value="Cloudy">Buludlu</option>
    </select>
    <br><br>

    <input type="submit" value="Submit"> </form>

<hr>

<h3>Statistics</h3> <table style="width: 50%;">
    <tr>
        <th>Date</th>
        <th>Km</th>
        <th>Weather</th>
    </tr>
    <?php foreach ($dataList as $row): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['drive_date']); ?></td>
            <td><?php echo htmlspecialchars($row['km']); ?></td>
            <td><?php echo htmlspecialchars($row['weather']); ?></td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>