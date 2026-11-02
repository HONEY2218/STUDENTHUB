
<?php
require_once "session_check.php";
require_once "mysqli_db.php";

// Admin access only
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    http_response_code(403);
    exit("Unauthorized access.");
}

// Get search and course filters
$search = trim($_GET["search"] ?? "");
$course = trim($_GET["course"] ?? "");

$sql = "SELECT student_id, full_name, enrollment,
               roll_number, email, username, mobile,
               course, academic_year, gender, created_at
        FROM students
        WHERE role = 'student' AND status = 'active'";

$types = "";
$params = [];

if ($search !== "") {
    $sql .= " AND (full_name LIKE ? OR email LIKE ?)";
    $value = "%" . $search . "%";

    $types .= "ss";
    $params[] = $value;
    $params[] = $value;
}

if ($course !== "") {
    $sql .= " AND course = ?";
    $types .= "s";
    $params[] = $course;
}

$sql .= " ORDER BY student_id ASC";

$stmt = $conn->prepare($sql);

// Bind dynamic parameters
if ($types !== "") {
    $bind = [$types];

    foreach ($params as $key => &$value) {
        $bind[] = &$value;
    }

    call_user_func_array([$stmt, "bind_param"], $bind);
}

$stmt->execute();
$result = $stmt->get_result();

// CSV download headers
header("Content-Type: text/csv; charset=UTF-8");
header("Content-Disposition: attachment; filename=StudentHub_Students.csv");

$output = fopen("php://output", "w");

// UTF-8 BOM for Excel compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// CSV column headings
fputcsv($output, [
    "Student ID",
    "Full Name",
    "Enrollment",
    "Roll Number",
    "Email",
    "Username",
    "Mobile",
    "Course",
    "Academic Year",
    "Gender",
    "Created At"
]);

// Export student records
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row["student_id"],
        $row["full_name"],
        $row["enrollment"],
        $row["roll_number"],
        $row["email"],
        $row["username"],
        $row["mobile"],
        $row["course"],
        $row["academic_year"],
        $row["gender"],
        $row["created_at"]
    ]);
}

fclose($output);
$stmt->close();
$conn->close();
exit;
?>
