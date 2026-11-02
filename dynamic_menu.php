
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION["role"] ?? "";

$menuItems = [];

if ($role === "admin") {

    $menuItems = [
    "Dashboard" => "admin_dashboard.php",
    "Student Management" => "admin_students.php",
    "Event Management" => "admin_events.php",
    "User Management" => "admin_users.php",
    "Audit Logs" => "admin_audit_logs.php"
];

} elseif ($role === "student") {

    $menuItems = [
        "Dashboard" => "student_dashboard.php",
        "Home" => "home.html",
        "About" => "about.html",
        "Events" => "event.html",
        "FAQ" => "FAQ.html"
    ];

} elseif ($role === "user") {

    $menuItems = [
        "Dashboard" => "user_dashboard.php",
        "Home" => "home.html",
        "About" => "about.html",
        "Events" => "event.html",
        "FAQ" => "FAQ.html"
    ];

}

?>

<nav>
    <?php foreach ($menuItems as $label => $url): ?>

        <a href="<?php echo htmlspecialchars($url); ?>">
            <?php echo htmlspecialchars($label); ?>
        </a>

    <?php endforeach; ?>

    <a href="logout.php">Logout</a>
</nav>
