
<?php
include 'db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$result = mysqli_query(
    $conn,
    "SELECT * FROM students WHERE id=$id AND status='active'"
);

$student = $result ? mysqli_fetch_assoc($result) : null;

if (!$student) {
    die("Student record not found or inactive.");
}

$message = "";

if (isset($_POST['update_student'])) {
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $course = mysqli_real_escape_string($conn, $_POST['course']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);

    if ($name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter valid details.";
    } else {
        $sql = "UPDATE students SET
                name='$name',
                email='$email',
                course='$course',
                gender='$gender'
                WHERE id=$id AND status='active'";

        if (mysqli_query($conn, $sql)) {
            header("Location: index.php?msg=updated");
            exit;
        } else {
            $message = "Update failed: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Edit Student</title>
<style>
body { font-family: Arial; margin: 30px; background: #f4f7fb; }
form { background: white; padding: 20px; max-width: 450px; }
input, select, button { display: block; margin: 12px 0; padding: 10px; width: 95%; }
button { background: #203864; color: white; border: 0; cursor: pointer; }
</style>
</head>
<body>
<h1>Edit Student Details</h1>

<?php if ($message !== ""): ?>
<p><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<form method="POST">
    <label>Full Name</label>
    <input type="text" name="name"
           value="<?= htmlspecialchars($student['name']) ?>" required>

    <label>Email</label>
    <input type="email" name="email"
           value="<?= htmlspecialchars($student['email']) ?>" required>

    <label>Course</label>
    <select name="course" required>
        <?php foreach (['Computer Science','Web Development','Data Science'] as $c): ?>
        <option value="<?= htmlspecialchars($c) ?>"
            <?= $student['course'] === $c ? 'selected' : '' ?>>
            <?= htmlspecialchars($c) ?>
        </option>
        <?php endforeach; ?>
    </select>

    <label>Gender</label>
    <select name="gender" required>
        <?php foreach (['Male','Female','Other'] as $g): ?>
        <option value="<?= htmlspecialchars($g) ?>"
            <?= $student['gender'] === $g ? 'selected' : '' ?>>
            <?= htmlspecialchars($g) ?>
        </option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="update_student">Update Student</button>
    <a href="index.php">Back to Student List</a>
</form>
</body>
</html>
