
<?php

require_once "session_check.php";

// Only allow User role
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "user") {
    if (isset($_SESSION["role"])) {
        require_once "role_redirect.php";
        redirectByRole($_SESSION["role"]);
    }

    header("Location: login.php");
    exit;
}

$userName = $_SESSION["full_name"] ?? "User";
$username = $_SESSION["username"] ?? "";
$email = $_SESSION["email"] ?? "";

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>StudentHub | User Dashboard</title>

    <link rel="stylesheet" href="style4.css">
</head>

<body>

    <header>
        <div class="container">
            <div class="top">
                <div class="logo">

                    <img src="image.jpg" alt="StudentHub Logo">

                    <div>
                        <h1>StudentHub</h1>
                        <h3>User Portal</h3>
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

        <div class="card">

            <h2>
                Welcome,
                <?php echo htmlspecialchars($userName); ?>!
            </h2>

            <p>
                You have successfully logged in as a User.
            </p>

            <h3>User Dashboard</h3>

            <div class="student-details">

                <p>
                    <strong>User Role:</strong>
                    <?php echo htmlspecialchars($_SESSION["role"]); ?>
                </p>

                <p>
                    <strong>Full Name:</strong>
                    <?php echo htmlspecialchars($userName); ?>
                </p>

                <p>
                    <strong>Username:</strong>
                    <?php echo htmlspecialchars($username); ?>
                </p>

                <p>
                    <strong>Email:</strong>
                    <?php echo htmlspecialchars($email); ?>
                </p>

            </div>

            <p>
                You have access to the StudentHub User Portal.
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

</body>
</html>
