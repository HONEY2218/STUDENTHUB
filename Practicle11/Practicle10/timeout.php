<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php?error=Please login first.");
    exit;
}

$timeout = 300; // 5 minutes

if (isset($_SESSION["last_activity"]) &&
    (time() - $_SESSION["last_activity"]) > $timeout) {

    $_SESSION = [];
    session_destroy();

    header("Location: login.php?error=Session expired. Please login again.");
    exit;
}

$_SESSION["last_activity"] = time();

echo "Session is active.";
?>