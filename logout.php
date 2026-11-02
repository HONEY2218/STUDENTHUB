
<?php
session_start();

require_once "mysqli_db.php";

// Store logged-in user ID before destroying session
$userId = $_SESSION["user_id"] ?? null;

// Delete Remember Me token using cookie selector
if (!empty($_COOKIE["remember_me"])) {

    $parts = explode(":", $_COOKIE["remember_me"], 2);

    if (
        count($parts) === 2 &&
        preg_match('/^[a-f0-9]{32}$/', $parts[0])
    ) {
        $selector = $parts[0];

        $stmt = $conn->prepare(
            "DELETE FROM remember_tokens WHERE selector = ?"
        );

        $stmt->bind_param("s", $selector);
        $stmt->execute();
        $stmt->close();
    }
}

// Fallback: Delete all tokens belonging to logged-in user
if ($userId !== null) {

    $stmt = $conn->prepare(
        "DELETE FROM remember_tokens WHERE student_id = ?"
    );

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();
}

// Clear Remember Me cookie
setcookie("remember_me", "", [
    "expires" => time() - 3600,
    "path" => "/",
    "httponly" => true,
    "secure" => !empty($_SERVER["HTTPS"]) &&
                $_SERVER["HTTPS"] !== "off",
    "samesite" => "Lax"
]);

// Clear session data
$_SESSION = [];

// Delete session cookie
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

// Redirect to login page
header(
    "Location: login.php?message=" .
    urlencode("You have been logged out successfully.")
);
exit;
?>
