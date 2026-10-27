<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = $_GET['error'] ?? '';
$message = $_GET['message'] ?? '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - StudentHub</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

    <h2>StudentHub Login</h2>

    <?php if ($error): ?>
        <p class="error">
            <?= htmlspecialchars($error) ?>
        </p>
    <?php endif; ?>

    <?php if ($message): ?>
        <p class="success">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>

    <form action="authenticate.php" method="POST">

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Password:</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>

    </form>

</div>

</body>
</html>