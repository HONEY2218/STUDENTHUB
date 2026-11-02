
<?php
require_once "session_check.php";
require_once "mysqli_db.php";

// Admin access only
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: student_dashboard.php?error=" .
           urlencode("Unauthorized access."));
    exit;
}

$message = "";
$messageType = "";

function clean($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

// Generate CSRF token
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["full_name"] ?? "");
    $enrollment = trim($_POST["enrollment"] ?? "");
    $roll = trim($_POST["roll_number"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $mobile = trim($_POST["mobile"] ?? "");
    $course = trim($_POST["course"] ?? "");
    $year = trim($_POST["academic_year"] ?? "");
    $gender = trim($_POST["gender"] ?? "");
    $password = $_POST["password"] ?? "";
    $csrfToken = $_POST["csrf_token"] ?? "";

    // CSRF validation
    if (!hash_equals($_SESSION["csrf_token"], $csrfToken)) {

        $message = "Invalid security token. Please try again.";
        $messageType = "error";

    } elseif (
        $name === "" || $enrollment === "" || $roll === "" ||
        $email === "" || $username === "" || $mobile === "" ||
        $course === "" || $year === "" || $gender === "" ||
        $password === ""
    ) {

        $message = "Please fill in all fields.";
        $messageType = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $messageType = "error";

    } elseif (!preg_match("/^[0-9]{10}$/", $mobile)) {

        $message = "Mobile number must contain exactly 10 digits.";
        $messageType = "error";

    } elseif (!preg_match("/^[0-9]{1,3}$/", $roll)) {

        $message = "Roll number must contain 1 to 3 digits.";
        $messageType = "error";

    } elseif (strlen($password) < 8) {

        $message = "Password must contain at least 8 characters.";
        $messageType = "error";

    } else {

        // Check duplicate enrollment, email and username
        $check = $conn->prepare(
            "SELECT student_id
             FROM students
             WHERE enrollment = ? OR email = ? OR username = ?
             LIMIT 1"
        );

        $check->bind_param("sss", $enrollment, $email, $username);
        $check->execute();

        $duplicate = $check->get_result()->fetch_assoc();
        $check->close();

        if ($duplicate) {

            $message = "Enrollment Number, Email or Username already exists.";
            $messageType = "error";

        } else {

            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO students
                    (full_name, enrollment, roll_number, email,
                     username, mobile, course, academic_year,
                     gender, password_hash, role, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'student', 'active')";

            $stmt = $conn->prepare($sql);

            $stmt->bind_param(
                "ssssssssss",
                $name,
                $enrollment,
                $roll,
                $email,
                $username,
                $mobile,
                $course,
                $year,
                $gender,
                $passwordHash
            );

            if ($stmt->execute()) {

                $message = "Student added successfully!";
                $messageType = "success";

                // Generate a fresh CSRF token
                $_SESSION["csrf_token"] = bin2hex(random_bytes(32));

            } else {

                if ($stmt->errno === 1062) {
                    $message = "Duplicate Enrollment, Email or Username.";
                } else {
                    $message = "Unable to add student. Please try again.";
                }

                $messageType = "error";
            }

            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>StudentHub | Add Student</title>

    <link rel="stylesheet" href="style4.css">

    <style>
        .add-student-container {
            max-width: 650px;
            margin: 35px auto;
            padding: 25px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.10);
        }

        .add-student-container h2 {
            color: #1b3260;
            text-align: center;
        }

        .form-field {
            margin-bottom: 16px;
        }

        .form-field label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        .form-field input,
        .form-field select {
            width: 100%;
            padding: 11px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }

        .add-button {
            background: #2196c7;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .message {
            padding: 13px;
            margin-bottom: 18px;
            border-radius: 6px;
        }

        .success {
            background: #dff5e5;
            color: #155724;
        }

        .error {
            background: #fde7e7;
            color: #721c24;
        }
    </style>
</head>

<body>

<header>
    <h1>StudentHub Portal</h1>

    <nav>
        <a href="home.html">Home</a>
        <a href="admin_dashboard.php">Admin Dashboard</a>
        <a href="admin_students.php">Manage Students</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main>

    <div class="add-student-container">

        <h2>Add New Student</h2>

        <p>Enter student details to create a new student record.</p>

        <?php if ($message !== ""): ?>
            <div class="message <?= $messageType ?>">
                <?= clean($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="add_student.php">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= clean($_SESSION["csrf_token"]) ?>"
            >

            <div class="form-field">
                <label>Full Name</label>
                <input type="text" name="full_name"
                       maxlength="100" required>
            </div>

            <div class="form-field">
                <label>Enrollment Number</label>
                <input type="text" name="enrollment"
                       maxlength="20" required>
            </div>

            <div class="form-field">
                <label>Roll Number</label>
                <input type="text" name="roll_number"
                       maxlength="10" required>
            </div>

            <div class="form-field">
                <label>Email</label>
                <input type="email" name="email"
                       maxlength="100" required>
            </div>

            <div class="form-field">
                <label>Username</label>
                <input type="text" name="username"
                       maxlength="50" required>
            </div>

            <div class="form-field">
                <label>Mobile</label>
                <input type="tel" name="mobile"
                       maxlength="15" pattern="[0-9]{10}" required>
            </div>

            <div class="form-field">
                <label>Course</label>
                <select name="course" required>
                    <option value="">Select Course</option>
                    <option value="B.Tech CSE">B.Tech CSE</option>
                    <option value="B.Tech IT">B.Tech IT</option>
                    <option value="BCA">BCA</option>
                    <option value="MCA">MCA</option>
                </select>
            </div>

            <div class="form-field">
                <label>Academic Year</label>
                <select name="academic_year" required>
                    <option value="">Select Year</option>
                    <option value="1st Year">1st Year</option>
                    <option value="2nd Year">2nd Year</option>
                    <option value="3rd Year">3rd Year</option>
                    <option value="4th Year">4th Year</option>
                </select>
            </div>

            <div class="form-field">
                <label>Gender</label>
                <select name="gender" required>
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div class="form-field">
                <label>Password</label>
                <input type="password" name="password"
                       minlength="8" required>
            </div>

            <button type="submit" class="add-button">
                Add Student
            </button>

            <button type="reset" class="add-button">
                Clear
            </button>

        </form>

        <p>
            <a href="admin_students.php">← Back to Student Management</a>
        </p>

    </div>

</main>

<footer>
    <h2>StudentHub Portal</h2>
    <p>CHARUSAT Campus, Changa, Gujarat 388421</p>
</footer>

</body>
</html>

<?php
$conn->close();
?>
