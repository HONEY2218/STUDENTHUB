<?php
require 'functions.php';
require 'db.php';
require_admin();

$errors = [];
$old = ['title' => '', 'event_date' => '', 'location' => '', 'category' => '', 'status' => 'Open'];

/* ---------- CREATE ---------- */
if (post_too_large()) {
    $errors[] = 'The upload is too large. Maximum allowed poster size is ' . MAX_POSTER_MB . ' MB.';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_event'])) {
    $old    = event_input();
    $errors = validate_event($old);

    $poster_name = null;
    if (!$errors) {
        $up = save_poster($_FILES['poster'] ?? null);
        if ($up['error']) {
            $errors[] = $up['error'];
        } else {
            $poster_name = $up['name'];
        }
    }

    if (!$errors) {
        $stmt = mysqli_prepare($conn,
            "INSERT INTO events (title, event_date, location, category, status, poster) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssssss",
            $old['title'], $old['event_date'], $old['location'], $old['category'], $old['status'], $poster_name);

        if (mysqli_stmt_execute($stmt)) {
            flash('success', 'Event "' . $old['title'] . '" was added successfully.');
            header('Location: index.php');
            exit();
        }
        delete_poster($poster_name);                      // DB failed: do not leave an orphan file
        $errors[] = 'Database error: the event could not be saved.';
    }
}

/* ---------- DELETE (POST, with confirm in the browser) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_event'])) {
    $id = (int)($_POST['id'] ?? 0);

    $stmt = mysqli_prepare($conn, "SELECT poster FROM events WHERE id = ?");   // need the file name first
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$row) {
        flash('danger', 'Event not found.');
    } else {
        $stmt = mysqli_prepare($conn, "DELETE FROM events WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            delete_poster($row['poster']);               // remove the image only after the row is gone
            flash('success', 'Event and its poster were deleted.');
        } else {
            flash('danger', 'Delete failed. Please try again.');
        }
    }
    header('Location: index.php');
    exit();
}

/* ---------- STATUS TOGGLE (Open <-> Closed) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_status'])) {
    $id = (int)($_POST['id'] ?? 0);
    $stmt = mysqli_prepare($conn,
        "UPDATE events SET status = IF(status = 'Open', 'Closed', 'Open') WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
        flash('success', 'Event status updated.');
    } else {
        flash('danger', 'Could not update the status.');
    }
    header('Location: index.php?' . http_build_query(array_filter([
        'search' => $_GET['search'] ?? '', 'category_filter' => $_GET['category_filter'] ?? '', 'status_filter' => $_GET['status_filter'] ?? ''])));
    exit();
}

/* ---------- READ with SEARCH + FILTERS ---------- */
$search          = trim($_GET['search'] ?? '');
$category_filter = trim($_GET['category_filter'] ?? '');
$status_filter   = trim($_GET['status_filter'] ?? '');

$sql    = "SELECT * FROM events";
$where  = [];
$types  = '';
$params = [];

