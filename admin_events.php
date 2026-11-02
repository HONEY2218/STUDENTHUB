
<?php
require_once "session_check.php";
require_once "mysqli_db.php";

// Only Admin can access this page
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

// Escape output
function clean($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

// Search and filter values
$search = trim($_GET["search"] ?? "");
$status = $_GET["status"] ?? "";
$category = $_GET["category"] ?? "";

// Pagination
$limit = 10;
$page = max(1, (int)($_GET["page"] ?? 1));
$offset = ($page - 1) * $limit;

// Count matching events
$countSql = "
    SELECT COUNT(*) AS total
    FROM events
    WHERE (? = '' OR event_name LIKE ? OR venue LIKE ?)
    AND (? = '' OR status = ?)
    AND (? = '' OR category = ?)
";

$countStmt = $conn->prepare($countSql);

$searchLike = "%" . $search . "%";

$countStmt->bind_param(
    "sssssss",
    $search,
    $searchLike,
    $searchLike,
    $status,
    $status,
    $category,
    $category
);

$countStmt->execute();
$totalEvents = $countStmt->get_result()->fetch_assoc()["total"];
$countStmt->close();

$totalPages = max(1, (int)ceil($totalEvents / $limit));

// Fetch events
$sql = "
    SELECT event_id, event_name, event_date, venue,
           category, description, poster, status
    FROM events
    WHERE (? = '' OR event_name LIKE ? OR venue LIKE ?)
    AND (? = '' OR status = ?)
    AND (? = '' OR category = ?)
    ORDER BY event_date DESC
    LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "sssssssii",
    $search,
    $searchLike,
    $searchLike,
    $status,
    $status,
    $category,
    $category,
    $limit,
    $offset
);

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management - StudentHub</title>

    <link rel="stylesheet" href="style4.css">

    <style>
        .event-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }

        .event-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }

        .add-btn, .action-btn {
            display: inline-block;
            padding: 9px 14px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            margin: 3px;
        }

        .add-btn {
            background: #198754;
        }

        .edit-btn {
            background: #0d6efd;
        }

        .delete-btn {
            background: #dc3545;
        }

        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 20px 0;
        }

        .filter-form input,
        .filter-form select,
        .filter-form button {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .filter-form button {
            background: #0d6efd;
            color: white;
            cursor: pointer;
        }

        .event-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            margin-top: 15px;
        }

        .event-table th,
        .event-table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .event-table th {
            background: #e9ecef;
        }

        .poster-img {
            width: 90px;
            height: 65px;
            object-fit: cover;
            border-radius: 5px;
        }

        .status-open {
            color: green;
            font-weight: bold;
        }

        .status-closed {
            color: red;
            font-weight: bold;
        }

        .pagination {
            margin: 20px 0;
        }

        .pagination a {
            display: inline-block;
            padding: 8px 12px;
            margin: 3px;
            background: #e9ecef;
            text-decoration: none;
            border-radius: 4px;
        }

        .pagination a.active {
            background: #0d6efd;
            color: white;
        }

        .table-wrapper {
            overflow-x: auto;
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

<main class="event-container">

    <div class="event-heading">
        <h2>Event Management</h2>

        <a href="add_event.php" class="add-btn">
            + Add New Event
        </a>
    </div>

    <p>
        Total Events: <strong><?php echo $totalEvents; ?></strong>
    </p>

    <!-- Search and Filter Form -->
    <form method="GET" class="filter-form">

        <input
            type="text"
            name="search"
            placeholder="Search event or venue"
            value="<?php echo clean($search); ?>"
        >

        <select name="category">
            <option value="">All Categories</option>

            <?php
            $categories = [
                "Technical",
                "Cultural",
                "Sports",
                "Workshop",
                "Seminar",
                "Other"
            ];

            foreach ($categories as $item) {
                $selected = ($category === $item) ? "selected" : "";
                echo '<option value="' . clean($item) . '" ' .
                     $selected . '>' . clean($item) . '</option>';
            }
            ?>
        </select>

        <select name="status">
            <option value="">All Status</option>
            <option value="open" <?php echo $status === "open" ? "selected" : ""; ?>>
                Open
            </option>
            <option value="closed" <?php echo $status === "closed" ? "selected" : ""; ?>>
                Closed
            </option>
        </select>

        <button type="submit">Search / Filter</button>

        <a href="admin_events.php">Reset</a>

    </form>

    <!-- Events Table -->
    <div class="table-wrapper">

        <table class="event-table">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Event Name</th>
                    <th>Date</th>
                    <th>Venue</th>
                    <th>Category</th>
                    <th>Poster</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

            <?php if ($result->num_rows > 0): ?>

                <?php while ($event = $result->fetch_assoc()): ?>

                    <tr>
                        <td><?php echo (int)$event["event_id"]; ?></td>

                        <td><?php echo clean($event["event_name"]); ?></td>

                        <td><?php echo clean($event["event_date"]); ?></td>

                        <td><?php echo clean($event["venue"]); ?></td>

                        <td><?php echo clean($event["category"]); ?></td>

                        <td>
                            <?php if (!empty($event["poster"])): ?>
                                <img
                                    src="<?php echo clean($event["poster"]); ?>"
                                    class="poster-img"
                                    alt="Event Poster"
                                >
                            <?php else: ?>
                                No Poster
                            <?php endif; ?>
                        </td>

                        <td>
                            <span class="<?php echo $event["status"] === "open" ? "status-open" : "status-closed"; ?>">
                                <?php echo ucfirst(clean($event["status"])); ?>
                            </span>
                        </td>

                        <td>
                            <a
                                href="edit_event.php?id=<?php echo (int)$event["event_id"]; ?>"
                                class="action-btn edit-btn"
                            >
                                Edit
                            </a>

                            <a
                                href="delete_event.php?id=<?php echo (int)$event["event_id"]; ?>"
                                class="action-btn delete-btn"
                            >
                                Delete
                            </a>
                        </td>
                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>
                    <td colspan="8">No events found.</td>
                </tr>

            <?php endif; ?>

            </tbody>
        </table>

    </div>

    <!-- Pagination -->
    <div class="pagination">

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>

            <a
                href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&category=<?php echo urlencode($category); ?>"
                class="<?php echo $page === $i ? "active" : ""; ?>"
            >
                <?php echo $i; ?>
            </a>

        <?php endfor; ?>

    </div>

</main>

<footer>
    <p>&copy; <?php echo date("Y"); ?> StudentHub - CHARUSAT</p>
</footer>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
