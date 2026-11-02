
<?php
require_once 'auth.php';
requireLogin();
require_once 'db.php';

// Only editor and admin can access Event Management.
$role = $_SESSION['role'] ?? 'user';

if (!in_array($role, ['editor', 'admin'], true)) {
    require_once 'forbidden.php';
    exit;
}

// Fetch event records from the database.
$stmt = $pdo->query(
    "SELECT id, title, event_date, created_at
     FROM events
     ORDER BY event_date ASC"
);

$events = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Practical 14 - Event Management</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>
<body class="bg-light">

<?php require_once 'menu.php'; ?>

<main class="container py-4">
    <h2 class="mb-4">Event Management</h2>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            Event Records
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Event Title</th>
                            <th>Event Date</th>
                            <th>Created At</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (count($events) > 0): ?>
                        <?php foreach ($events as $event): ?>
                            <tr>
                                <td><?= (int) $event['id'] ?></td>
                                <td><?= htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($event['event_date'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($event['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">
                                No events found.
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
