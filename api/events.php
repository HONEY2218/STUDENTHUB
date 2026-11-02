
<?php
header("Content-Type: application/json; charset=UTF-8");

require_once "../session_check.php";
require_once "../mysqli_db.php";

// Send JSON response
function sendResponse($success, $message, $data = null, $code = 200) {
    http_response_code($code);

    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ]);

    exit();
}

// Admin access only
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    sendResponse(false, "Access denied. Admin only.", null, 403);
}

// Generate CSRF token
if (empty($_SESSION["api_csrf_token"])) {
    $_SESSION["api_csrf_token"] = bin2hex(random_bytes(32));
}

$method = $_SERVER["REQUEST_METHOD"];

// GET: Fetch events

// GET: Fetch events
if ($method === "GET") {

    $id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

    // Fetch one event by ID
    if ($id) {
        $stmt = $conn->prepare(
            "SELECT event_id, event_name, event_date, venue,
                    category, description, poster, status
             FROM events WHERE event_id = ?"
        );

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $event = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$event) {
            sendResponse(false, "Event not found.", null, 404);
        }

        sendResponse(true, "Event fetched successfully.", [
            "event" => $event,
            "csrf_token" => $_SESSION["api_csrf_token"]
        ]);
    }

    // Search events by name or venue
    $search = trim($_GET["search"] ?? "");
    $searchLike = "%" . $search . "%";

    $stmt = $conn->prepare(
        "SELECT event_id, event_name, event_date, venue,
                category, description, poster, status
         FROM events
         WHERE event_name LIKE ? OR venue LIKE ?
         ORDER BY event_date DESC"
    );

    $stmt->bind_param("ss", $searchLike, $searchLike);
    $stmt->execute();

    $result = $stmt->get_result();
    $events = [];

    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }

    $stmt->close();

    sendResponse(true, "Events fetched successfully.", [
        "events" => $events,
        "csrf_token" => $_SESSION["api_csrf_token"]
    ]);
}


// Only POST, PUT and DELETE are supported for changes
if (!in_array($method, ["POST", "PUT", "DELETE"], true)) {
    sendResponse(false, "Method not allowed.", null, 405);
}

// Read JSON request body
$input = json_decode(file_get_contents("php://input"), true);

if (!is_array($input)) {
    sendResponse(false, "Invalid JSON request.", null, 400);
}

// Validate CSRF token
$token = $_SERVER["HTTP_X_CSRF_TOKEN"] ?? "";

if (
    !hash_equals($_SESSION["api_csrf_token"], $token)
) {
    sendResponse(false, "Invalid CSRF token.", null, 403);
}

