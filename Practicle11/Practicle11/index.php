
<?php
include 'db.php';

if (isset($_POST['add_student'])) {
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $course = mysqli_real_escape_string($conn, $_POST['course']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: index.php?msg=invalid');
        exit;
    }

    $sql = "INSERT INTO students (name, email, course, gender)
            VALUES ('$name', '$email', '$course', '$gender')";

    header('Location: index.php?msg=' .
        (mysqli_query($conn, $sql) ? 'added' : 'error'));
    exit;
}

if (isset($_POST['update_student'])) {
    $id = (int)$_POST['id'];
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $course = mysqli_real_escape_string($conn, $_POST['course']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);

    if ($id > 0 && $name !== '' &&
        filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $sql = "UPDATE students
                SET name='$name', email='$email',
                    course='$course', gender='$gender'
                WHERE id=$id AND status='active'";

        header('Location: index.php?msg=' .
            (mysqli_query($conn, $sql) ? 'updated' : 'error'));
    } else {
        header('Location: index.php?msg=invalid');
    }
    exit;
}

if (isset($_POST['delete_student'])) {
    $id = (int)$_POST['id'];
    $sql = "UPDATE students SET status='inactive'
            WHERE id=$id AND status='active'";

    header('Location: index.php?msg=' .
        (mysqli_query($conn, $sql) ? 'deleted' : 'error'));
    exit;
}

$messages = [
    'added' => 'Student added successfully.',
    'updated' => 'Student updated successfully.',
    'deleted' => 'Student soft-deleted successfully.',
    'invalid' => 'Please enter valid student details.',
    'error' => 'Operation failed.'
];

$msg = $_GET['msg'] ?? '';
$search = mysqli_real_escape_string($conn, trim($_GET['search'] ?? ''));
$courseFilter = $_GET['course_filter'] ?? '';

$allowedCourses = [
    'Computer Science',
    'Web Development',
    'Data Science'
];

$where = ["status='active'"];

if ($search !== '') {
    $where[] = "(name LIKE '%$search%' OR email LIKE '%$search%')";
}

if (in_array($courseFilter, $allowedCourses, true)) {
    $safeCourse = mysqli_real_escape_string($conn, $courseFilter);
    $where[] = "course='$safeCourse'";
}

$sql = "SELECT * FROM students WHERE " .
       implode(' AND ', $where) . " ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Student Management System</title>
<style>
body { font-family: Arial, sans-serif; margin: 25px; background: #f4f7fb; }
h1, h2 { color: #203864; }
.card { background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; }
input, select, button, .button { padding: 10px; margin: 5px 3px; }
button, .button { background: #203864; color: white; border: 0; cursor: pointer; text-decoration: none; border-radius: 4px; display: inline-block; }
table { width: 100%; border-collapse: collapse; background: white; margin-top: 15px; }
th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
th { background: #d9e5f6; }
.message { padding: 12px; margin-bottom: 15px; background: #d9f2df; }
.actions form { display: inline; }
</style>
</head>
<body>

<h1>Student Management System</h1>

<?php if (isset($messages[$msg])): ?>
<div class="message"><?= htmlspecialchars($messages[$msg]) ?></div>
<?php endif; ?>

<div class="card">
<h2>Add New Student</h2>
<form method="POST">
    <input type="text" name="name" placeholder="Full Name" required>
    <input type="email" name="email" placeholder="Email" required>

    <select name="course" required>
        <option value="">Select Course</option>
        <?php foreach ($allowedCourses as $c): ?>
        <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
        <?php endforeach; ?>
    </select>

    <select name="gender" required>
        <option value="">Select Gender</option>
        <option>Male</option>
        <option>Female</option>
        <option>Other</option>
    </select>

    <button type="submit" name="add_student">Add Student</button>
</form>
</div>

<div class="card">
<h2>Search and Filter Students</h2>
<form method="GET" action="index.php">
    <input type="text" name="search" placeholder="Search by name or email"
           value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">

    <select name="course_filter">
        <option value="">All Courses</option>
        <?php foreach ($allowedCourses as $c): ?>
        <option value="<?= htmlspecialchars($c) ?>"
            <?= $courseFilter === $c ? 'selected' : '' ?>>
            <?= htmlspecialchars($c) ?>
        </option>
        <?php endforeach; ?>
    </select>

    <button type="submit">Search / Filter</button>
    <a class="button" href="index.php">Reset</a>
    <a class="button" href="export.php">Export CSV</a>
</form>
</div>

<div class="card">
<h2>Student Records</h2>
<table>
<thead>
<tr>
    <th>ID</th><th>Name</th><th>Email</th>
    <th>Course</th><th>Gender</th><th>Actions</th>
</tr>
</thead>
<tbody>
<?php if ($result && mysqli_num_rows($result) > 0): ?>
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
    <tr>
        <td><?= (int)$row['id'] ?></td>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td><?= htmlspecialchars($row['course']) ?></td>
        <td><?= htmlspecialchars($row['gender']) ?></td>
        <td class="actions">
            <a class="button" href="edit.php?id=<?= (int)$row['id'] ?>">Edit</a>
            <form method="POST" onsubmit="return confirm('Are you sure you want to deactivate this student?');">
                <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                <button type="submit" name="delete_student">Delete</button>
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
<tr><td colspan="6">No active student records found.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>

</body>
</html>
