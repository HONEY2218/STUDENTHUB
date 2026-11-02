
<?php

require_once "session_check.php";
require_once "mysqli_db.php";
require_once "role_redirect.php";

// Only Admin can access this page
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    if (isset($_SESSION["role"])) {
        redirectByRole($_SESSION["role"]);
    }
    header("Location: login.php");
    exit;
}

// Generate CSRF token
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$message = "";
$messageType = "";

// Handle all POST requests
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $token = $_POST["csrf_token"] ?? "";

    if (!hash_equals($_SESSION["csrf_token"], $token)) {

        $message = "Invalid CSRF token.";
        $messageType = "error";

    } else {

        $formAction = $_POST["form_action"] ?? "";

        // =====================================
        // ACTIVATE / DEACTIVATE USER ACCOUNT
        // =====================================
        if ($formAction === "toggle_status") {

            $userId = filter_var(
                $_POST["user_id"] ?? "",
                FILTER_VALIDATE_INT
            );

            if (!$userId || $userId <= 0) {

                $message = "Invalid User ID.";
                $messageType = "error";

            } else {

                $checkUser = $conn->prepare(
                    "SELECT student_id, full_name, email, username, status
                     FROM students
                     WHERE student_id = ? AND role = 'user'
                     LIMIT 1"
                );

                $checkUser->bind_param("i", $userId);
                $checkUser->execute();

                $userResult = $checkUser->get_result();
                $targetUser = $userResult->fetch_assoc();

                $checkUser->close();

                if (!$targetUser) {

                    $message = "User account not found.";
                    $messageType = "error";

                } else {

                    $newStatus = (
                        $targetUser["status"] === "active"
                    ) ? "inactive" : "active";

                    $stmt = $conn->prepare(
                        "UPDATE students
                         SET status = ?
                         WHERE student_id = ? AND role = 'user'"
                    );

                    $stmt->bind_param(
                        "si",
                        $newStatus,
                        $userId
                    );

                    if ($stmt->execute() && $stmt->affected_rows === 1) {

                        // Delete Remember Me tokens when deactivating
                        if ($newStatus === "inactive") {

                            $deleteTokens = $conn->prepare(
                                "DELETE FROM remember_tokens
                                 WHERE student_id = ?"
                            );

                            $deleteTokens->bind_param("i", $userId);
                            $deleteTokens->execute();
                            $deleteTokens->close();
                        }

                        // Save audit log
                        $action = (
                            $newStatus === "active"
                        ) ? "USER_ACTIVATED" : "USER_DEACTIVATED";

                        $auditStatus = "SUCCESS";
                        $ip = $_SERVER["REMOTE_ADDR"] ?? "UNKNOWN";

                        $audit = $conn->prepare(
                            "INSERT INTO audit_logs
                            (email, username, action, status, ip_address)
                            VALUES (?, ?, ?, ?, ?)"
                        );

                        $audit->bind_param(
                            "sssss",
                            $targetUser["email"],
                            $targetUser["username"],
                            $action,
                            $auditStatus,
                            $ip
                        );

                        $audit->execute();
                        $audit->close();

                        $message = "User account " .
                                   $newStatus .
                                   " successfully!";

                        $messageType = "success";

                    } else {

                        $message = "Unable to update User status.";
                        $messageType = "error";
                    }

                    $stmt->close();
                }
            }

        // =====================================
        // CREATE NEW USER ACCOUNT
        // =====================================
        } elseif ($formAction === "create_user") {

            $full_name = trim($_POST["full_name"] ?? "");
            $enrollment = trim($_POST["enrollment"] ?? "");
            $roll_number = trim($_POST["roll_number"] ?? "");
            $email = trim($_POST["email"] ?? "");
            $username = trim($_POST["username"] ?? "");
            $mobile = trim($_POST["mobile"] ?? "");
            $course = trim($_POST["course"] ?? "");
            $academic_year = trim($_POST["academic_year"] ?? "");
            $gender = trim($_POST["gender"] ?? "");
            $password = $_POST["password"] ?? "";
            $confirm_password = $_POST["confirm_password"] ?? "";

            if (
                $full_name === "" || $enrollment === "" ||
                $roll_number === "" || $email === "" ||
                $username === "" || $mobile === "" ||
                $course === "" || $academic_year === "" ||
                $gender === "" || $password === "" ||
                $confirm_password === ""
            ) {

                $message = "All fields are required.";
                $messageType = "error";

            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

                $message = "Invalid email address.";
                $messageType = "error";

            } elseif (!preg_match("/^[0-9]{10}$/", $mobile)) {

                $message = "Mobile number must contain 10 digits.";
                $messageType = "error";

            } elseif ($password !== $confirm_password) {

                $message = "Passwords do not match.";
                $messageType = "error";

            } elseif (strlen($password) < 8) {

                $message = "Password must contain at least 8 characters.";
                $messageType = "error";

            } else {

                // Check duplicate records
                $check = $conn->prepare(
                    "SELECT student_id FROM students
                     WHERE email = ? OR username = ? OR enrollment = ?
                     LIMIT 1"
                );

                $check->bind_param(
                    "sss",
                    $email,
                    $username,
                    $enrollment
                );

                $check->execute();
                $result = $check->get_result();

                if ($result->num_rows > 0) {
                    $message = "Email, username or enrollment already exists.";
                    $messageType = "error";
                }

                $check->close();

                // Insert new User
                if ($message === "") {

                    $password_hash = password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );

                    $role = "user";
                    $status = "active";

                    $stmt = $conn->prepare(
                        "INSERT INTO students
                        (full_name, enrollment, roll_number, email,
                         username, mobile, course, academic_year,
                         gender, password_hash, role, status)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                    );

                    $stmt->bind_param(
                        "ssssssssssss",
                        $full_name,
                        $enrollment,
                        $roll_number,
                        $email,
                        $username,
                        $mobile,
                        $course,
                        $academic_year,
                        $gender,
                        $password_hash,
                        $role,
                        $status
                    );

                    if ($stmt->execute()) {

                        $message = "User account created successfully!";
                        $messageType = "success";

                        // Save audit log
                        $action = "USER_CREATED";
                        $auditStatus = "SUCCESS";
                        $ip = $_SERVER["REMOTE_ADDR"] ?? "UNKNOWN";

                        $audit = $conn->prepare(
                            "INSERT INTO audit_logs
                            (email, username, action, status, ip_address)
                            VALUES (?, ?, ?, ?, ?)"
                        );

                        $audit->bind_param(
                            "sssss",
                            $email,
                            $username,
                            $action,
                            $auditStatus,
                            $ip
                        );

                        $audit->execute();
                        $audit->close();

                    } else {

                        $message = "Unable to create User account.";
                        $messageType = "error";
                    }

                    $stmt->close();
                }
            }

        } else {

            $message = "Invalid form action.";
            $messageType = "error";
        }
    }

    // Generate fresh CSRF token
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// Fetch User accounts
$users = $conn->query(
    "SELECT student_id, full_name, email, username, status
     FROM students
     WHERE role = 'user'
     ORDER BY student_id DESC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub | User Management</title>
    <link rel="stylesheet" href="style4.css">
</head>

<body>

<header>
    <div class="container">
        <h1>StudentHub</h1>
        <h3>Admin User Management</h3>
    </div>
</header>

<div class="nav-section">
    <div class="container">
        <nav>
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="admin_users.php">User Management</a>
            <a href="logout.php">Logout</a>
        </nav>
    </div>
</div>

<main>

<div class="card">

    <h2>Create New User Account</h2>

    <?php if ($message !== ""): ?>
        <div class="<?php echo $messageType === 'success' ? 'success-box' : 'error-box'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">

        <input type="hidden" name="csrf_token"
               value="<?php echo htmlspecialchars($_SESSION["csrf_token"]); ?>">

        <input type="hidden" name="form_action" value="create_user">

        <label>Full Name</label>
        <input type="text" name="full_name" required>

        <label>Enrollment Number</label>
        <input type="text" name="enrollment" required>

        <label>Roll Number</label>
        <input type="text" name="roll_number" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Username</label>
        <input type="text" name="username" required>

        <label>Mobile</label>
        <input type="tel" name="mobile"
               pattern="[0-9]{10}" maxlength="10" required>

        <label>Course</label>
        <select name="course" required>
            <option value="">Select Course</option>
            <option value="B.Tech CSE">B.Tech CSE</option>
            <option value="B.Tech IT">B.Tech IT</option>
            <option value="BCA">BCA</option>
            <option value="MCA">MCA</option>
        </select>

        <label>Academic Year</label>
        <select name="academic_year" required>
            <option value="">Select Year</option>
            <option value="1st Year">1st Year</option>
            <option value="2nd Year">2nd Year</option>
            <option value="3rd Year">3rd Year</option>
            <option value="4th Year">4th Year</option>
        </select>

        <label>Gender</label>
        <select name="gender" required>
            <option value="">Select Gender</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
        </select>

        <label>Password</label>
        <input type="password" name="password" minlength="8" required>

        <label>Confirm Password</label>
        <input type="password" name="confirm_password" minlength="8" required>

        <p><strong>Account Role:</strong> User (Automatically Assigned)</p>

        <button type="submit">Create User Account</button>

    </form>

</div>

<div class="card">

    <h2>Existing User Accounts</h2>

    <table border="1" cellpadding="10" cellspacing="0">

        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Username</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        <?php while ($user = $users->fetch_assoc()): ?>

            <tr>
                <td><?php echo (int)$user["student_id"]; ?></td>

                <td><?php echo htmlspecialchars($user["full_name"]); ?></td>

                <td><?php echo htmlspecialchars($user["email"]); ?></td>

                <td><?php echo htmlspecialchars($user["username"]); ?></td>

                <td>
                    <?php echo htmlspecialchars($user["status"]); ?>
                </td>

                <td>

                    <form method="POST" action="">

                        <input type="hidden" name="csrf_token"
                               value="<?php echo htmlspecialchars($_SESSION["csrf_token"]); ?>">

                        <input type="hidden" name="form_action"
                               value="toggle_status">

                        <input type="hidden" name="user_id"
                               value="<?php echo (int)$user["student_id"]; ?>">

                        <?php if ($user["status"] === "active"): ?>

                            <button type="submit"
                                    onclick="return confirm('Are you sure you want to deactivate this account?');">
                                Deactivate
                            </button>

                        <?php else: ?>

                            <button type="submit"
                                    onclick="return confirm('Are you sure you want to activate this account?');">
                                Activate
                            </button>

                        <?php endif; ?>

                    </form>

                </td>
            </tr>

        <?php endwhile; ?>

    </table>

</div>

</main>

<footer>
    <div class="footer-content">
        <h2>StudentHub Portal</h2>
        <p>Empowering Students Through Technology</p>
        <p class="copyright">&copy; 2026 StudentHub. All Rights Reserved.</p>
    </div>
</footer>

</body>
</html>
