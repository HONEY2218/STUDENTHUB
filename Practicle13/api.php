
<?php
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store');

require_once 'db.php';

function respond($status, $message = '', $data = null, $code = 200) {
    http_response_code($code);
    $result = ['status' => $status];

    if ($message !== '') {
        $result['message'] = $message;
    }

    if ($data !== null) {
        $result['data'] = $data;
    }

    echo json_encode($result);
    exit;
}

function readInput() {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        respond('error', 'Invalid JSON request.', null, 400);
    }

    return $data;
}

function validId($id) {
    return filter_var($id, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1]
    ]) !== false;
}

function validDate($date) {
    if (!is_string($date)) {
        return false;
    }

    $d = DateTime::createFromFormat('!Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            $stmt = $pdo->query(
                'SELECT id, title, category, event_date, venue
                 FROM events ORDER BY id DESC'
            );
            respond('success', 'Events loaded.', $stmt->fetchAll());

        case 'create':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                respond('error', 'POST required.', null, 405);
            }

            $input = readInput();
            $title = trim($input['title'] ?? '');
            $category = trim($input['category'] ?? '');
            $date = $input['event_date'] ?? '';
            $venue = trim($input['venue'] ?? '');

            if ($title === '' || $category === '' ||
                $venue === '' || !validDate($date)) {
                respond('error', 'Enter all valid event details.', null, 422);
            }

            if (mb_strlen($title) > 100 ||
                mb_strlen($category) > 50 ||
                mb_strlen($venue) > 100) {
                respond('error', 'One or more fields are too long.', null, 422);
            }

            $stmt = $pdo->prepare(
                'INSERT INTO events (title, category, event_date, venue)
                 VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$title, $category, $date, $venue]);

            respond('success', 'Event added successfully.', [
                'id' => (int)$pdo->lastInsertId()
            ], 201);

        case 'update':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                respond('error', 'POST required.', null, 405);
            }

            $input = readInput();
            $id = $input['id'] ?? null;
            $title = trim($input['title'] ?? '');
            $category = trim($input['category'] ?? '');
            $date = $input['event_date'] ?? '';
            $venue = trim($input['venue'] ?? '');

            if (!validId($id) || $title === '' || $category === '' ||
                $venue === '' || !validDate($date)) {
                respond('error', 'Enter all valid event details.', null, 422);
            }

            if (mb_strlen($title) > 100 ||
                mb_strlen($category) > 50 ||
                mb_strlen($venue) > 100) {
                respond('error', 'One or more fields are too long.', null, 422);
            }

            $stmt = $pdo->prepare(
                'UPDATE events SET title=?, category=?, event_date=?, venue=?
                 WHERE id=?'
            );
            $stmt->execute([$title, $category, $date, $venue, $id]);

            if ($stmt->rowCount() === 0) {
                $check = $pdo->prepare('SELECT id FROM events WHERE id=?');
                $check->execute([$id]);

                if (!$check->fetch()) {
                    respond('error', 'Event not found.', null, 404);
                }
            }

            respond('success', 'Event updated successfully.');

        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                respond('error', 'POST required.', null, 405);
            }

            $input = readInput();
            $id = $input['id'] ?? null;

            if (!validId($id)) {
                respond('error', 'Valid event ID required.', null, 422);
            }

            $stmt = $pdo->prepare('DELETE FROM events WHERE id=?');
            $stmt->execute([$id]);

            if ($stmt->rowCount() === 0) {
                respond('error', 'Event not found.', null, 404);
            }

            respond('success', 'Event deleted successfully.');

        default:
            respond('error', 'Invalid API action.', null, 400);
    }
} catch (Throwable $e) {
    respond('error', 'Server error while processing request.', null, 500);
}
?>
