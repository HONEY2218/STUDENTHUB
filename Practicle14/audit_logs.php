
<?php
require_once 'auth.php';
requireAdmin();
require_once 'db.php';

$stmt = $pdo->query(
    "SELECT a.id, a.action, a.details, a.ip_address,
            a.created_at, u.username
     FROM audit_logs a
     LEFT JOIN users u ON a.user_id = u.id
     ORDER BY a.id DESC
     LIMIT 100"
);

$logs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Practical 14 - Audit Logs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">

<?php require_once 'menu.php'; ?>

<div class="container py-4">
    <h2 class="mb-4">Audit Logs</h2>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            Recent Admin Activities
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Details</th>
                            <th>IP Address</th>
                            <th>Date and Time</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (count($logs) > 0): ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= (int) $log['id'] ?></td>
                                <td><?= htmlspecialchars($log['username'] ?? 'Deleted user', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($log['action'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($log['details'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($log['ip_address'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($log['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">
                                No audit records found.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <a href="dashboard.php" class="btn btn-secondary">
                Back to Dashboard
            </a>
        </div>
    </div>
</div>

</body>
</html>
