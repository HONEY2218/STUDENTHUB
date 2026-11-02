
<?php
require_once "session_check.php";
require_once "mysqli_db.php";

// Admin access only
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: student_dashboard.php?error=" .
           urlencode("Unauthorized access."));
    exit;
}

function clean($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$message = "";
$messageType = "";

$uploadDir = __DIR__ . "/uploads/events/";
$maxSize = 2 * 1024 * 1024;

// Ensure upload directory exists
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// CSRF token
if (empty($_SESSION["event_csrf_token"])) {
    $_SESSION["event_csrf_token"] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $eventName = trim($_POST["event_name"] ?? "");
    $eventDate = trim($_POST["event_date"] ?? "");
    $venue = trim($_POST["venue"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $status = trim($_POST["status"] ?? "");
    $csrfToken = $_POST["csrf_token"] ?? "";

    $posterPath = null;
    $uploadedFile = null;

    if (!hash_equals($_SESSION["event_csrf_token"], $csrfToken)) {

        $message = "Invalid security token. Please try again.";
        $messageType = "error";

    } elseif (
        $eventName === "" || $eventDate === "" ||
        $venue === "" || $category === "" ||
        !in_array($status, ["open", "closed"], true)
    ) {

        $message = "Please fill in all required fields.";
        $messageType = "error";

    } elseif (
        !DateTime::createFromFormat("Y-m-d", $eventDate) ||
        DateTime::createFromFormat("Y-m-d", $eventDate)->format("Y-m-d") !== $eventDate
    ) {

        $message = "Please enter a valid event date.";
        $messageType = "error";

    } else {

        // Validate uploaded poster
        if (isset($_FILES["poster"]) &&
            $_FILES["poster"]["error"] !== UPLOAD_ERR_NO_FILE) {

            $file = $_FILES["poster"];

            if ($file["error"] !== UPLOAD_ERR_OK) {

                $message = "Poster upload failed. Please try again.";
                $messageType = "error";

            } elseif ($file["size"] > $maxSize) {

                $message = "Poster size must not exceed 2 MB.";
                $messageType = "error";

            } else {

                $allowedMimeTypes = [
                    "image/jpeg" => "jpg",
                    "image/png" => "png"
                ];

                $fileInfo = new finfo(FILEINFO_MIME_TYPE);
                $mimeType = $fileInfo->file($file["tmp_name"]);

                if (!isset($allowedMimeTypes[$mimeType])) {

                    $message = "Only JPG, JPEG and PNG images are allowed.";
                    $messageType = "error";

                } else {

                    $extension = $allowedMimeTypes[$mimeType];

                    $newFileName = bin2hex(random_bytes(16))
                                   . "." . $extension;

                    $targetPath = $uploadDir . $newFileName;

                    if (move_uploaded_file($file["tmp_name"], $targetPath)) {

                        $posterPath = "uploads/events/" . $newFileName;
                        $uploadedFile = $targetPath;

                    } else {

                        $message = "Unable to save poster image.";
                        $messageType = "error";
                    }
                }
            }
        }

        // Insert event only when validation succeeds
        if ($messageType !== "error") {

            $stmt = $conn->prepare(
                "INSERT INTO events
                (event_name, event_date, venue, category,
                 description, poster, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "sssssss",
                $eventName,
                $eventDate,
                $venue,
                $category,
                $description,
                $posterPath,
                $status
            );

            if ($stmt->execute()) {

                $message = "Event added successfully!";
                $messageType = "success";

                $_SESSION["event_csrf_token"] =
                    bin2hex(random_bytes(32));

            } else {

                if ($uploadedFile && file_exists($uploadedFile)) {
                    unlink($uploadedFile);
                }

                $message = "Unable to add event. Please try again.";
                $messageType = "error";
            }

            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub | Add Event</title>
    <link rel="stylesheet" href="style4.css">

    <style>
        .event-container {
            max-width: 700px;
            margin: 35px auto;
            padding: 25px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.10);
        }

        .event-container h2 {
            color: #1b3260;
            text-align: center;
        }

        .form-field {
            margin-bottom: 16px;
        }

        .form-field label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        .form-field input,
        .form-field select,
        .form-field textarea {
            width: 100%;
            padding: 11px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }

        .event-button {
            background: #2196c7;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .message {
            padding: 13px;
            margin-bottom: 18px;
            border-radius: 6px;
        }

        .success {
            background: #dff5e5;
            color: #155724;
        }

        .error {
            background: #fde7e7;
            color: #721c24;
        }
    </style>
</head>

<body>

<header>
    <h1>StudentHub Portal</h1>
    <nav>
        <a href="home.html">Home</a>
        <a href="admin_dashboard.php">Admin Dashboard</a>
        <a href="admin_events.php">Manage Events</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main>

    <div class="event-container">

        <h2>Add New Event</h2>

        <p>Enter event details and upload an optional poster.</p>

        <?php if ($message !== ""): ?>
            <div class="message <?= clean($messageType) ?>">
                <?= clean($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST"
              action="add_event.php"
              enctype="multipart/form-data">

            <input type="hidden" name="csrf_token"
                   value="<?= clean($_SESSION["event_csrf_token"]) ?>">

            <div class="form-field">
                <label>Event Name</label>
                <input type="text" name="event_name"
                       maxlength="150" required>
            </div>

            <div class="form-field">
                <label>Event Date</label>
                <input type="date" name="event_date" required>
            </div>

            <div class="form-field">
                <label>Venue</label>
                <input type="text" name="venue"
                       maxlength="150" required>
            </div>

            <div class="form-field">
                <label>Category</label>
                <select name="category" required>
                    <option value="">Select Category</option>
                    <option value="Technical">Technical</option>
                    <option value="Cultural">Cultural</option>
                    <option value="Sports">Sports</option>
                    <option value="Workshop">Workshop</option>
                    <option value="Seminar">Seminar</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div class="form-field">
                <label>Description</label>
                <textarea name="description" rows="4"></textarea>
            </div>

            <div class="form-field">
                <label>Event Poster (JPG / JPEG / PNG, Maximum 2 MB)</label>
                <input type="file" name="poster"
                       accept=".jpg,.jpeg,.png,image/jpeg,image/png">
            </div>

            <div class="form-field">
                <label>Event Status</label>
                <select name="status" required>
                    <option value="open">Open</option>
                    <option value="closed">Closed</option>
                </select>
            </div>

            <button type="submit" class="event-button">
                Add Event
            </button>

            <button type="reset" class="event-button">
                Clear
            </button>

        </form>

        <p>
            <a href="admin_dashboard.php">Back to Admin Dashboard</a>
        </p>

    </div>

</main>

<footer>
    <h2>StudentHub Portal</h2>
    <p>CHARUSAT Campus, Changa, Gujarat 388421</p>
</footer>

</body>
</html>

<?php
$conn->close();
?>
