
<?php
session_start();

require_once "role_redirect.php";
require_once "remember_login.php";
if (isset($_SESSION["user_id"])) {
    redirectByRole($_SESSION["role"] ?? "");
}

$error = $_GET["error"] ?? "";
$message = $_GET["message"] ?? "";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub | Secure Login</title>
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
                    <h3>Student Management Portal</h3>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="nav-section">
    <div class="container">
        <nav>
            <a href="home.html">Home</a>
            <a href="about.html">About</a>
            <a href="register.html">Register</a>
            <a href="login.php">Login</a>
            <a href="FAQ.html">FAQ</a>
        </nav>
    </div>
</div>

<main>

    <div class="card login-card">

        <h2>Secure User Login</h2>

        <p>
            Enter your registered email and password
            to access your StudentHub account.
        </p>

        <?php if ($error != ""): ?>
            <div class="error-box">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($message != ""): ?>
            <div class="success-box">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="authenticate.php" method="POST">

            <label for="email">Email Address</label>
            <input
                type="email"
                id="email"
                name="email"
                placeholder="Enter your registered email"
                required
            >

            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="Enter your password"
                required
            >

            <div class="remember-row">
                <input
                    type="checkbox"
                    id="remember_me"
                    name="remember_me"
                    value="1"
                >
                <label for="remember_me">Remember Me</label>
            </div>

            <div class="buttons">
                <input type="submit" value="Login">
                <input type="reset" value="Clear">
            </div>

        </form>

        <p class="register-link">
            Don't have an account?
            <a href="register.html">Register Here</a>
        </p>

    </div>

    <div class="card contact-card">
        <h2>Contact Us</h2>

        <div class="contact-info">
            <h3>StudentHub Portal</h3>
            <p>Email: studenthub@gmail.com</p>
            <p>Phone: +91 9876543210</p>
            <p>Address: CHARUSAT Campus, Changa, Gujarat 388421</p>
        </div>
    </div>

</main>

<footer>
    <div class="footer-content">
        <h2>StudentHub Portal</h2>
        <p>Empowering Students Through Technology</p>
        <p class="email">Email: studenthub@gmail.com</p>
        <p class="copyright">
            &copy; 2026 StudentHub. All Rights Reserved.
        </p>
    </div>
</footer>

</body>
</html>
