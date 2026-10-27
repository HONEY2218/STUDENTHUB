<?php
require_once "db.php";

$sql = "SELECT * FROM students";

$stmt = $pdo->prepare($sql);
$stmt->execute();

$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Database Connection & Student Data</h2>";

foreach ($students as $student) {
    echo "ID: " . $student["student_id"] . "<br>";
    echo "Name: " . $student["name"] . "<br>";
    echo "Email: " . $student["email"] . "<br>";
    echo "Course: " . $student["course"] . "<br>";
    echo "<hr>";
}
?>