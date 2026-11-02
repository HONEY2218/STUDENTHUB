
<?php
session_start();

require_once "mysqli_db.php";
require_once "role_redirect.php";

// Only allow POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit;
}

// Get login details
$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";
$rememberMe = isset($_POST["remember_me"]) &&
              $_POST["remember_me"] === "1";

// Validate empty fields
if ($email === "" || $password === "") {
    header("Location: login.php?error=" .
           urlencode("Please enter email and password."));
    exit;
}

// Find account by email
$stmt = $conn->prepare(
    "SELECT student_id, full_name, email, username,
            password_hash, role, status
     FROM students
     WHERE email = ?
     LIMIT 1"
);

$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Verify email and password
if (!$user || !password_verify($password, $user["password_hash"])) {
    header("Location: login.php?error=" .
           urlencode("Invalid email or password."));
    exit;
}

// Check account status
if ($user["status"] !== "active") {
    header("Location: login.php?error=" .
           urlencode("Your account is inactive. Please contact administrator."));
    exit;
}

// Regenerate session ID
session_regenerate_id(true);

// Store authenticated user information
$_SESSION["user_id"] = $user["student_id"];
$_SESSION["full_name"] = $user["full_name"];
$_SESSION["email"] = $user["email"];
$_SESSION["username"] = $user["username"];
$_SESSION["role"] = $user["role"];
$_SESSION["last_activity"] = time();

// Update last login
$update = $conn->prepare(
    "UPDATE students
     SET last_login = NOW()
     WHERE student_id = ?"
);

$update->bind_param("i", $user["student_id"]);
$update->execute();
$update->close();

// Remember Me functionality
if ($rememberMe) {

    $selector = bin2hex(random_bytes(16));
    $token = bin2hex(random_bytes(32));

    $tokenHash = hash("sha256", $token);

    $expires = time() + (30 * 24 * 60 * 60);
    $expiresAt = date("Y-m-d H:i:s", $expires);

    $rememberStmt = $conn->prepare(
        "INSERT INTO remember_tokens
         (student_id, selector, token_hash, expires_at)
         VALUES (?, ?, ?, ?)"
    );

    $rememberStmt->bind_param(
        "isss",
        $user["student_id"],
        $selector,
        $tokenHash,
        $expiresAt
    );

    $rememberStmt->execute();
    $rememberStmt->close();

    $cookieValue = $selector . ":" . $token;

    setcookie(
        "remember_me",
        $cookieValue,
        [
            "expires" => $expires,
            "path" => "/",
            "httponly" => true,
            "secure" => !empty($_SERVER["HTTPS"]) &&
                        $_SERVER["HTTPS"] !== "off",
            "samesite" => "Lax"
        ]
    );
}

// Redirect according to database role
redirectByRole($user["role"]);

?>
