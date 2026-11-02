
<?php

$csvFile = __DIR__ . "/data/students.csv";

$students = [];

if (file_exists($csvFile)) {

    $file = fopen($csvFile, "r");

    if ($file !== false) {

        // Read CSV header
        $header = fgetcsv($file);

        // Read student records
        while (($row = fgetcsv($file)) !== false) {

            if (count($row) === count($header)) {
                $students[] = array_combine($header, $row);
            }

        }

        fclose($file);
    }
}

function clean($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub | Registered Students</title>
    <link rel="stylesheet" href="style4.css">
</head>

<body>

<main>
    <div class="card">

        <h2>Registered Students</h2>

        <p>Total Registered Students: <?php echo count($students); ?></p>

        <div class="table-container">

            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Full Name</th>
                        <th>Enrollment</th>
                        <th>Roll Number</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Course</th>
                        <th>Academic Year</th>
                        <th>Gender</th>
                    </tr>
                </thead>

                <tbody>

                <?php foreach ($students as $student) { ?>

                    <tr>
                        <td><?php echo clean($student["Date"]); ?></td>
                        <td><?php echo clean($student["Full Name"]); ?></td>
                        <td><?php echo clean($student["Enrollment"]); ?></td>
                        <td><?php echo clean($student["Roll Number"]); ?></td>
                        <td><?php echo clean($student["Email"]); ?></td>
                        <td><?php echo clean($student["Mobile"]); ?></td>
                        <td><?php echo clean($student["Course"]); ?></td>
                        <td><?php echo clean($student["Academic Year"]); ?></td>
                        <td><?php echo clean($student["Gender"]); ?></td>
                    </tr>

                <?php } ?>

                </tbody>
            </table>

        </div>

        <p>
            <a href="register.html">Back to Registration</a>
        </p>

    </div>
</main>

</body>
</html>
