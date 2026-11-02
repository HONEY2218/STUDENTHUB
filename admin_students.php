
<?php
require_once "session_check.php";
require_once "mysqli_db.php";

// Only Admin can access this page
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: student_dashboard.php?error=" .
           urlencode("Unauthorized access."));
    exit;
}

// Search and filter values
$search = trim($_GET["search"] ?? "");
$course = trim($_GET["course"] ?? "");

// Pagination
$limit = 10;
$page = max(1, (int)($_GET["page"] ?? 1));
$offset = ($page - 1) * $limit;

// Build conditions
$where = " WHERE role = 'student' AND status = 'active' ";
$types = "";
$params = [];

if ($search !== "") {
    $where .= " AND (full_name LIKE ? OR email LIKE ?) ";
    $searchValue = "%" . $search . "%";

    $types .= "ss";
    $params[] = $searchValue;
    $params[] = $searchValue;
}

if ($course !== "") {
    $where .= " AND course = ? ";
    $types .= "s";
    $params[] = $course;
}

// Helper for prepared statement parameters
function bindDynamicParams($stmt, $types, &$params) {
    if ($types === "") {
        return;
    }

    $references = [];

    foreach ($params as $key => &$value) {
        $references[$key] = &$value;
    }

    call_user_func_array(
        [$stmt, "bind_param"],
        array_merge([$types], $references)
    );
}

// Get total matching records
$countSQL = "SELECT COUNT(*) AS total FROM students " . $where;

$countStmt = $conn->prepare($countSQL);
$countParams = $params;
bindDynamicParams($countStmt, $types, $countParams);

$countStmt->execute();
$countResult = $countStmt->get_result();
$totalRecords = (int)$countResult->fetch_assoc()["total"];
$countStmt->close();

$totalPages = max(1, (int)ceil($totalRecords / $limit));

if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $limit;
}

// Get student records
$sql = "SELECT student_id, full_name, enrollment,
               roll_number, email, username, mobile,
               course, academic_year, gender, created_at
        FROM students
        " . $where . "
        ORDER BY student_id DESC
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);

$selectTypes = $types . "ii";
$selectParams = $params;
$selectParams[] = $limit;
$selectParams[] = $offset;

bindDynamicParams($stmt, $selectTypes, $selectParams);

$stmt->execute();
$result = $stmt->get_result();

// Get available courses for filter
$courseResult = $conn->query(
    "SELECT DISTINCT course
     FROM students
     WHERE status = 'active'
     ORDER BY course"
);

function escapeHTML($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$message = $_GET["message"] ?? "";
$error = $_GET["error"] ?? "";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Management | StudentHub</title>

    <link rel="stylesheet" href="style4.css">

    <style>
        .management-container {
            width: 95%;
            max-width: 1250px;
            margin: 35px auto;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.10);
        }

        .management-container h2 {
            color: #1b3260;
            margin-bottom: 20px;
        }

        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
        }

        .filter-form input,
        .filter-form select {
            padding: 11px;
            border: 1px solid #ccc;
            border-radius: 6px;
            min-width: 180px;
        }

        .btn {
            display: inline-block;
            padding: 10px 16px;
            background: #2196c7;
            color: white;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
        }

        .btn:hover {
            background: #176f99;
        }

        .btn-secondary {
            background: #1b3260;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        .student-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .student-table th,
        .student-table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
            white-space: nowrap;
        }

        .student-table th {
            background: #1b3260;
            color: white;
        }

        .student-table tr:nth-child(even) {
            background: #f2f5f9;
        }

        .message {
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 6px;
        }

        .success {
            background: #d4edda;
            color: #155724;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
        }

        .pagination {
            margin-top: 20px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .pagination a {
            padding: 8px 13px;
            background: #e5eaf2;
            color: #1b3260;
            text-decoration: none;
            border-radius: 5px;
        }

        .pagination a.current {
            background: #1b3260;
            color: white;
        }

        .record-info {
            margin: 15px 0;
            color: #555;
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

<div class="management-container">

    <h2>Student Management</h2>

    <?php if ($message !== ""): ?>
        <div class="message success">
            <?= escapeHTML($message) ?>
        </div>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <div class="message error">
            <?= escapeHTML($error) ?>
        </div>
    <?php endif; ?>

    <a href="add_student.php" class="btn">
        + Add New Student
    </a>

    <a
    href="export_students.php?search=<?= urlencode($search) ?>&course=<?= urlencode($course) ?>"
    class="btn btn-secondary"
>
    Export CSV
</a>

    <form method="GET" action="admin_students.php"
          class="filter-form" style="margin-top:20px;">

        <input
            type="text"
            name="search"
            placeholder="Search name or email"
            value="<?= escapeHTML($search) ?>"
        >

        <select name="course">
            <option value="">All Courses</option>

            <?php while ($courseRow = $courseResult->fetch_assoc()): ?>
                <option
                    value="<?= escapeHTML($courseRow["course"]) ?>"
                    <?= $course === $courseRow["course"] ? "selected" : "" ?>
                >
                    <?= escapeHTML($courseRow["course"]) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <button type="submit" class="btn">Search / Filter</button>

        <a href="admin_students.php" class="btn btn-secondary">
            Reset
        </a>

    </form>

    <div class="record-info">
        Total matching active students: <strong><?= $totalRecords ?></strong>
    </div>

    <div class="table-wrapper">

        <table class="student-table">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Enrollment</th>
                    <th>Roll No.</th>
                    <th>Email</th>
                    <th>Username</th>
                    <th>Mobile</th>
                    <th>Course</th>
                    <th>Academic Year</th>
                    <th>Gender</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>

                <?php if ($result->num_rows > 0): ?>

                    <?php while ($student = $result->fetch_assoc()): ?>

                        <tr>
                            <td><?= escapeHTML($student["student_id"]) ?></td>
                            <td><?= escapeHTML($student["full_name"]) ?></td>
                            <td><?= escapeHTML($student["enrollment"]) ?></td>
                            <td><?= escapeHTML($student["roll_number"]) ?></td>
                            <td><?= escapeHTML($student["email"]) ?></td>
                            <td><?= escapeHTML($student["username"]) ?></td>
                            <td><?= escapeHTML($student["mobile"]) ?></td>
                            <td><?= escapeHTML($student["course"]) ?></td>
                            <td><?= escapeHTML($student["academic_year"]) ?></td>
                            <td><?= escapeHTML($student["gender"]) ?></td>

                            <td>
                                <a
                                    class="btn"
                                    href="edit_student.php?id=<?= (int)$student["student_id"] ?>"
                                >
                                    Edit
                                </a>

                                <a
                                    class="btn btn-secondary"
                                    href="delete_student.php?id=<?= (int)$student["student_id"] ?>"
                                >
                                    Delete
                                </a>
                            </td>
                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="11">
                            No active student records found.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

    <div class="pagination">

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>

            <a
                href="?search=<?= urlencode($search) ?>&course=<?= urlencode($course) ?>&page=<?= $i ?>"
                class="<?= $page === $i ? "current" : "" ?>"
            >
                <?= $i ?>
            </a>

        <?php endfor; ?>

    </div>

    <p style="margin-top:25px;">
        <a href="admin_dashboard.php">← Back to Admin Dashboard</a>
    </p>

</div>

<footer>
    <h2>StudentHub Portal</h2>
    <p>CHARUSAT Campus, Changa, Gujarat 388421</p>
</footer>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
