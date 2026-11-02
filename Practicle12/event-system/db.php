<?php
// Database connection (mysqli, procedural style)
$host = "localhost";
$user = "root";
$pass = "";
$db   = "event_db";

mysqli_report(MYSQLI_REPORT_OFF);          // we check errors ourselves
$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");
