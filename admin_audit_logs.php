
<?php

require_once "session_check.php";
require_once "mysqli_db.php";
require_once "role_redirect.php";

// Only Admin can access Audit Logs
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    if (isset($_SESSION["role"])) {
        redirectByRole($_SESSION["role"]);
    }

    header("Location: login.php");
    exit;
}

// Search and filter values
$search = trim($_GET["search"] ?? "");
$statusFilter = trim($_GET["status"] ?? "");

// Fetch Audit Logs
$sql = "SELECT log_id, email, username, action,
               status, ip_address, created_at
        FROM audit_logs
        WHERE 1=1";

$types = "";
$params = [];

if ($search !== "") {

    $sql .= " AND (
        email LIKE ?
        OR username LIKE ?
        OR action LIKE ?
    )";

    $searchValue = "%" . $search . "%";

    $types .= "sss";

    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
}

if ($statusFilter !== "") {

    $sql .= " AND status = ?";

    $types .= "s";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY created_at DESC, log_id DESC";

$stmt = $conn->prepare($sql);

if ($types !== "") {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();

$logs = $stmt->get_result();

$totalLogs = $logs->num_rows;

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>StudentHub | Audit Logs</title>

    <link rel="stylesheet" href="style4.css">

    <style>

        .audit-table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        .audit-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 850px;
        }

        .audit-table th {
            background-color: #203b70;
            color: white;
            padding: 14px;
            text-align: center;
        }

        .audit-table td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }

        .audit-table tr:nth-child(even) {
            background-color: #f2f5fa;
        }

        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            margin: 20px 0;
        }

        .filter-form input,
        .filter-form select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .filter-form button,
        .reset-button {
            padding: 10px 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        .reset-button {
            background: #555;
            color: white;
        }

        .success-status {
            color: green;
            font-weight: bold;
        }

        .failure-status {
            color: red;
            font-weight: bold;
        }

        .total-count {
            font-size: 18px;
            font-weight: bold;
            margin: 15px 0;
        }

    </style>

</head>

<body>

<header>

    <div class="container">

        <h1>StudentHub</h1>

        <h3>Admin Audit Logs</h3>

    </div>

</header>

<div class="nav-section">

    <div class="container">

        <?php require_once "dynamic_menu.php"; ?>

    </div>

</div>

<main>

    <div class="card">

        <h2>Audit Logs Management</h2>

        <p>
            Monitor user activities and system events.
        </p>

        <form method="GET" class="filter-form">

            <input
                type="text"
                name="search"
                placeholder="Search Email, Username or Action"
                value="<?php echo htmlspecialchars($search); ?>"
            >

            <select name="status">

                <option value="">All Status</option>

                <option value="SUCCESS"
                    <?php echo $statusFilter === "SUCCESS" ? "selected" : ""; ?>>
                    SUCCESS
                </option>

                <option value="FAILURE"
                    <?php echo $statusFilter === "FAILURE" ? "selected" : ""; ?>>
                    FAILURE
                </option>

            </select>

            <button type="submit">Search / Filter</button>

            <a href="admin_audit_logs.php" class="reset-button">
                Reset
            </a>

        </form>

        <div class="total-count">

            Total Audit Records: <?php echo $totalLogs; ?>

        </div>

        <div class="audit-table-wrapper">

            <table class="audit-table">

                <thead>

                    <tr>

                        <th>Log ID</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th>Action</th>
                        <th>Status</th>
                        <th>IP Address</th>
                        <th>Date & Time</th>

                    </tr>

                </thead>

                <tbody>

                    <?php if ($totalLogs > 0): ?>

                        <?php while ($log = $logs->fetch_assoc()): ?>

                            <tr>

                                <td>
                                    <?php echo (int)$log["log_id"]; ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($log["email"] ?? "N/A"); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($log["username"] ?? "N/A"); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($log["action"]); ?>
                                </td>

                                <td>

                                    <?php if ($log["status"] === "SUCCESS"): ?>

                                        <span class="success-status">
                                            SUCCESS
                                        </span>

                                    <?php elseif ($log["status"] === "FAILURE"): ?>

                                        <span class="failure-status">
                                            FAILURE
                                        </span>

                                    <?php else: ?>

                                        <?php echo htmlspecialchars($log["status"]); ?>

                                    <?php endif; ?>

                                </td>

                                <td>
                                    <?php echo htmlspecialchars($log["ip_address"] ?? "N/A"); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($log["created_at"]); ?>
                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="7">
                                No Audit Records Found.
                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</main>

<footer>

    <div class="footer-content">

        <h2>StudentHub Portal</h2>

        <p>Empowering Students Through Technology</p>

        <p class="copyright">
            &copy; 2026 StudentHub. All Rights Reserved.
        </p>

    </div>

</footer>

</body>

</html>

<?php
$stmt->close();
?>
