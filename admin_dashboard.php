
<?php

require_once "session_check.php";
require_once "role_redirect.php";

// Only allow Admin role
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {

    if (isset($_SESSION["role"])) {
        redirectByRole($_SESSION["role"]);
    }

    header("Location: login.php");
    exit;
}

$adminName = $_SESSION["full_name"] ?? "Admin";

// Database connection
require_once "mysqli_db.php";

// =====================================
// TOTAL STUDENTS
// =====================================
$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM students
     WHERE role = 'student'"
);
$totalStudents = (int)$result->fetch_assoc()["total"];

// =====================================
// TOTAL USERS
// =====================================
$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM students
     WHERE role = 'user'"
);
$totalUsers = (int)$result->fetch_assoc()["total"];

// =====================================
// TOTAL EVENTS
// =====================================
$result = $conn->query(
    "SELECT COUNT(*) AS total FROM events"
);
$totalEvents = (int)$result->fetch_assoc()["total"];

// =====================================
// ACTIVE AND INACTIVE USERS
// =====================================
$result = $conn->query(
    "SELECT
        SUM(status = 'active') AS active_count,
        SUM(status = 'inactive') AS inactive_count
     FROM students
     WHERE role = 'user'"
);

$userStatus = $result->fetch_assoc();

$activeUsers = (int)($userStatus["active_count"] ?? 0);
$inactiveUsers = (int)($userStatus["inactive_count"] ?? 0);

// =====================================
// PREPARE LAST 6 MONTHS
// =====================================
$monthLabels = [];
$monthKeys = [];

for ($i = 5; $i >= 0; $i--) {

    $date = new DateTime("first day of this month");
    $date->modify("-" . $i . " months");

    $key = $date->format("Y-m");

    $monthKeys[] = $key;
    $monthLabels[] = $date->format("M Y");
}

// Initialize monthly values
$studentMonthly = array_fill(0, 6, 0);
$userMonthly = array_fill(0, 6, 0);
$eventMonthly = array_fill(0, 6, 0);

// =====================================
// MONTHLY STUDENT REGISTRATIONS
// =====================================
$result = $conn->query(
    "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month,
            COUNT(*) AS total
     FROM students
     WHERE role = 'student'
     AND created_at >= DATE_FORMAT(
         DATE_SUB(CURDATE(), INTERVAL 5 MONTH),
         '%Y-%m-01'
     )
     GROUP BY DATE_FORMAT(created_at, '%Y-%m')"
);

while ($row = $result->fetch_assoc()) {

    $index = array_search($row["month"], $monthKeys, true);

    if ($index !== false) {
        $studentMonthly[$index] = (int)$row["total"];
    }
}

// =====================================
// MONTHLY USER REGISTRATIONS
// =====================================
$result = $conn->query(
    "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month,
            COUNT(*) AS total
     FROM students
     WHERE role = 'user'
     AND created_at >= DATE_FORMAT(
         DATE_SUB(CURDATE(), INTERVAL 5 MONTH),
         '%Y-%m-01'
     )
     GROUP BY DATE_FORMAT(created_at, '%Y-%m')"
);

while ($row = $result->fetch_assoc()) {

    $index = array_search($row["month"], $monthKeys, true);

    if ($index !== false) {
        $userMonthly[$index] = (int)$row["total"];
    }
}

// =====================================
// MONTHLY EVENTS BY EVENT DATE
// =====================================
$result = $conn->query(
    "SELECT DATE_FORMAT(event_date, '%Y-%m') AS month,
            COUNT(*) AS total
     FROM events
     WHERE event_date >= DATE_FORMAT(
         DATE_SUB(CURDATE(), INTERVAL 5 MONTH),
         '%Y-%m-01'
     )
     AND event_date < DATE_ADD(
         LAST_DAY(CURDATE()),
         INTERVAL 1 DAY
     )
     GROUP BY DATE_FORMAT(event_date, '%Y-%m')"
);