// POST: Add new event
if ($method === "POST") {

    $event_name = trim($input["event_name"] ?? "");
    $event_date = trim($input["event_date"] ?? "");
    $venue = trim($input["venue"] ?? "");
    $category = trim($input["category"] ?? "");
    $description = trim($input["description"] ?? "");
    $status = $input["status"] ?? "open";

    $allowedCategories = [
        "Technical", "Cultural", "Sports",
        "Workshop", "Seminar", "Other"
    ];

    $dateCheck = DateTime::createFromFormat("Y-m-d", $event_date);

    if (
        $event_name === "" ||
        $event_date === "" ||
        !$dateCheck ||
        $dateCheck->format("Y-m-d") !== $event_date ||
        $venue === "" ||
        !in_array($category, $allowedCategories, true) ||
        !in_array($status, ["open", "closed"], true)
    ) {
        sendResponse(false, "Please provide valid event details.", null, 422);
    }

    $stmt = $conn->prepare(
        "INSERT INTO events
         (event_name, event_date, venue, category, description, status)
         VALUES (?, ?, ?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "ssssss",
        $event_name,
        $event_date,
        $venue,
        $category,
        $description,
        $status
    );

    if ($stmt->execute()) {
        $newId = $conn->insert_id;
        $stmt->close();

        sendResponse(true, "Event added successfully.", [
            "event_id" => $newId
        ], 201);
    }

    $stmt->close();
    sendResponse(false, "Unable to add event.", null, 500);
}

// PUT: Update existing event
if ($method === "PUT") {

    $id = filter_var(
        $input["event_id"] ?? null,
        FILTER_VALIDATE_INT
    );

    $event_name = trim($input["event_name"] ?? "");
    $event_date = trim($input["event_date"] ?? "");
    $venue = trim($input["venue"] ?? "");
    $category = trim($input["category"] ?? "");
    $description = trim($input["description"] ?? "");
    $status = $input["status"] ?? "";

    $allowedCategories = [
        "Technical", "Cultural", "Sports",
        "Workshop", "Seminar", "Other"
    ];

    $dateCheck = DateTime::createFromFormat("Y-m-d", $event_date);

    if (
        !$id || $id <= 0 ||
        $event_name === "" ||
        $event_date === "" ||
        !$dateCheck ||
        $dateCheck->format("Y-m-d") !== $event_date ||
        $venue === "" ||
        !in_array($category, $allowedCategories, true) ||
        !in_array($status, ["open", "closed"], true)
    ) {
        sendResponse(false, "Please provide valid event details.", null, 422);
    }

    $stmt = $conn->prepare(
        "UPDATE events
         SET event_name = ?, event_date = ?, venue = ?,
             category = ?, description = ?, status = ?
         WHERE event_id = ?"
    );

    $stmt->bind_param(
        "ssssssi",
        $event_name,
        $event_date,
        $venue,
        $category,
        $description,
        $status,
        $id
    );

    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    // Check whether the event exists
    $check = $conn->prepare(
        "SELECT event_id FROM events WHERE event_id = ?"
    );
    $check->bind_param("i", $id);
    $check->execute();
    $exists = $check->get_result()->num_rows > 0;
    $check->close();

    if (!$exists) {
        sendResponse(false, "Event not found.", null, 404);
    }

    sendResponse(true, "Event updated successfully.", [
        "event_id" => $id,
        "changed" => $affected > 0
    ]);
}

// DELETE: Delete an event
if ($method === "DELETE") {

    $id = filter_var(
        $input["event_id"] ?? null,
        FILTER_VALIDATE_INT
    );

    if (!$id || $id <= 0) {
        sendResponse(false, "Valid event ID is required.", null, 422);
    }

    $check = $conn->prepare(
        "SELECT poster FROM events WHERE event_id = ?"
    );
    $check->bind_param("i", $id);
    $check->execute();
    $event = $check->get_result()->fetch_assoc();
    $check->close();

    if (!$event) {
        sendResponse(false, "Event not found.", null, 404);
    }

    // Prevent deletion if registrations exist
    $registrationCheck = $conn->prepare(
        "SELECT COUNT(*) AS total
         FROM registrations WHERE event_id = ?"
    );

    $registrationCheck->bind_param("i", $id);
    $registrationCheck->execute();

    $registrationCount = (int)$registrationCheck
        ->get_result()->fetch_assoc()["total"];

    $registrationCheck->close();

    if ($registrationCount > 0) {
        sendResponse(
            false,
            "Event has registrations and cannot be deleted.",
            null,
            409
        );
    }

    $delete = $conn->prepare(
        "DELETE FROM events WHERE event_id = ?"
    );
    $delete->bind_param("i", $id);

    if ($delete->execute() && $delete->affected_rows === 1) {

        if (!empty($event["poster"])) {
            $posterPath = __DIR__ . "/../" . $event["poster"];

            if (is_file($posterPath)) {
                unlink($posterPath);
            }
        }

        $delete->close();

        sendResponse(true, "Event deleted successfully.", [
            "event_id" => $id
        ]);
    }

    $delete->close();
    sendResponse(false, "Unable to delete event.", null, 500);
}
