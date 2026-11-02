
<?php
require_once "session_check.php";
require_once "mysqli_db.php";

// Admin access only
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: student_dashboard.php?error=" .
           urlencode("Unauthorized access."));
    exit;
}

// CSRF token
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

function clean($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$message = "";
$messageType = "";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

// Validate student ID
if (!$id || $id <= 0) {
    header("Location: admin_students.php?error=" .
           urlencode("Invalid student ID."));
    exit;
}

// Fetch student details
$stmt = $conn->prepare(
    "SELECT student_id, full_name, enrollment, email, course
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
           urlencode("Student not found or already inactive."));
    exit;
}

// Process delete confirmation
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $csrfToken = $_POST["csrf_token"] ?? "";

    if (!hash_equals($_SESSION["csrf_token"], $csrfToken)) {

        $message = "Invalid security token.";
        $messageType = "error";

    } else {

        // Soft delete: change status only
        $delete = $conn->prepare(
            "UPDATE students
             SET status = 'inactive'
             WHERE student_id = ? AND role = 'student' AND status = 'active'"
        );

        $delete->bind_param("i", $id);

        if ($delete->execute() && $delete->affected_rows === 1) {

            $delete->close();
            $conn->close();

            header("Location: admin_students.php?message=" .
                   urlencode("Student moved to inactive successfully."));
            exit;

        } else {

            $message = "Unable to delete student.";
            $messageType = "error";
        }

        $delete->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>StudentHub | Delete Student</title>

    <link rel="stylesheet" href="style4.css">

    <style>
        .delete-container {
            max-width: 550px;
            margin: 60px auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.10);
        }

        .delete-container h2 {
            color: #b42318;
            text-align: center;
        }

        .student-details {
            background: #f2f5f9;
            padding: 18px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .student-details p {
            margin: 10px 0;
        }

        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 13px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .delete-button {
            background: #dc3545;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .cancel-button {
            display: inline-block;
            background: #1b3260;
            color: white;
            padding: 12px 20px;
            border-radius: 6px;
            text-decoration: none;
            margin-left: 8px;
        }

        .message {
            padding: 13px;
            border-radius: 6px;
            margin-bottom: 15px;
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

    <div class="delete-container">

        <h2>Confirm Student Deletion</h2>

        <div class="warning">
            Are you sure you want to deactivate this student?
            This record will not be permanently deleted.
        </div>

        <?php if ($message !== ""): ?>
            <div class="message error">
                <?= clean($message) ?>
            </div>
        <?php endif; ?>

        <div class="student-details">

            <p>
                <strong>Student ID:</strong>
                <?= (int)$student["student_id"] ?>
            </p>

            <p>
                <strong>Name:</strong>
                <?= clean($student["full_name"]) ?>
            </p>

            <p>
                <strong>Enrollment:</strong>
                <?= clean($student["enrollment"]) ?>
            </p>

            <p>
                <strong>Email:</strong>
                <?= clean($student["email"]) ?>
            </p>

            <p>
                <strong>Course:</strong>
                <?= clean($student["course"]) ?>
            </p>

        </div>

        <form method="POST"
              action="delete_student.php?id=<?= (int)$id ?>">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= clean($_SESSION["csrf_token"]) ?>"
            >

            <button type="submit" class="delete-button">
                Yes, Deactivate Student
            </button>

            <a href="admin_students.php" class="cancel-button">
                Cancel
            </a>

        </form>

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