if ($search !== '') {
    $where[] = "(title LIKE ? OR location LIKE ?)";
    $like = '%' . $search . '%';
    $types .= 'ss';
    array_push($params, $like, $like);
}
if ($category_filter !== '') {
    $where[] = "category = ?";
    $types .= 's';
    $params[] = $category_filter;
}
if ($status_filter !== '') {
    $where[] = "status = ?";
    $types .= 's';
    $params[] = $status_filter;
}
if ($where) {
    $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " ORDER BY event_date ASC, id DESC";

$stmt = mysqli_prepare($conn, $sql);
if ($params) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$total  = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Event Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .poster-img { width: 70px; height: 90px; object-fit: cover; border-radius: 4px; }
        .no-poster  { width: 70px; height: 90px; display: flex; align-items: center; justify-content: center;
                      background: #e9ecef; color: #6c757d; font-size: 12px; border-radius: 4px; }
    </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <span class="navbar-brand">Event Management System</span>
        <span class="text-white">
            Logged in as <strong><?= e($_SESSION['admin_name'] ?? 'admin') ?></strong>
            <a href="logout.php" class="btn btn-sm btn-outline-light ms-2">Logout</a>
        </span>
    </div>
</nav>

<div class="container pb-5">
    <?php show_flash(); ?>

    <!-- ADD EVENT -->
    <div class="card shadow-sm mb-4">
        <div class="card-header fw-semibold">Add New Event</div>
        <div class="card-body">
            <?php if ($errors): ?>
                <div class="alert alert-danger mb-3">
                    <strong>Please fix the following:</strong>
                    <ul class="mb-0">
                        <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <form method="POST" action="index.php" enctype="multipart/form-data" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="title">Title</label>
                    <input class="form-control" id="title" name="title" maxlength="150" value="<?= e($old['title']) ?>" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="event_date">Date</label>
                    <input class="form-control" type="date" id="event_date" name="event_date" value="<?= e($old['event_date']) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="location">Location</label>
                    <input class="form-control" id="location" name="location" maxlength="150" value="<?= e($old['location']) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="category">Category</label>
                    <select class="form-select" id="category" name="category" required>
                        <option value="">Select category</option>
                        <?php foreach (CATEGORIES as $c): ?>
                            <option value="<?= e($c) ?>" <?= $old['category'] === $c ? 'selected' : '' ?>><?= e($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="status">Status</label>
                    <select class="form-select" id="status" name="status">
                        <?php foreach (STATUSES as $s): ?>
                            <option value="<?= e($s) ?>" <?= $old['status'] === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="poster">Poster (optional, JPG/PNG/WEBP, max <?= MAX_POSTER_MB ?> MB)</label>
                    <input class="form-control" type="file" id="poster" name="poster" accept=".jpg,.jpeg,.png,.webp">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-primary w-100" type="submit" name="add_event">Add Event</button>
                </div>
            </form>
        </div>
    </div>

    <!-- SEARCH + FILTER -->
    <form method="GET" action="index.php" class="row g-2 mb-3">
        <div class="col-md-4">
            <input class="form-control" name="search" placeholder="Search title or location" value="<?= e($search) ?>">
        </div>
        <div class="col-md-3">
            <select class="form-select" name="category_filter">
                <option value="">All categories</option>
                <?php foreach (CATEGORIES as $c): ?>
                    <option value="<?= e($c) ?>" <?= $category_filter === $c ? 'selected' : '' ?>><?= e($c) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select class="form-select" name="status_filter">
                <option value="">Open + Closed</option>
                <?php foreach (STATUSES as $s): ?>
                    <option value="<?= e($s) ?>" <?= $status_filter === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-secondary" type="submit">Filter</button>
            <a href="index.php" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>

    <!-- EVENT LIST -->
    <div class="card shadow-sm">
        <div class="card-header fw-semibold">Events (<?= $total ?>)</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>ID</th><th>Poster</th><th>Title</th><th>Date</th><th>Location</th><th>Category</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php if ($total > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= (int)$row['id'] ?></td>
                            <td>
                                <?php if ($row['poster']): ?>
                                    <img class="poster-img" src="<?= UPLOAD_URL . e($row['poster']) ?>" alt="Poster of <?= e($row['title']) ?>">
                                <?php else: ?>
                                    <div class="no-poster">No poster</div>
                                <?php endif; ?>
                            </td>
                            <td><?= e($row['title']) ?></td>
                            <td><?= e($row['event_date']) ?></td>
                            <td><?= e($row['location']) ?></td>
                            <td><?= e($row['category']) ?></td>
                            <td>
                                <span class="badge <?= $row['status'] === 'Open' ? 'text-bg-success' : 'text-bg-secondary' ?>">
                                    <?= e($row['status']) ?>
                                </span>
                            </td>
                            <td class="text-nowrap">
                                <a class="btn btn-sm btn-outline-primary" href="edit.php?id=<?= (int)$row['id'] ?>">Edit</a>
                                <form method="POST" action="index.php?<?= e(http_build_query(['search' => $search, 'category_filter' => $category_filter, 'status_filter' => $status_filter])) ?>" class="d-inline">
                                    <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                    <button class="btn btn-sm btn-outline-warning" name="toggle_status" type="submit">
                                        <?= $row['status'] === 'Open' ? 'Close' : 'Reopen' ?>
                                    </button>
                                </form>
                                <form method="POST" action="index.php" class="d-inline"
                                      onsubmit="return confirm('Delete this event and its poster?');">
                                    <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger" name="delete_event" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No events found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
