<?php
require 'functions.php';
require 'db.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);

$stmt = mysqli_prepare($conn, "SELECT * FROM events WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$event = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$event) {
    flash('danger', 'Event not found.');
    header('Location: index.php');
    exit();
}

$errors = [];
$form = [
    'title' => $event['title'], 'event_date' => $event['event_date'], 'location' => $event['location'],
    'category' => $event['category'], 'status' => $event['status'],
];

/* ---------- UPDATE ---------- */
if (post_too_large()) {
    $errors[] = 'The upload is too large. Maximum allowed poster size is ' . MAX_POSTER_MB . ' MB.';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_event'])) {
    $form   = event_input();
    $errors = validate_event($form);

    $new_poster = null;
    if (!$errors) {
        $up = save_poster($_FILES['poster'] ?? null);
        if ($up['error']) {
            $errors[] = $up['error'];
        } else {
            $new_poster = $up['name'];                      // null = keep the old poster
        }
    }

    if (!$errors) {
        $poster_final = $new_poster ?? $event['poster'];

        $stmt = mysqli_prepare($conn,
            "UPDATE events SET title = ?, event_date = ?, location = ?, category = ?, status = ?, poster = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ssssssi",
            $form['title'], $form['event_date'], $form['location'], $form['category'], $form['status'], $poster_final, $id);

        if (mysqli_stmt_execute($stmt)) {
            if ($new_poster) {
                delete_poster($event['poster']);            // old file removed only AFTER the update succeeded
            }
            flash('success', 'Event updated successfully.');
            header('Location: index.php');
            exit();
        }
        delete_poster($new_poster);                         // update failed: remove the new unused file
        $errors[] = 'Database error: the event could not be updated.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>.poster-preview { width: 120px; height: 160px; object-fit: cover; border-radius: 4px; }</style>
</head>
<body class="bg-light">
<div class="container py-4" style="max-width: 700px;">
    <div class="card shadow-sm">
        <div class="card-header fw-semibold">Edit Event #<?= (int)$event['id'] ?></div>
        <div class="card-body">
            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="edit.php?id=<?= (int)$id ?>" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label" for="title">Title</label>
                    <input class="form-control" id="title" name="title" maxlength="150" value="<?= e($form['title']) ?>" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="event_date">Date</label>
                        <input class="form-control" type="date" id="event_date" name="event_date" value="<?= e($form['event_date']) ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="location">Location</label>
                        <input class="form-control" id="location" name="location" maxlength="150" value="<?= e($form['location']) ?>" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="category">Category</label>
                        <select class="form-select" id="category" name="category" required>
                            <?php foreach (CATEGORIES as $c): ?>
                                <option value="<?= e($c) ?>" <?= $form['category'] === $c ? 'selected' : '' ?>><?= e($c) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="status">Status</label>
                        <select class="form-select" id="status" name="status">
                            <?php foreach (STATUSES as $s): ?>
                                <option value="<?= e($s) ?>" <?= $form['status'] === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label d-block">Current poster</label>
                    <?php if ($event['poster']): ?>
                        <img class="poster-preview mb-2" src="<?= UPLOAD_URL . e($event['poster']) ?>" alt="Current poster">
                    <?php else: ?>
                        <p class="text-muted">No poster uploaded.</p>
                    <?php endif; ?>
                    <label class="form-label d-block" for="poster">Change poster (optional, JPG/PNG/WEBP, max <?= MAX_POSTER_MB ?> MB)</label>
                    <input class="form-control" type="file" id="poster" name="poster" accept=".jpg,.jpeg,.png,.webp">
                </div>
                <button class="btn btn-primary" type="submit" name="update_event">Update Event</button>
                <a class="btn btn-outline-secondary" href="index.php">Cancel</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
