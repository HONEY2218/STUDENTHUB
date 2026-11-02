
<?php
session_start();
// ============================================
// PRACTICAL 7 - STUDENTHUB PHP FORM PROCESSING
// ============================================

// Accept only POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: register.html");
    exit;
}

// CSRF Token Validation
if (
    !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    http_response_code(403);
    exit("Invalid CSRF Token! Form submission rejected.");
}

// Store validation errors
$errors = [];

// Get and sanitize input values
$name = trim($_POST["name"] ?? "");
$enrollment = strtoupper(trim($_POST["enrollment"] ?? ""));
$roll = trim($_POST["rollno"] ?? "");
$email = trim($_POST["email"] ?? "");
$mobile = trim($_POST["mobile"] ?? "");
$course = trim($_POST["course"] ?? "");
$year = trim($_POST["year"] ?? "");
$gender = trim($_POST["gender"] ?? "");
$password = $_POST["password"] ?? "";
$confirm = $_POST["confirm"] ?? "";
$terms = isset($_POST["terms"]);

// ============================================
// 1. FULL NAME VALIDATION
// ============================================

if ($name === "") {
    $errors[] = "Full Name is required.";
} elseif (!preg_match("/^[\p{L}\s]+$/u", $name)) {
    $errors[] = "Full Name must contain only letters and spaces.";
}

// ============================================
// 2. ENROLLMENT VALIDATION
// ============================================

if (!preg_match("/^D?[0-9]{2}[A-Z]{2}[0-9]{3}$/", $enrollment)) {
    $errors[] = "Invalid Enrollment Number. Example: 25CS063 or D25CS063.";
}

// ============================================
// 3. ROLL NUMBER VALIDATION
// ============================================

if (!preg_match("/^[0-9]{1,3}$/", $roll)) {
    $errors[] = "Roll Number must contain 1 to 3 digits.";
}

// ============================================
// 4. EMAIL VALIDATION
// ============================================

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Please enter a valid Email Address.";
}

// ============================================
// 5. MOBILE VALIDATION
// ============================================

if (!preg_match("/^[6-9][0-9]{9}$/", $mobile)) {
    $errors[] = "Please enter a valid 10-digit Indian Mobile Number.";
}

// ============================================
// 6. COURSE VALIDATION
// ============================================

$allowedCourses = ["B.Tech CSE", "B.Tech IT", "BCA", "MCA"];

if (!in_array($course, $allowedCourses, true)) {
    $errors[] = "Please select a valid Course.";
}

// ============================================
// 7. ACADEMIC YEAR VALIDATION
// ============================================

$allowedYears = ["1st Year", "2nd Year", "3rd Year", "4th Year"];

if (!in_array($year, $allowedYears, true)) {
    $errors[] = "Please select a valid Academic Year.";
}

// ============================================
// 8. GENDER VALIDATION
// ============================================

$allowedGenders = ["Male", "Female", "Other"];

if (!in_array($gender, $allowedGenders, true)) {
    $errors[] = "Please select a valid Gender.";
}

// ============================================
// 9. PASSWORD VALIDATION
// ============================================

$passwordPattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[@$!%*?&])[A-Za-z0-9@$!%*?&]{8,}$/";

if (!preg_match($passwordPattern, $password)) {
    $errors[] = "Password must have at least 8 characters, uppercase, lowercase, number and symbol.";
}

// ============================================
// 10. CONFIRM PASSWORD
// ============================================

if ($password !== $confirm) {
    $errors[] = "Password and Confirm Password do not match.";
}

// ============================================
// 11. TERMS VALIDATION
// ============================================

if (!$terms) {
    $errors[] = "You must accept Terms and Conditions.";
}

// ============================================
// 12. SAVE DATA IN CSV FILE
// ============================================

if (empty($errors)) {

    // Create data folder if it does not exist
    $dataDirectory = __DIR__ . "/data";

    if (!is_dir($dataDirectory)) {
        mkdir($dataDirectory, 0755, true);
    }

    $csvFile = $dataDirectory . "/students.csv";

    // Open CSV file
    $file = fopen($csvFile, "a+");

    if ($file === false) {

        $errors[] = "Unable to open the student data file.";

    } else {

        // Lock file while writing
        if (flock($file, LOCK_EX)) {

            // Add headings if file is empty
            $fileInfo = fstat($file);

            if ($fileInfo["size"] === 0) {

                fputcsv($file, [
                    "Date",
                    "Full Name",
                    "Enrollment",
                    "Roll Number",
                    "Email",
                    "Mobile",
                    "Course",
                    "Academic Year",
                    "Gender",
                    "Password Hash"
                ]);

            }

            // Hash password before storing
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            // Save student record
            $saved = fputcsv($file, [
                date("Y-m-d H:i:s"),
                $name,
                $enrollment,
                $roll,
                $email,
                $mobile,
                $course,
                $year,
                $gender,
                $passwordHash
            ]);

            fflush($file);
            flock($file, LOCK_UN);

            if ($saved === false) {
                $errors[] = "Unable to save student record.";
            }

        } else {

            $errors[] = "Unable to lock the data file.";

        }

        fclose($file);

    }

}

// ============================================
// 13. DISPLAY SUCCESS OR ERROR MESSAGE
// ============================================

function escapeOutput($value) {
    return htmlspecialchars($value, ENT_QUOTES, "UTF-8");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub | Registration Result</title>
    <link rel="stylesheet" href="style4.css">
</head>

<body>

    <main>

        <div class="card">

            <?php if (empty($errors)) { ?>

                <h2>Registration Successful!</h2>

                <div class="success-box">
                    Student registration data has been saved successfully.
                </div>

                <p>
                    Welcome to StudentHub,
                    <strong><?php echo escapeOutput($name); ?></strong>!
                </p>

            <?php } else { ?>

                <h2>Registration Failed</h2>

                <div class="error-box">
                    <strong>Please correct the following errors:</strong>

                    <ul>
                        <?php foreach ($errors as $error) { ?>
                            <li><?php echo escapeOutput($error); ?></li>
                        <?php } ?>
                    </ul>
                </div>

            <?php } ?>

            <p>
                <a href="register.html" class="btn">
                    Back to Registration
                </a>
            </p>

            <p>
                <a href="home.html">Go to Home Page</a>
            </p>

        </div>

    </main>

</body>
</html>
