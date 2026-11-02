
<?php

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check whether user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php?error=" .
           urlencode("Please login first."));
    exit;
}

// Session timeout: 5 minutes
$timeout_duration = 300;

if (
    isset($_SESSION["last_activity"]) &&
    (time() - $_SESSION["last_activity"]) > $timeout_duration
) {

// Delete Remember Me tokens from database
require_once "mysqli_db.php";

$userId = $_SESSION["user_id"];

$deleteTokens = $conn->prepare(
    "DELETE FROM remember_tokens WHERE student_id = ?"
);

$deleteTokens->bind_param("i", $userId);
$deleteTokens->execute();
$deleteTokens->close();

    // Clear session data
    $_SESSION = [];

    // Delete Remember Me cookie
    setcookie("remember_me", "", [
        "expires" => time() - 3600,
        "path" => "/",
        "httponly" => true,
        "secure" => !empty($_SERVER["HTTPS"]) &&
                    $_SERVER["HTTPS"] !== "off",
        "samesite" => "Lax"
    ]);

    // Delete PHP session cookie
    if (ini_get("session.use_cookies")) {

        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            "",
            [
                "expires" => time() - 42000,
                "path" => $params["path"],
                "domain" => $params["domain"],
                "secure" => $params["secure"],
                "httponly" => $params["httponly"],
                "samesite" => "Lax"
            ]
        );
    }

    // Destroy session
    session_destroy();

    header("Location: login.php?error=" .
           urlencode("Session expired. Please login again."));
    exit;
}

// Update last activity time
$_SESSION["last_activity"] = time();

?>
