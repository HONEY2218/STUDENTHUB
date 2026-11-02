<?php
// Shared helpers: session, flash messages, admin guard, validation, poster upload
session_start();

const UPLOAD_DIR     = __DIR__ . '/uploads/';   // where files are saved on disk
const UPLOAD_URL     = 'uploads/';              // how the browser reaches them
const MAX_POSTER_MB  = 2;
const ALLOWED_EXT    = ['jpg', 'jpeg', 'png', 'webp'];
const ALLOWED_MIME   = ['image/jpeg', 'image/png', 'image/webp'];
const CATEGORIES     = ['Music', 'Conference', 'Workshop', 'Sports'];
const STATUSES       = ['Open', 'Closed'];

// Safe output (prevents XSS)
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// One-time success / failure message shown after a redirect
function flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function show_flash() {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="alert alert-' . e($f['type']) . ' alert-dismissible fade show" role="alert">'
           . e($f['message'])
           . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
}

// Access restriction: only a logged-in admin may continue
function require_admin() {
    if (empty($_SESSION['admin_id'])) {
        flash('warning', 'Please log in as admin to continue.');
        header('Location: login.php');
        exit();
    }
}

// Validate the text fields of the event form. Returns a list of error messages.
function validate_event(array $d) {
    $errors = [];
    if ($d['title'] === '' || mb_strlen($d['title']) > 150) {
        $errors[] = 'Title is required (maximum 150 characters).';
    }
    $dt = DateTime::createFromFormat('Y-m-d', $d['event_date']);
    if (!$dt || $dt->format('Y-m-d') !== $d['event_date']) {
        $errors[] = 'Please enter a valid event date.';
    }
    if ($d['location'] === '' || mb_strlen($d['location']) > 150) {
        $errors[] = 'Location is required (maximum 150 characters).';
    }
    if (!in_array($d['category'], CATEGORIES, true)) {
        $errors[] = 'Please select a valid category.';
    }
    if (!in_array($d['status'], STATUSES, true)) {
        $errors[] = 'Please select a valid status.';
    }
    return $errors;
}

/*
 * Validate and save an uploaded poster.
 * Returns ['name' => saved file name or null, 'error' => message or null]
 *  - no file chosen            -> name null, error null (poster is optional)
 *  - invalid file              -> name null, error message
 *  - valid file                -> name = new unique file name (already moved to uploads/)
 */
function save_poster($file) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['name' => null, 'error' => null];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $msg = ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE)
             ? 'Poster is too large. Maximum allowed size is ' . MAX_POSTER_MB . ' MB.'
             : 'Poster upload failed (error code ' . $file['error'] . ').';
        return ['name' => null, 'error' => $msg];
    }

    // 1. File type check by extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXT, true)) {
        return ['name' => null, 'error' => 'Invalid file type. Only JPG, JPEG, PNG and WEBP are allowed.'];
    }

    // 2. File size check
    if ($file['size'] > MAX_POSTER_MB * 1024 * 1024) {
        return ['name' => null, 'error' => 'Poster is too large. Maximum allowed size is ' . MAX_POSTER_MB . ' MB.'];
    }

    // 3. Real content check (a renamed .txt or .php file is not a real image)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, ALLOWED_MIME, true) || @getimagesize($file['tmp_name']) === false) {
        return ['name' => null, 'error' => 'The file is not a valid image.'];
    }

    // 4. Rename (never trust the user's file name) and move
    $new_name = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $new_name)) {
        return ['name' => null, 'error' => 'Could not save the poster. Check that the uploads folder exists and is writable.'];
    }
    return ['name' => $new_name, 'error' => null];
}

// Delete a poster file from disk (safe if empty or missing)
function delete_poster($file_name) {
    if ($file_name) {
        $path = UPLOAD_DIR . basename($file_name);
        if (is_file($path)) {
            unlink($path);
        }
    }
}

// When a file is bigger than PHP's post_max_size, PHP empties $_POST and $_FILES completely.
function post_too_large() {
    return $_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES)
        && !empty($_SERVER['CONTENT_LENGTH']);
}

// Collect the event form fields from $_POST
function event_input() {
    return [
        'title'      => trim($_POST['title'] ?? ''),
        'event_date' => trim($_POST['event_date'] ?? ''),
        'location'   => trim($_POST['location'] ?? ''),
        'category'   => trim($_POST['category'] ?? ''),
        'status'     => trim($_POST['status'] ?? 'Open'),
    ];
}
