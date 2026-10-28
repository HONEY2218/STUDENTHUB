
<?php
require_once 'auth.php';
requireLogin();
require_once 'db.php';

// Only editor and admin can access this page.
$role = $_SESSION['role'] ?? 'user';

if (!in_array($role, ['editor', 'admin'], true)) {
    require_once 'forbidden.php';
    exit;
}

// Load student records from the database.
$stmt = $pdo->query(
    "SELECT id, name, email, course, created_at
     FROM students
     ORDER BY id DESC"
);

$students = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Practical 14 - Students</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>
<body class="bg-light">

<?php require_once 'menu.php'; ?>

<main class="container py-4">
    <h2 class="mb-4">Students Management</h2>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            Student Records
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Course</th>
                            <th>Created At</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (count($students) > 0): ?>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?= (int) $student['id'] ?></td>
                                <td><?= htmlspecialchars($student['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($student['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($student['course'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($student['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">
                                No students found.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <a href="menu_test.php" class="btn btn-secondary">
                Back
            </a>
        </div>
    </div>
</main>

</body>
</html>
