
<?php
require_once "db.php";

$student = null;
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $enrollment = trim($_POST["enrollment"] ?? "");

    if ($enrollment === "") {
        $message = "Please enter Enrollment Number.";
    } else {

        try {
            // Prepared Statement
            $sql = "SELECT student_id, full_name, enrollment,
                           roll_number, email, mobile, course,
                           academic_year, gender, created_at
                    FROM students
                    WHERE enrollment = :enrollment";

            $stmt = $pdo->prepare($sql);

            // Bind parameter and execute
            $stmt->bindValue(":enrollment", $enrollment, PDO::PARAM_STR);
            $stmt->execute();

            $student = $stmt->fetch();

            if (!$student) {
                $message = "No student found with this Enrollment Number.";
            }

        } catch (PDOException $e) {
            $message = "Database Error: Unable to search student.";
        }
    }
}

function clean($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub | Search Student</title>
    <link rel="stylesheet" href="style4.css">
</head>

<body>

<main>
    <div class="card">

        <h2>Search Student</h2>
        <p>Enter Enrollment Number to find student details.</p>

        <form method="POST" action="student_search.php">

            <div class="form-field">
                <label for="enrollment">Enrollment Number</label>

                <input
                    type="text"
                    id="enrollment"
                    name="enrollment"
                    placeholder="Enter Enrollment Number"
                    required
                >
            </div>

            <div class="buttons">
                <input type="submit" value="Search Student">
            </div>

        </form>

        <?php if ($message !== "") { ?>
            <p><?php echo clean($message); ?></p>
        <?php } ?>

        <?php if ($student) { ?>

            <h2>Student Details</h2>

            <div class="table-container">
                <table>
                    <tr><th>Student ID</th><td><?php echo clean($student["student_id"]); ?></td></tr>
                    <tr><th>Full Name</th><td><?php echo clean($student["full_name"]); ?></td></tr>
                    <tr><th>Enrollment</th><td><?php echo clean($student["enrollment"]); ?></td></tr>
                    <tr><th>Roll Number</th><td><?php echo clean($student["roll_number"]); ?></td></tr>
                    <tr><th>Email</th><td><?php echo clean($student["email"]); ?></td></tr>
                    <tr><th>Mobile</th><td><?php echo clean($student["mobile"]); ?></td></tr>
                    <tr><th>Course</th><td><?php echo clean($student["course"]); ?></td></tr>
                    <tr><th>Academic Year</th><td><?php echo clean($student["academic_year"]); ?></td></tr>
                    <tr><th>Gender</th><td><?php echo clean($student["gender"]); ?></td></tr>
                    <tr><th>Created At</th><td><?php echo clean($student["created_at"]); ?></td></tr>
                </table>
            </div>

        <?php } ?>

        <p><a href="home.html">Back to Home</a></p>

    </div>
</main>

</body>
</html>
