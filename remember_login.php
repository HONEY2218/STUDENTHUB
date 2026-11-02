
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "mysqli_db.php";
require_once "role_redirect.php";

// If already logged in, do nothing
if (isset($_SESSION["user_id"])) {
    return;
}

// Check Remember Me cookie
if (empty($_COOKIE["remember_me"])) {
    return;
}

// Separate selector and token
$cookieParts = explode(":", $_COOKIE["remember_me"], 2);

if (count($cookieParts) !== 2) {
    return;
}

$selector = $cookieParts[0];
$token = $cookieParts[1];

// Validate cookie format
if (
    !preg_match('/^[a-f0-9]{32}$/', $selector) ||
    !preg_match('/^[a-f0-9]{64}$/', $token)
) {
    return;
}

// Find remember token and account
$stmt = $conn->prepare(
    "SELECT rt.student_id, rt.token_hash, rt.expires_at,
            s.full_name, s.email, s.username,
            s.role, s.status
     FROM remember_tokens rt
     JOIN students s ON rt.student_id = s.student_id
     WHERE rt.selector = ?
     LIMIT 1"
);

$stmt->bind_param("s", $selector);
$stmt->execute();

$result = $stmt->get_result();
$record = $result->fetch_assoc();
$stmt->close();

// Verify token, expiry and account status
if (
    !$record ||
    strtotime($record["expires_at"]) <= time() ||
    !hash_equals(
        $record["token_hash"],
        hash("sha256", $token)
    ) ||
    $record["status"] !== "active"
) {

    // Delete invalid or inactive account token
    $delete = $conn->prepare(
        "DELETE FROM remember_tokens WHERE selector = ?"
    );

    $delete->bind_param("s", $selector);
    $delete->execute();
    $delete->close();

    // Clear Remember Me cookie
    setcookie("remember_me", "", [
        "expires" => time() - 3600,
        "path" => "/",
        "httponly" => true,
        "secure" => !empty($_SERVER["HTTPS"]) &&
                    $_SERVER["HTTPS"] !== "off",
        "samesite" => "Lax"
    ]);

    return;
}

// Regenerate session ID
session_regenerate_id(true);

// Restore authenticated session
$_SESSION["user_id"] = $record["student_id"];
$_SESSION["full_name"] = $record["full_name"];
$_SESSION["email"] = $record["email"];
$_SESSION["username"] = $record["username"];
$_SESSION["role"] = $record["role"];
$_SESSION["last_activity"] = time();

// Generate new Remember Me token
$newSelector = bin2hex(random_bytes(16));
$newToken = bin2hex(random_bytes(32));
$newTokenHash = hash("sha256", $newToken);

$newExpiry = time() + (30 * 24 * 60 * 60);
$newExpiryAt = date("Y-m-d H:i:s", $newExpiry);

// Rotate token inside transaction
$conn->begin_transaction();

try {

    $delete = $conn->prepare(
        "DELETE FROM remember_tokens WHERE selector = ?"
    );

    $delete->bind_param("s", $selector);
    $delete->execute();
    $delete->close();

    $insert = $conn->prepare(
        "INSERT INTO remember_tokens
         (student_id, selector, token_hash, expires_at)
         VALUES (?, ?, ?, ?)"
    );

    $insert->bind_param(
        "isss",
        $record["student_id"],
        $newSelector,
        $newTokenHash,
        $newExpiryAt
    );

    $insert->execute();
    $insert->close();

    $conn->commit();

} catch (Throwable $e) {

    $conn->rollback();

    $_SESSION = [];
    session_destroy();

    http_response_code(500);
    exit("Remember Me authentication failed.");
}

// Set rotated cookie
$newCookieValue = $newSelector . ":" . $newToken;

setcookie("remember_me", $newCookieValue, [
    "expires" => $newExpiry,
    "path" => "/",
    "httponly" => true,
    "secure" => !empty($_SERVER["HTTPS"]) &&
                $_SERVER["HTTPS"] !== "off",
    "samesite" => "Lax"
]);

// Update last login
$update = $conn->prepare(
    "UPDATE students
     SET last_login = NOW()
     WHERE student_id = ?"
);

$update->bind_param("i", $record["student_id"]);
$update->execute();
$update->close();

// Redirect according to role
redirectByRole($record["role"]);

?>
