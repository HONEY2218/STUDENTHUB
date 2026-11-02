
<?php

require_once "session_check.php";
require_once "role_redirect.php";

// Only allow Student role
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "student") {

    if (isset($_SESSION["role"])) {
        redirectByRole($_SESSION["role"]);
    }

    header("Location: login.php");
    exit;
}

// Get student details from session
$studentName = $_SESSION["full_name"] ?? "Student";
$username = $_SESSION["username"] ?? "";
$email = $_SESSION["email"] ?? "";

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>StudentHub | Student Dashboard</title>

    <link rel="stylesheet" href="style4.css">
</head>

<body>

    <!-- Header -->
    <header>
        <div class="container">
            <div class="top">
                <div class="logo">

                    <img src="image.jpg" alt="StudentHub Logo">

                    <div>
                        <h1>StudentHub</h1>
                        <h3>Student Management Portal</h3>
                    </div>

                </div>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <div class="nav-section">
        <div class="container">
            <?php require_once "dynamic_menu.php"; ?>
        </div>
    </div>

    <!-- Main Content -->
    <main>

        <div class="card">

            <h2>
                Welcome,
                <?php echo htmlspecialchars($studentName); ?>!
            </h2>

            <p>
                You have successfully logged in as a Student.
            </p>

            <h3>Student Dashboard</h3>

            <div class="student-details">

                <p>
                    <strong>User Role:</strong>
                    <?php echo htmlspecialchars($_SESSION["role"]); ?>
                </p>

                <p>
                    <strong>Full Name:</strong>
                    <?php echo htmlspecialchars($studentName); ?>
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
                You have access to the StudentHub student area.
            </p>

            <div class="buttons">
                <a href="logout.php">
                    <button type="button">Logout</button>
                </a>
            </div>

        </div>

    </main>

    <!-- Footer -->
    <footer>
        <div class="footer-content">

            <h2>StudentHub Portal</h2>

            <p>Empowering Students Through Technology</p>

            <p class="email">
                Email: studenthub@gmail.com
            </p>

            <p class="copyright">
                &copy; 2026 StudentHub. All Rights Reserved.
            </p>

        </div>
    </footer>

</body>
</html>
