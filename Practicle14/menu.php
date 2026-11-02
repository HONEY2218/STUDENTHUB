
<?php
require_once 'auth.php';
requireLogin();
require_once 'db.php';

$role = $_SESSION['role'] ?? 'user';

$roleLevels = [
    'user' => 1,
    'editor' => 2,
    'admin' => 3
];

$currentLevel = $roleLevels[$role] ?? 0;

$stmt = $pdo->query(
    "SELECT title, link, min_role
     FROM menu_items
     ORDER BY id ASC"
);

$menuItems = $stmt->fetchAll();
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand"
           href="<?= $role === 'admin' ? 'dashboard.php' : 'menu_test.php' ?>">
            Practical 14
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#mainMenu"
                aria-controls="mainMenu"
                aria-expanded="false"
                aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse show" id="mainMenu">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php foreach ($menuItems as $item): ?>
                    <?php
                    $requiredLevel =
                        $roleLevels[$item['min_role']] ?? 99;
                    ?>

                    <?php if ($currentLevel >= $requiredLevel): ?>
                        <li class="nav-item">
                            <a class="nav-link"
                               href="<?= htmlspecialchars(
                                   $item['link'],
                                   ENT_QUOTES,
                                   'UTF-8'
                               ) ?>">
                                <?= htmlspecialchars(
                                    $item['title'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>

            <span class="navbar-text text-white me-3">
                <?= htmlspecialchars(
                    $_SESSION['username'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
                (<?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?>)
            </span>

            <a class="btn btn-danger btn-sm" href="logout.php">
                Logout
            </a>
        </div>
    </div>
</nav>
