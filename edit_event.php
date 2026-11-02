
<?php
require_once "session_check.php";
require_once "mysqli_db.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

function clean($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

if (empty($_SESSION["event_csrf_token"])) {
    $_SESSION["event_csrf_token"] = bin2hex(random_bytes(32));
}

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = filter_input(INPUT_POST, "event_id", FILTER_VALIDATE_INT);
}

if (!$id || $id <= 0) {
    die("Invalid Event ID.");
}

// Fetch existing event
$stmt = $conn->prepare("SELECT * FROM events WHERE event_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$event) {
    die("Event not found.");
}

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $token = $_POST["csrf_token"] ?? "";

    if (!hash_equals($_SESSION["event_csrf_token"], $token)) {
        $error = "Invalid security token. Please try again.";
    } else {

        $event_name = trim($_POST["event_name"] ?? "");
        $event_date = trim($_POST["event_date"] ?? "");
        $venue = trim($_POST["venue"] ?? "");
        $category = trim($_POST["category"] ?? "");
        $description = trim($_POST["description"] ?? "");
        $status = $_POST["status"] ?? "";

        $allowedCategories = [
            "Technical", "Cultural", "Sports",
            "Workshop", "Seminar", "Other"
        ];

        $allowedStatuses = ["open", "closed"];

        if (
            $event_name === "" ||
            $event_date === "" ||
            $venue === "" ||
            !in_array($category, $allowedCategories, true) ||
            !in_array($status, $allowedStatuses, true)
        ) {
            $error = "Please fill all required fields correctly.";
        } else {

            $dateCheck = DateTime::createFromFormat("Y-m-d", $event_date);

            if (!$dateCheck || $dateCheck->format("Y-m-d") !== $event_date) {
                $error = "Invalid event date.";
            }
        }

        $newPoster = $event["poster"];

        // Poster upload validation
        if ($error === "" && isset($_FILES["poster"]) &&
            $_FILES["poster"]["error"] !== UPLOAD_ERR_NO_FILE) {

            if ($_FILES["poster"]["error"] !== UPLOAD_ERR_OK) {
                $error = "Poster upload failed.";
            } elseif ($_FILES["poster"]["size"] > 2 * 1024 * 1024) {
                $error = "Poster must be less than or equal to 2 MB.";
            } else {

                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($_FILES["poster"]["tmp_name"]);

                $allowedTypes = [
                    "image/jpeg" => "jpg",
                    "image/png" => "png"
                ];

                if (!isset($allowedTypes[$mime])) {
                    $error = "Only JPG and PNG images are allowed.";
                } else {

                    $uploadDir = __DIR__ . "/uploads/events/";

                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    $fileName = bin2hex(random_bytes(16)) .
                                "." . $allowedTypes[$mime];

                    $destination = $uploadDir . $fileName;

                    if (move_uploaded_file(
                        $_FILES["poster"]["tmp_name"],
                        $destination
                    )) {
                        $newPoster = "uploads/events/" . $fileName;
                    } else {
                        $error = "Unable to save uploaded poster.";
                    }
                }
            }
        }

        if ($error === "") {

            $update = $conn->prepare("
                UPDATE events
                SET event_name = ?,
                    event_date = ?,
                    venue = ?,
                    category = ?,
                    description = ?,
                    poster = ?,
                    status = ?
                WHERE event_id = ?
            ");

            $update->bind_param(
                "sssssssi",
                $event_name,
                $event_date,
                $venue,
                $category,
                $description,
                $newPoster,
                $status,
                $id
            );

            if ($update->execute()) {

                // Delete old poster only after successful update
                if (
                    $newPoster !== $event["poster"] &&
                    !empty($event["poster"])
                ) {
                    $oldFile = __DIR__ . "/" . $event["poster"];

                    if (is_file($oldFile)) {
                        unlink($oldFile);
                    }
                }

                $update->close();
                header("Location: admin_events.php?updated=1");
                exit();

            } else {
                $error = "Event update failed: " . $update->error;

                // Remove newly uploaded file if DB update failed
                if (
                    $newPoster !== $event["poster"] &&
                    is_file(__DIR__ . "/" . $newPoster)
                ) {
                    unlink(__DIR__ . "/" . $newPoster);
                }

                $update->close();
            }
        }
    }

    // Keep submitted values in the form if validation fails
    $event["event_name"] = $event_name ?? $event["event_name"];
    $event["event_date"] = $event_date ?? $event["event_date"];
    $event["venue"] = $venue ?? $event["venue"];
    $event["category"] = $category ?? $event["category"];
    $event["description"] = $description ?? $event["description"];
    $event["status"] = $status ?? $event["status"];
    $event["poster"] = $newPoster;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event - StudentHub</title>
    <link rel="stylesheet" href="style4.css">
    <style>
        .event-form-container {
            max-width: 700px;
            margin: 30px auto;
            padding: 25px;
            background: white;
            border-radius: 8px;
        }

        .event-form-container input,
        .event-form-container select,
        .event-form-container textarea {
            width: 100%;
            padding: 10px;
            margin: 8px 0 18px;
            box-sizing: border-box;
        }

        .event-form-container button {
            padding: 11px 20px;
            background: #198754;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .poster-preview {
            width: 150px;
            display: block;
            margin: 10px 0;
        }

        .success-message {
            color: green;
        }

        .error-message {
            color: red;
        }
    </style>
</head>
<body>

<header>
    <h1>StudentHub</h1>
    <p>CHARUSAT Campus, Changa, Gujarat</p>
</header>

<nav>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="admin_students.php">Students</a>
    <a href="admin_events.php">Event Management</a>
    <a href="logout.php">Logout</a>
</nav>

<main class="event-form-container">

    <h2>Edit Event</h2>

    <?php if ($error !== ""): ?>
        <p class="error-message"><?php echo clean($error); ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <input type="hidden" name="event_id"
               value="<?php echo (int)$event["event_id"]; ?>">

        <input type="hidden" name="csrf_token"
               value="<?php echo clean($_SESSION["event_csrf_token"]); ?>">

        <label>Event Name</label>
        <input type="text" name="event_name" required
               value="<?php echo clean($event["event_name"]); ?>">

        <label>Event Date</label>
        <input type="date" name="event_date" required
               value="<?php echo clean($event["event_date"]); ?>">

        <label>Venue</label>
        <input type="text" name="venue" required
               value="<?php echo clean($event["venue"]); ?>">

        <label>Category</label>
        <select name="category" required>
            <?php
            foreach (
                ["Technical", "Cultural", "Sports",
                 "Workshop", "Seminar", "Other"] as $item
            ):
            ?>
                <option value="<?php echo clean($item); ?>"
                    <?php echo $event["category"] === $item ? "selected" : ""; ?>>
                    <?php echo clean($item); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Description</label>
        <textarea name="description" rows="5"><?php
            echo clean($event["description"]);
        ?></textarea>

        <label>Current Poster</label>

        <?php if (!empty($event["poster"])): ?>
            <img src="<?php echo clean($event["poster"]); ?>"
                 class="poster-preview" alt="Current Poster">
        <?php else: ?>
            <p>No poster uploaded.</p>
        <?php endif; ?>

        <label>Upload New Poster (Optional, JPG/PNG, Max 2 MB)</label>
        <input type="file" name="poster" accept=".jpg,.jpeg,.png">

        <label>Event Status</label>
        <select name="status" required>
            <option value="open"
                <?php echo $event["status"] === "open" ? "selected" : ""; ?>>
                Open
            </option>
            <option value="closed"
                <?php echo $event["status"] === "closed" ? "selected" : ""; ?>>
                Closed
            </option>
        </select>

        <button type="submit">Update Event</button>

        <a href="admin_events.php">Cancel</a>

    </form>

</main>

<footer>
    <p>&copy; <?php echo date("Y"); ?> StudentHub - CHARUSAT</p>
</footer>

</body>
</html>

<?php
$conn->close();
?>
