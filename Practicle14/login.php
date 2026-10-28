
<?php
session_start();
require_once 'db.php';

if (isset($_SESSION['user_id'])) {
    if (($_SESSION['role'] ?? '') === 'admin') {
        header('Location: dashboard.php');
    } else {
        header('Location: menu_test.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare(
        "SELECT id, username, password, role, status
         FROM users
         WHERE username = ?
         LIMIT 1"
    );

    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (
        $user &&
        password_verify($password, $user['password']) &&
        $user['status'] === 'active'
    ) {
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header('Location: dashboard.php');
        } else {
            header('Location: menu_test.php');
        }

        exit;
    }

    $error = 'Invalid username or password, or account inactive.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Practical 14 - Login</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow mx-auto" style="max-width: 420px;">

        <div class="card-body p-4">

            <h2 class="text-center mb-4">
                Admin Dashboard Login
            </h2>

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars(
                        $error,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">

                <div class="mb-3">
                    <label class="form-label">Username</label>

                    <input
                        type="text"
                        name="username"
                        class="form-control"
                        autocomplete="username"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>

                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <button
                    type="submit"
                    class="btn btn-primary w-100"
                >
                    Login
                </button>

            </form>

        </div>
    </div>
</div>

</body>
</html>
