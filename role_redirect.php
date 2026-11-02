
<?php

function redirectByRole($role)
{
    switch ($role) {

        case "admin":
            header("Location: admin_dashboard.php");
            exit;

        case "student":
            header("Location: student_dashboard.php");
            exit;

        case "user":
            header("Location: user_dashboard.php");
            exit;

        default:
            header("Location: login.php?error=" .
                   urlencode("Invalid user role."));
            exit;
    }
}

?>
