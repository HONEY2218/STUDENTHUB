
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

if (empty($_SESSION["delete_event_csrf"])) {
    $_SESSION["delete_event_csrf"] = bin2hex(random_bytes(32));
}

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = filter_input(INPUT_POST, "event_id", FILTER_VALIDATE_INT);
}

if (!$id || $id <= 0) {
    die("Invalid Event ID.");
}

// Get event details
$stmt = $conn->prepare(
    "SELECT event_id, event_name, poster FROM events WHERE event_id = ?"
);
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

    if (!hash_equals($_SESSION["delete_event_csrf"], $token)) {
        $error = "Invalid security token. Please try again.";
    } else {

        // Do not delete an event that has registrations
        $check = $conn->prepare(
            "SELECT COUNT(*) AS total FROM registrations WHERE event_id = ?"
        );
        $check->bind_param("i", $id);
        $check->execute();
        $registrationCount = (int)$check->get_result()
            ->fetch_assoc()["total"];
        $check->close();

        if ($registrationCount > 0) {
            $error = "This event has registrations and cannot be deleted. Close the event instead.";
        } else {

            $delete = $conn->prepare(
                "DELETE FROM events WHERE event_id = ?"
            );
            $delete->bind_param("i", $id);

            if ($delete->execute() && $delete->affected_rows === 1) {

                // Delete poster only after successful database deletion
                if (!empty($event["poster"])) {
                    $posterPath = __DIR__ . "/" . $event["poster"];

                    if (is_file($posterPath)) {
                        unlink($posterPath);
                    }
                }

                $delete->close();
                header("Location: admin_events.php?deleted=1");
                exit();

            } else {
                $error = "Event could not be deleted.";
                $delete->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Event - StudentHub</title>
    <link rel="stylesheet" href="style4.css">

    <style>
        .delete-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 25px;
            background: white;
            border-radius: 8px;
            text-align: center;
        }

        .confirm-btn, .cancel-btn {
            display: inline-block;
            padding: 11px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            margin: 8px;
        }

        .confirm-btn {
            background: #dc3545;
            color: white;
        }

        .cancel-btn {
            background: #6c757d;
            color: white;
        }

        .error-message {
            color: red;
            font-weight: bold;
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

<main class="delete-container">

    <h2>Delete Event</h2>

    <?php if ($error !== ""): ?>
        <p class="error-message"><?php echo clean($error); ?></p>
        <a href="admin_events.php" class="cancel-btn">Back to Events</a>
    <?php else: ?>

        <p>Are you sure you want to delete this event?</p>

        <h3><?php echo clean($event["event_name"]); ?></h3>

        <p>This action cannot be undone.</p>

        <form method="POST">
            <input type="hidden" name="event_id"
                   value="<?php echo (int)$event["event_id"]; ?>">

            <input type="hidden" name="csrf_token"
                   value="<?php echo clean($_SESSION["delete_event_csrf"]); ?>">

            <button type="submit" class="confirm-btn">
                Yes, Delete Event
            </button>

            <a href="admin_events.php" class="cancel-btn">
                Cancel
            </a>
        </form>

    <?php endif; ?>

</main>

<footer>
    <p>&copy; <?php echo date("Y"); ?> StudentHub - CHARUSAT</p>
</footer>

</body>
</html>

<?php
$conn->close();
?>
