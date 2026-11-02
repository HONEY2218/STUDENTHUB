<?php
session_start();

$timeout = 300; // 5 minutes

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php?error=Please login first.");
    exit;
}

if (isset($_SESSION["last_activity"]) &&
    (time() - $_SESSION["last_activity"]) > $timeout) {

    $_SESSION = [];
    session_destroy();

    header("Location: login.php?error=Session expired. Please login again.");
    exit;
}

$_SESSION["last_activity"] = time();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - StudentHub</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

    <h2>Welcome, <?= htmlspecialchars($_SESSION["username"]) ?>!</h2>

    <p>
        <strong>Email:</strong>
        <?= htmlspecialchars($_SESSION["email"]) ?>
    </p>

    <p>
        <strong>Role:</strong>
        <?= htmlspecialchars($_SESSION["role"]) ?>
    </p>

    <?php if ($_SESSION["role"] === "admin"): ?>

        <p>
            <a href="admin_dashboard.php">
                Go to Admin Dashboard
            </a>
        </p>

    <?php elseif ($_SESSION["role"] === "student"): ?>

        <p>
            <a href="student_dashboard.php">
                Go to Student Dashboard
            </a>
        </p>

    <?php endif; ?>

    <p>
        <a href="logout.php">Logout</a>
    </p>

</div>

</body>
</html>