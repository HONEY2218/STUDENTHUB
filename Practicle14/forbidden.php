
<?php
http_response_code(403);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 Forbidden</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="alert alert-danger shadow">
        <h1>403 - Forbidden</h1>
        <p>You do not have permission to access this page.</p>
        <a href="dashboard.php" class="btn btn-secondary">
            Back to Dashboard
        </a>
        <a href="logout.php" class="btn btn-danger">
            Logout
        </a>
    </div>
</div>
</body>
</html>
