
<?php
include 'db.php';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=students.csv');

$output = fopen('php://output', 'w');

fputcsv($output, ['ID', 'Name', 'Email', 'Course', 'Gender', 'Status']);

$sql = "SELECT id, name, email, course, gender, status
        FROM students
        ORDER BY id ASC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    exit("Database query failed.");
}

while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, [
        $row['id'],
        $row['name'],
        $row['email'],
        $row['course'],
        $row['gender'],
        $row['status']
    ]);
}

fclose($output);
exit;
?>
