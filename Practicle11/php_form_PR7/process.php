<?php

/* ==========================================
   PRACTICAL 7
   PHP FORM PROCESSING
   SERVER-SIDE VALIDATION
   CSV FILE STORAGE
========================================== */


/* ==========================================
   1. CHECK REQUEST METHOD
========================================== */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    die(
        "Invalid Request! Please submit the registration form."
    );

}


/* ==========================================
   2. GET FORM DATA
========================================== */

$name =
    trim($_POST["name"] ?? "");


$enrollment =
    trim($_POST["enrollment"] ?? "");


$rollNo =
    trim($_POST["roll_no"] ?? "");


$email =
    trim($_POST["email"] ?? "");


$mobile =
    trim($_POST["mobile"] ?? "");


$course =
    trim($_POST["course"] ?? "");


$year =
    trim($_POST["year"] ?? "");


$gender =
    trim($_POST["gender"] ?? "");


$password =
    $_POST["password"] ?? "";


$confirm =
    $_POST["confirm"] ?? "";



/* ==========================================
   3. SANITIZATION
========================================== */

$name =
    htmlspecialchars(
        $name,
        ENT_QUOTES,
        "UTF-8"
    );


$enrollment =
    htmlspecialchars(
        $enrollment,
        ENT_QUOTES,
        "UTF-8"
    );


$rollNo =
    htmlspecialchars(
        $rollNo,
        ENT_QUOTES,
        "UTF-8"
    );


$email =
    htmlspecialchars(
        $email,
        ENT_QUOTES,
        "UTF-8"
    );


$mobile =
    htmlspecialchars(
        $mobile,
        ENT_QUOTES,
        "UTF-8"
    );


$course =
    htmlspecialchars(
        $course,
        ENT_QUOTES,
        "UTF-8"
    );


$year =
    htmlspecialchars(
        $year,
        ENT_QUOTES,
        "UTF-8"
    );


$gender =
    htmlspecialchars(
        $gender,
        ENT_QUOTES,
        "UTF-8"
    );



/* ==========================================
   4. ERROR ARRAY
========================================== */

$errors = array();



/* ==========================================
   5. FULL NAME VALIDATION
========================================== */

if ($name === "") {

    $errors[] =
        "Full Name is required.";

}

else if (
    !preg_match(
        "/^[A-Za-z\s]+$/",
        $name
    )
) {

    $errors[] =
        "Full Name must contain only alphabets.";

}



/* ==========================================
   6. ENROLLMENT VALIDATION
========================================== */

$enrollmentPattern =
    "/^[Dd]?[0-9]{2}[A-Za-z]{2}[0-9]{3}$/";


if ($enrollment === "") {

    $errors[] =
        "Enrollment Number is required.";

}

else if (
    !preg_match(
        $enrollmentPattern,
        $enrollment
    )
) {

    $errors[] =
        "Invalid Enrollment Number. Example: 25CS063 or D25CS063.";

}



/* ==========================================
   7. ROLL NUMBER VALIDATION
========================================== */

if ($rollNo === "") {

    $errors[] =
        "Roll Number is required.";

}

else if (
    !preg_match(
        "/^[0-9]{1,3}$/",
        $rollNo
    )
) {

    $errors[] =
        "Roll Number must contain 1 to 3 digits.";

}



/* ==========================================
   8. EMAIL VALIDATION
========================================== */

if ($email === "") {

    $errors[] =
        "Email Address is required.";

}

else if (
    !filter_var(
        $email,
        FILTER_VALIDATE_EMAIL
    )
) {

    $errors[] =
        "Please enter a valid Email Address.";

}



/* ==========================================
   9. MOBILE VALIDATION
========================================== */

if ($mobile === "") {

    $errors[] =
        "Mobile Number is required.";

}

else if (
    !preg_match(
        "/^[6-9][0-9]{9}$/",
        $mobile
    )
) {

    $errors[] =
        "Please enter a valid 10-digit Indian Mobile Number.";

}



/* ==========================================
   10. COURSE
========================================== */

if ($course === "") {

    $errors[] =
        "Please select a Course.";

}



/* ==========================================
   11. ACADEMIC YEAR
========================================== */

if ($year === "") {

    $errors[] =
        "Please select your Academic Year.";

}



/* ==========================================
   12. GENDER
========================================== */

if ($gender === "") {

    $errors[] =
        "Please select your Gender.";

}



