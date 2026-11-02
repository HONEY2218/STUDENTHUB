
<?php

require_once "session_check.php";
require_once "role_redirect.php";

// Redirect according to logged-in user's role
if (isset($_SESSION["role"])) {
    redirectByRole($_SESSION["role"]);
}

// If role is missing, return to login
header("Location: login.php?error=" .
       urlencode("User role not found. Please login again."));
exit;

?>
