<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php?error=Please login first.");
    exit;
}

if ($_SESSION["role"] !== "admin") {
    http_response_code(403);
    die("Unauthorized Access: Admins only.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - StudentHub</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

    <h2>Admin Dashboard</h2>

    <p>
        Welcome Admin,
        <?= htmlspecialchars($_SESSION["username"]) ?>!
    </p>

    <p>
        You have administrator access.
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