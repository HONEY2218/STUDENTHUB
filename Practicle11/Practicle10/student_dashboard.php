<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php?error=Please login first.");
    exit;
}

if ($_SESSION["role"] !== "student") {
    http_response_code(403);
    die("Unauthorized Access: Students only.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard - StudentHub</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

    <h2>Student Dashboard</h2>

    <p>
        Welcome Student,
        <?= htmlspecialchars($_SESSION["username"]) ?>!
    </p>

    <p>
        You have student access.
    </p>

    <p>
        <a href="dashboard.php">Back to Dashboard</a>
    </p>

    <p>
        <a href="logout.php">Logout</a>
    </p>

</div>

</body>
</html>