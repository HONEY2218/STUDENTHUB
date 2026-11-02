
<?php
require_once "session_check.php";
require_once "mysqli_db.php";

// Admin access only
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: student_dashboard.php?error=" .
           urlencode("Unauthorized access."));
    exit;
}

function clean($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

// CSRF token
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$message = "";
$messageType = "";

// Get student ID
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = filter_input(INPUT_POST, "student_id", FILTER_VALIDATE_INT);
}

if (!$id || $id <= 0) {
    header("Location: admin_students.php?error=" .
           urlencode("Invalid student ID."));
    exit;
}

// Fetch existing student
$stmt = $conn->prepare(
    "SELECT student_id, full_name, enrollment, roll_number,
            email, username, mobile, course, academic_year, gender
     FROM students
     WHERE student_id = ? AND role = 'student' AND status = 'active'
LIMIT 1"
);

$stmt->bind_param("i", $id);
$stmt->execute();

$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    header("Location: admin_students.php?error=" .
           urlencode("Student record not found."));
    exit;
}

// Update student
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $csrfToken = $_POST["csrf_token"] ?? "";

    $name = trim($_POST["full_name"] ?? "");
    $enrollment = trim($_POST["enrollment"] ?? "");
    $roll = trim($_POST["roll_number"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $mobile = trim($_POST["mobile"] ?? "");
    $course = trim($_POST["course"] ?? "");
    $year = trim($_POST["academic_year"] ?? "");
    $gender = trim($_POST["gender"] ?? "");

    if (!hash_equals($_SESSION["csrf_token"], $csrfToken)) {
        $message = "Invalid security token. Please try again.";
        $messageType = "error";

    } elseif (
        $name === "" || $enrollment === "" || $roll === "" ||
        $email === "" || $username === "" || $mobile === "" ||
        $course === "" || $year === "" || $gender === ""
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

    } else {

        // Check duplicates excluding current student
        $check = $conn->prepare(
            "SELECT student_id
             FROM students
             WHERE (enrollment = ? OR email = ? OR username = ?)
             AND student_id != ?
             LIMIT 1"
        );

        $check->bind_param(
            "sssi",
            $enrollment,
            $email,
            $username,
            $id
        );

        $check->execute();
        $duplicate = $check->get_result()->fetch_assoc();
        $check->close();

        if ($duplicate) {

            $message = "Enrollment, Email or Username already exists.";
            $messageType = "error";

        } else {

            $update = $conn->prepare(
                "UPDATE students
                 SET full_name = ?,
                     enrollment = ?,
                     roll_number = ?,
                     email = ?,
                     username = ?,
                     mobile = ?,
                     course = ?,
                     academic_year = ?,
                     gender = ?
                 WHERE student_id = ? AND role = 'student' AND status = 'active'"
            );

            $update->bind_param(
                "sssssssssi",
                $name,
                $enrollment,
                $roll,
                $email,
                $username,
                $mobile,
                $course,
                $year,
                $gender,
                $id
            );

            if ($update->execute()) {

                $message = "Student details updated successfully!";
                $messageType = "success";

                // Refresh student details
                $stmt = $conn->prepare(
                    "SELECT student_id, full_name, enrollment,
                            roll_number, email, username, mobile,
                            course, academic_year, gender
                     FROM students
                     WHERE student_id = ? AND status = 'active'"
                );

                $stmt->bind_param("i", $id);
                $stmt->execute();
                $student = $stmt->get_result()->fetch_assoc();
                $stmt->close();

            } else {

                $message = "Unable to update student.";
                $messageType = "error";
            }

            $update->close();
        }
    }

    // Preserve submitted values after validation errors
    if ($messageType === "error") {
        $student["full_name"] = $name;
        $student["enrollment"] = $enrollment;
        $student["roll_number"] = $roll;
        $student["email"] = $email;
        $student["username"] = $username;
        $student["mobile"] = $mobile;
        $student["course"] = $course;
        $student["academic_year"] = $year;
        $student["gender"] = $gender;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>StudentHub | Edit Student</title>

    <link rel="stylesheet" href="style4.css">

    <style>
        .edit-container {
            max-width: 650px;
            margin: 35px auto;
            padding: 25px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.10);
        }

        .edit-container h2 {
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

        .btn {
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

    <div class="edit-container">

        <h2>Edit Student Details</h2>

        <?php if ($message !== ""): ?>
            <div class="message <?= $messageType ?>">
                <?= clean($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="edit_student.php">

            <input type="hidden" name="student_id"
                   value="<?= (int)$id ?>">

            <input type="hidden" name="csrf_token"
                   value="<?= clean($_SESSION["csrf_token"]) ?>">

            <div class="form-field">
                <label>Full Name</label>
                <input type="text" name="full_name"
                       maxlength="100"
                       value="<?= clean($student["full_name"]) ?>" required>
            </div>

            <div class="form-field">
                <label>Enrollment Number</label>
                <input type="text" name="enrollment"
                       maxlength="20"
                       value="<?= clean($student["enrollment"]) ?>" required>
            </div>

            <div class="form-field">
                <label>Roll Number</label>
                <input type="text" name="roll_number"
                       maxlength="10"
                       value="<?= clean($student["roll_number"]) ?>" required>
            </div>

            <div class="form-field">
                <label>Email</label>
                <input type="email" name="email"
                       maxlength="100"
                       value="<?= clean($student["email"]) ?>" required>
            </div>

            <div class="form-field">
                <label>Username</label>
                <input type="text" name="username"
                       maxlength="50"
                       value="<?= clean($student["username"]) ?>" required>
            </div>

            <div class="form-field">
                <label>Mobile</label>
                <input type="tel" name="mobile"
                       pattern="[0-9]{10}"
                       value="<?= clean($student["mobile"]) ?>" required>
            </div>

            <div class="form-field">
                <label>Course</label>
                <select name="course" required>
                    <?php foreach (["B.Tech CSE", "B.Tech IT", "BCA", "MCA"] as $c): ?>
                        <option value="<?= clean($c) ?>"
                            <?= $student["course"] === $c ? "selected" : "" ?>>
                            <?= clean($c) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-field">
                <label>Academic Year</label>
                <select name="academic_year" required>
                    <?php foreach (["1st Year", "2nd Year", "3rd Year", "4th Year"] as $y): ?>
                        <option value="<?= clean($y) ?>"
                            <?= $student["academic_year"] === $y ? "selected" : "" ?>>
                            <?= clean($y) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-field">
                <label>Gender</label>
                <select name="gender" required>
                    <?php foreach (["Male", "Female", "Other"] as $g): ?>
                        <option value="<?= clean($g) ?>"
                            <?= $student["gender"] === $g ? "selected" : "" ?>>
                            <?= clean($g) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn">
                Update Student
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
