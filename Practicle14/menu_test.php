
<?php
require_once 'auth.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Practical 14 - Role Menu Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">

<?php require_once 'menu.php'; ?>

<div class="container py-5">
    <div class="card shadow-sm">
        <div class="card-body">
            <h2>Dynamic Role-Based Menu</h2>
            <p>
                Logged-in user:
                <strong><?= htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8') ?></strong>
            </p>
            <p>
                Current role:
                <strong><?= htmlspecialchars($_SESSION['role'], ENT_QUOTES, 'UTF-8') ?></strong>
            </p>
            <p>
                The navigation menu above is displayed according to
                the user's role.
            </p>
        </div>
    </div>
</div>

</body>
</html>