/* ==========================================
   13. PASSWORD
========================================== */

$passwordPattern =
    "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/";


if ($password === "") {

    $errors[] =
        "Password is required.";

}

else if (
    !preg_match(
        $passwordPattern,
        $password
    )
) {

    $errors[] =
        "Password must be minimum 8 characters with 1 uppercase, 1 lowercase, 1 number and 1 special symbol.";

}



/* ==========================================
   14. CONFIRM PASSWORD
========================================== */

if ($confirm === "") {

    $errors[] =
        "Confirm Password is required.";

}

else if (
    $password !== $confirm
) {

    $errors[] =
        "Password and Confirm Password do not match.";

}



/* ==========================================
   15. TERMS
========================================== */

if (!isset($_POST["terms"])) {

    $errors[] =
        "You must agree to the Terms and Conditions.";

}



/* ==========================================
   16. DISPLAY ERRORS
========================================== */

if (count($errors) > 0) {

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Registration Failed
    </title>


    <style>

        body {

            font-family: Arial, sans-serif;

            background: #f4f6f9;

            padding: 50px;

        }


        .error-box {

            background: white;

            max-width: 650px;

            margin: auto;

            padding: 35px;

            border-radius: 12px;

            box-shadow:
                0 5px 15px
                rgba(0,0,0,0.15);

        }


        h1 {

            color: #dc2626;

        }


        li {

            margin: 12px 0;

            color: #333;

        }


        a {

            display: inline-block;

            margin-top: 20px;

            padding: 12px 25px;

            background: #2098D1;

            color: white;

            text-decoration: none;

            border-radius: 6px;

        }


        a:hover {

            background: #1B7FB0;

        }

    </style>

</head>


<body>


    <div class="error-box">

        <h1>
            Registration Failed!
        </h1>


        <ul>

            <?php

            foreach ($errors as $error) {

                echo
                    "<li>" .
                    htmlspecialchars(
                        $error,
                        ENT_QUOTES,
                        "UTF-8"
                    ) .
                    "</li>";

            }

            ?>

        </ul>


        <a href="index.php">
            Go Back to Registration
        </a>

    </div>


</body>

</html>

<?php

    exit();

}



/* ==========================================
   17. PASSWORD HASHING
========================================== */

$hashedPassword =
    password_hash(
        $password,
        PASSWORD_DEFAULT
    );



/* ==========================================
   18. CSV FILE
========================================== */

$file =
    "data.csv";



/* ==========================================
   19. OPEN CSV FILE
========================================== */

$handle =
    fopen(
        $file,
        "a"
    );


if ($handle === false) {

    die(
        "Error: Unable to open data.csv file."
    );

}



/* ==========================================
   20. ADD HEADER
========================================== */

if (filesize($file) === 0) {

    fputcsv(
        $handle,
        array(

            "Name",

            "Enrollment",

            "Roll No",

            "Email",

            "Mobile",

            "Course",

            "Academic Year",

            "Gender",

            "Password"

        )
    );

}



/* ==========================================
   21. STORE DATA
========================================== */

fputcsv(
    $handle,
    array(

        $name,

        $enrollment,

        $rollNo,

        $email,

        $mobile,

        $course,

        $year,

        $gender,

        $hashedPassword

    )
);



/* ==========================================
   22. CLOSE CSV
========================================== */

fclose($handle);



/* ==========================================
   23. SUCCESS
========================================== */

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Registration Successful
    </title>


    <style>

        body {

            font-family: Arial, sans-serif;

            background: #f4f6f9;

            padding-top: 100px;

            text-align: center;

        }


        .success-box {

            background: white;

            max-width: 600px;

            margin: auto;

            padding: 40px;

            border-radius: 12px;

            box-shadow:
                0 5px 15px
                rgba(0,0,0,0.15);

        }


        h1 {

            color: #16a34a;

        }


        p {

            font-size: 18px;

        }


        a {

            display: inline-block;

            margin-top: 20px;

            padding: 12px 25px;

            background: #2098D1;

            color: white;

            text-decoration: none;

            border-radius: 6px;

        }


        a:hover {

            background: #1B7FB0;

        }

    </style>

</head>


<body>


    <div class="success-box">

        <h1>
            ✓ Registration Completed Successfully!
        </h1>


        <p>
            Your registration data has been stored successfully.
        </p>


        <a href="index.php">
            Register Another Student
        </a>

    </div>


</body>

</html>