while ($row = $result->fetch_assoc()) {

    $index = array_search($row["month"], $monthKeys, true);

    if ($index !== false) {
        $eventMonthly[$index] = (int)$row["total"];
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>StudentHub | Admin Dashboard</title>

    <link rel="stylesheet" href="style4.css">

    
<style>
.analytics-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 20px;
    margin-top: 25px;
}

.analytics-box {
    background: #203b70;
    color: white;
    padding: 22px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 4px 10px rgba(0,0,0,0.12);
}

.analytics-box h3 {
    font-size: 17px;
}

.analytics-box h2 {
    font-size: 32px;
    margin-top: 10px;
}

#studentChart,
#userChart,
#eventChart {
    max-height: 300px;
    margin: 20px 0 35px;
}
</style>

</head>

<body>

<header>
    <div class="container">
        <div class="top">
            <div class="logo">
                <img src="image.jpg" alt="StudentHub Logo">

                <div>
                    <h1>StudentHub</h1>
                    <h3>Admin Management Portal</h3>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="nav-section">
    <div class="container">
    <?php require_once "dynamic_menu.php"; ?>
    </div>
</div>

<main>


<!-- ANALYTICS DASHBOARD -->

<div class="card">

    <h2>StudentHub Analytics Dashboard</h2>

    <p>Overview of Students, Users and Events</p>

    <div class="analytics-cards">

        <div class="analytics-box">
            <h3>Total Students</h3>
            <h2><?php echo $totalStudents; ?></h2>
        </div>

        <div class="analytics-box">
            <h3>Total Users</h3>
            <h2><?php echo $totalUsers; ?></h2>
        </div>

        <div class="analytics-box">
            <h3>Total Events</h3>
            <h2><?php echo $totalEvents; ?></h2>
        </div>

        <div class="analytics-box">
            <h3>Active Users</h3>
            <h2><?php echo $activeUsers; ?></h2>
        </div>

        <div class="analytics-box">
            <h3>Inactive Users</h3>
            <h2><?php echo $inactiveUsers; ?></h2>
        </div>

    </div>

</div>

<!-- MONTHLY ANALYTICS -->

<div class="card">

    <h2>Monthly Analytics</h2>

    <p>Last 6 Months Performance</p>

    <h3>Student Registrations</h3>

    <canvas id="studentChart"></canvas>

    <h3>User Registrations</h3>

    <canvas id="userChart"></canvas>

    <h3>Events by Event Date</h3>

    <canvas id="eventChart"></canvas>

</div>


    <div class="card">

        <h2>
            Welcome, <?php echo htmlspecialchars($adminName); ?>!
        </h2>

        <p>
            You have successfully logged in as an Administrator.
        </p>

        <h3>Admin Dashboard</h3>

        <p>
            <strong>User Role:</strong> Admin
        </p>

        <p>
            <strong>Username:</strong>
            <?php echo htmlspecialchars($_SESSION["username"]); ?>
        </p>

        <p>
            You have access to the StudentHub administration area.
        </p>

        <div class="buttons">
            <a href="logout.php">
                <button type="button">Logout</button>
            </a>
        </div>

    </div>

</main>

<footer>
    <div class="footer-content">

        <h2>StudentHub Portal</h2>

        <p>Empowering Students Through Technology</p>

        <p class="copyright">
            &copy; 2026 StudentHub. All Rights Reserved.
        </p>

    </div>
</footer>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const monthLabels = <?php echo json_encode($monthLabels); ?>;

function createMonthlyChart(canvasId, label, values) {

    new Chart(document.getElementById(canvasId), {
        type: "bar",

        data: {
            labels: monthLabels,
            datasets: [{
                label: label,
                data: values,
                borderWidth: 1
            }]
        },

        options: {
            responsive: true,
            maintainAspectRatio: true,

            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
}

createMonthlyChart(
    "studentChart",
    "Student Registrations",
    <?php echo json_encode($studentMonthly); ?>
);

createMonthlyChart(
    "userChart",
    "User Registrations",
    <?php echo json_encode($userMonthly); ?>
);

createMonthlyChart(
    "eventChart",
    "Events by Event Date",
    <?php echo json_encode($eventMonthly); ?>
);
</script>


</body>
</html>
