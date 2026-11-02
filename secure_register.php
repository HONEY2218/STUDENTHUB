
<?php
session_start();
require_once "mysqli_db.php";

// Generate CSRF token
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$message = "";
$messageType = "";
$auditStatus = "FAILED";

function saveAuditLog($conn, $email, $username, $status) {
    $action = "USER_REGISTRATION";
    $ip = $_SERVER["REMOTE_ADDR"] ?? "UNKNOWN";

    $stmt = $conn->prepare(
        "INSERT INTO audit_logs
        (email, username, action, status, ip_address)
        VALUES (?, ?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "sssss",
        $email,
        $username,
        $action,
        $status,
        $ip
    );

    $stmt->execute();
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

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

    // CSRF verification
    $token = $_POST["csrf_token"] ?? "";

    if (
        !hash_equals($_SESSION["csrf_token"], $token)
    ) {
        $message = "Invalid CSRF token. Request rejected.";
        $messageType = "error";
        $auditStatus = "CSRF_FAILED";

    } elseif (
        $full_name == "" || $enrollment == "" ||
        $roll_number == "" || $email == "" ||
        $username == "" || $mobile == "" ||
        $course == "" || $academic_year == "" ||
        $gender == "" || $password == "" ||
        $confirm_password == ""
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
        $message = "Password must be at least 8 characters.";
        $messageType = "error";

    } else {

        // Duplicate email check
        $stmt = $conn->prepare(
            "SELECT student_id FROM students WHERE email = ?"
        );
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = "This email is already registered.";
            $messageType = "error";
        }
        $stmt->close();

        // Duplicate username check
        if ($message == "") {
            $stmt = $conn->prepare(
                "SELECT student_id FROM students WHERE username = ?"
            );
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $message = "This username is already taken.";
                $messageType = "error";
            }
            $stmt->close();
        }

        // Duplicate enrollment check
        if ($message == "") {
            $stmt = $conn->prepare(
                "SELECT student_id FROM students WHERE enrollment = ?"
            );
            $stmt->bind_param("s", $enrollment);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $message = "This enrollment number is already registered.";
                $messageType = "error";
            }
            $stmt->close();
        }

        // Insert student
        if ($message == "") {

            $password_hash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $conn->prepare(
                "INSERT INTO students
                (full_name, enrollment, roll_number, email,
                 username, mobile, course, academic_year,
                 gender, password_hash)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "ssssssssss",
                $full_name,
                $enrollment,
                $roll_number,
                $email,
                $username,
                $mobile,
                $course,
                $academic_year,
                $gender,
                $password_hash
            );

            if ($stmt->execute()) {
                $message = "Registration successful!";
                $messageType = "success";
                $auditStatus = "SUCCESS";
            } else {
                $message = "Registration failed.";
                $messageType = "error";
            }

            $stmt->close();
        }
    }

    // Save registration activity in audit log
    saveAuditLog($conn, $email, $username, $auditStatus);

    // Generate a fresh token for the next form submission
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure User Registration</title>
</head>
<body>

<h2>Secure User Registration</h2>

<?php if ($message != ""): ?>
    <p style="color: <?php echo $messageType == 'success' ? 'green' : 'red'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </p>
<?php endif; ?>

<form method="POST" action="">

    <input type="hidden" name="csrf_token"
           value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

    <label>Full Name:</label><br>
    <input type="text" name="full_name" required><br><br>

    <label>Enrollment Number:</label><br>
    <input type="text" name="enrollment" required><br><br>

    <label>Roll Number:</label><br>
    <input type="text" name="roll_number" required><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Username:</label><br>
    <input type="text" name="username" required><br><br>

    <label>Mobile:</label><br>
    <input type="tel" name="mobile"
           pattern="[0-9]{10}" maxlength="10" required><br><br>

    <label>Course:</label><br>
    <select name="course" required>
        <option value="">Select Course</option>
        <option value="B.Tech CSE">B.Tech CSE</option>
        <option value="B.Tech IT">B.Tech IT</option>
        <option value="BCA">BCA</option>
        <option value="MCA">MCA</option>
    </select><br><br>

    <label>Academic Year:</label><br>
    <select name="academic_year" required>
        <option value="">Select Year</option>
        <option value="1st Year">1st Year</option>
        <option value="2nd Year">2nd Year</option>
        <option value="3rd Year">3rd Year</option>
        <option value="4th Year">4th Year</option>
    </select><br><br>

    <label>Gender:</label><br>
    <input type="radio" name="gender" value="Male" required> Male
    <input type="radio" name="gender" value="Female"> Female
    <input type="radio" name="gender" value="Other"> Other
    <br><br>

    <label>Password:</label><br>
    <input type="password" name="password" minlength="8" required><br><br>

    <label>Confirm Password:</label><br>
    <input type="password" name="confirm_password"
           minlength="8" required><br><br>

    <button type="submit">Register</button>

</form>

</body>
</html>
