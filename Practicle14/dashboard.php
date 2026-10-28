
<?php
require_once 'auth.php';
requireAdmin();
require_once 'db.php';

// Record the admin dashboard visit in the audit log.
$log = $pdo->prepare(
    "INSERT INTO audit_logs (user_id, action, details, ip_address)
     VALUES (?, ?, ?, ?)"
);
$log->execute([
    $_SESSION['user_id'],
    'VIEW_DASHBOARD',
    'Admin opened the dashboard',
    $_SERVER['REMOTE_ADDR'] ?? null
]);

// Load dashboard statistics dynamically.
$totalUsers = (int) $pdo->query(
    "SELECT COUNT(*) FROM users"
)->fetchColumn();

$totalStudents = (int) $pdo->query(
    "SELECT COUNT(*) FROM students"
)->fetchColumn();

$totalEvents = (int) $pdo->query(
    "SELECT COUNT(*) FROM events"
)->fetchColumn();

$totalRegistrations = (int) $pdo->query(
    "SELECT COUNT(*) FROM registrations"
)->fetchColumn();

// Get the latest registrations.
$stmt = $pdo->query(
    "SELECT r.id, s.name AS student_name,
            s.email, e.title AS event_title, r.registered_at
     FROM registrations r
     INNER JOIN students s ON r.student_id = s.id
     INNER JOIN events e ON r.event_id = e.id
     ORDER BY r.registered_at DESC, r.id DESC
     LIMIT 10"
);
$registrations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Practical 14 - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">




<?php require_once 'menu.php'; ?>

<main class="container py-4">
    <h2 class="mb-4">Dashboard Overview</h2>

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card shadow-sm border-primary h-100">
                <div class="card-body">
                    <h6 class="text-muted">Total Users</h6>
                    <h2><?= $totalUsers ?></h2>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card shadow-sm border-success h-100">
                <div class="card-body">
                    <h6 class="text-muted">Total Students</h6>
                    <h2><?= $totalStudents ?></h2>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card shadow-sm border-warning h-100">
                <div class="card-body">
                    <h6 class="text-muted">Total Events</h6>
                    <h2><?= $totalEvents ?></h2>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card shadow-sm border-info h-100">
                <div class="card-body">
                    <h6 class="text-muted">Total Registrations</h6>
                    <h2><?= $totalRegistrations ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            Latest Registrations
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Student Name</th>
                            <th>Email</th>
                            <th>Event</th>
                            <th>Registration Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (count($registrations) > 0): ?>
                        <?php foreach ($registrations as $row): ?>
                            <tr>
                                <td><?= (int) $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['student_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($row['event_title'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($row['registered_at'], ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">
                                No registrations found.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <p class="text-muted mt-3">
        Dashboard statistics are loaded dynamically from the MySQL database.
    </p>
</main>

</body>
</html>
