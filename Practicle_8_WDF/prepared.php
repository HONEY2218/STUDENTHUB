<?php

require_once "db.php";

$name = "New Student";
$enrollment = "25CS998";
$email = "newstudent@gmail.com";
$mobile = "9876543211";

$sql = "INSERT INTO students
        (name, enrollment_no, email, mobile)
        VALUES
        (:name, :enrollment, :email, :mobile)";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ":name" => $name,
    ":enrollment" => $enrollment,
    ":email" => $email,
    ":mobile" => $mobile
]);

echo "Student inserted successfully using Prepared Statement.";

?>