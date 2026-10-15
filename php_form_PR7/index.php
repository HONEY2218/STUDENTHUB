<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>StudentHub | Register</title>

    <link rel="stylesheet" href="style4.css">

</head>


<body>


    <!-- ===============================
         HEADER
    ================================ -->

    <header>

        <div class="container">

            <div class="top">

                <div class="logo">

                    <img src="image.jpg"
                         alt="StudentHub Logo">

                    <div>

                        <h1>StudentHub</h1>

                        <h3>
                            Student Management Portal
                        </h3>

                    </div>

                </div>


                <button
                    class="theme-btn"
                    id="themeBtn"
                >
                    🌙 Dark Mode
                </button>

            </div>

        </div>

    </header>



    <!-- ===============================
         NAVIGATION
    ================================ -->

    <div class="nav-section">

        <div class="container">

            <nav id="navbar">

                <a href="home.html">Home</a>

                <a href="about.html">About</a>

                <a href="register.html">Register</a>

                <a href="login.html">Login</a>

                <a href="dashboard.html">Dashboard</a>

                <a href="profile.html">Profile</a>

                <a href="event.html">Events</a>

                <a href="fees.html">Fees</a>

                <a href="result.html">Result</a>

                <a href="FAQ.html">FAQ</a>

                <a href="logout.html">Logout</a>

            </nav>

        </div>

    </div>



    <!-- ===============================
         MAIN
    ================================ -->

    <main>

        <div class="card">

            <h2>
                Student Registration Form
            </h2>

            <p>
                Fill in the following details to create your StudentHub account.
            </p>



            <!-- =================================
                 PRACTICAL 7 FORM
            ================================== -->

            <form
                action="process.php"
                method="POST"
                id="registerForm"
            >


                <!-- Full Name -->

                <div class="form-field">

                    <label for="name">
                        Full Name
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        placeholder="Enter Full Name (Alphabet only)"
                    >

                    <small
                        class="error-msg"
                        id="nameError"
                    ></small>

                </div>



                <!-- Enrollment Number -->

                <div class="form-field">

                    <label for="enrollment">
                        Enrollment Number
                    </label>

                    <input
                        type="text"
                        id="enrollment"
                        name="enrollment"
                        placeholder="e.g. 25CS063 or D25CS063"
                        minlength="7"
                        maxlength="8"
                    >

                    <small
                        class="error-msg"
                        id="enrollmentError"
                    ></small>

                </div>



                <!-- Roll Number -->

                <div class="form-field">

                    <label for="Roll-no.">
                        Roll-no.
                    </label>

                    <input
                        type="text"
                        id="Roll-no."
                        name="roll_no"
                        placeholder="e.g. 63"
                    >

                    <small
                        class="error-msg"
                        id="rollNoError"
                    ></small>

                </div>



                <!-- Email -->

                <div class="form-field">

                    <label for="email">
                        Email Address
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="example@gmail.com"
                    >

                    <small
                        class="error-msg"
                        id="emailError"
                    ></small>

                </div>



                <!-- Mobile -->

                <div class="form-field">

                    <label for="mobile">
                        Mobile Number
                    </label>

                    <input
                        type="tel"
                        id="mobile"
                        name="mobile"
                        placeholder="Enter 10 digit mobile number"
                        maxlength="10"
                    >

                    <small
                        class="error-msg"
                        id="mobileError"
                    ></small>

                </div>



                <!-- Course -->

                <div class="form-field">

                    <label for="course">
                        Course
                    </label>

                    <select
                        id="course"
                        name="course"
                    >

                        <option value="">
                            Select Course
                        </option>

                        <option value="B.Tech CSE">
                            B.Tech CSE
                        </option>

                        <option value="B.Tech IT">
                            B.Tech IT
                        </option>

                        <option value="BCA">
                            BCA
                        </option>

                        <option value="MCA">
                            MCA
                        </option>

                    </select>

                    <small
                        class="error-msg"
                        id="courseError"
                    ></small>

                </div>



                <!-- Academic Year -->

                <div class="form-field">

                    <label for="year">
                        Academic Year
                    </label>

                    <select
                        id="year"
                        name="year"
                    >

                        <option value="">
                            Select Year
                        </option>

                        <option value="1st Year">
                            1st Year
                        </option>

                        <option value="2nd Year">
                            2nd Year
                        </option>

                        <option value="3rd Year">
                            3rd Year
                        </option>

                        <option value="4th Year">
                            4th Year
                        </option>

                    </select>

                    <small
                        class="error-msg"
                        id="yearError"
                    ></small>

                </div>



                <!-- Gender -->

                <div class="form-field">

                    <label style="margin-top:20px;">
                        Gender
                    </label>

                    <div class="gender">

                        <input
                            type="radio"
                            id="male"
                            name="gender"
                            value="Male"
                        >

                        <label
                            for="male"
                            style="display:inline;font-weight:normal;"
                        >
                            Male
                        </label>


                        &nbsp;&nbsp;


                        <input
                            type="radio"
                            id="female"
                            name="gender"
                            value="Female"
                        >

                        <label
                            for="female"
                            style="display:inline;font-weight:normal;"
                        >
                            Female
                        </label>


                        &nbsp;&nbsp;


                        <input
                            type="radio"
                            id="other"
                            name="gender"
                            value="Other"
                        >

                        <label
                            for="other"
                            style="display:inline;font-weight:normal;"
                        >
                            Other
                        </label>

                    </div>

                    <small
                        class="error-msg"
                        id="genderError"
                    ></small>

                </div>



                <!-- Password -->

                <div class="form-field">

                    <label for="password">
                        Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Min 8 chars, 1 Uppercase, 1 Lowercase, 1 Number, 1 Symbol"
                    >

                    <div
                        id="passwordStrength"
                        class="password-strength"
                    ></div>

                    <small
                        class="error-msg"
                        id="passwordError"
                    ></small>

                </div>



                <!-- Confirm Password -->

                <div class="form-field">

                    <label for="confirm">
                        Confirm Password
                    </label>

                    <input
                        type="password"
                        id="confirm"
                        name="confirm"
                        placeholder="Re-enter password"
                    >

                    <small
                        class="error-msg"
                        id="confirmError"
                    ></small>

                </div>



                <!-- Terms -->

                <div
                    class="form-field"
                    style="margin-top:20px;"
                >

                    <div class="terms-container">

                        <input
                            type="checkbox"
                            id="terms"
                            name="terms"
                            value="yes"
                        >

                        <label for="terms">
                            I agree to the Terms and Conditions.
                        </label>

                    </div>

                    <small
                        class="error-msg"
                        id="termsError"
                    ></small>

                </div>



                <!-- Buttons -->

                <div class="buttons">

                    <input
                        type="submit"
                        value="Register"
                    >

                    <input
                        type="reset"
                        value="Clear"
                        id="resetBtn"
                    >

                </div>



                <!-- Success message -->

                <div
                    id="successMsg"
                    class="success-display"
                ></div>


            </form>

        </div>



        <!-- Already Registered -->

        <div class="card">

            <h2>
                Already Registered?
            </h2>

            <p>

                If you already have an account?

                <a
                    href="login.html"
                    style="color:#7B5CB8;
                           font-weight:bold;
                           text-decoration:none;"
                >
                    Login Here
                </a>

            </p>

        </div>

    </main>



    <!-- ===============================
         CONTACT
    ================================ -->

    <div class="card contact-card">

        <h2>
            Contact Us
        </h2>

        <div class="contact-info">

            <h3>
                StudentHub Portal
            </h3>

            <p>
                📧 Email : studenthub@gmail.com
            </p>

            <p>
                📞 Phone : +91 9876543210
            </p>

            <p>
                📍 Address : CHARUSAT Campus, Changa, Gujarat 388421
            </p>

        </div>

    </div>



    <!-- ===============================
         FOOTER
    ================================ -->

    <footer>

        <div class="footer-content">

            <h2>
                StudentHub Portal
            </h2>

            <p>
                Empowering Students Through Technology
            </p>

            <p class="email">
                Email : studenthub@gmail.com
            </p>

            <p class="copyright">
                &copy; 2026 StudentHub. All Rights Reserved.
            </p>

        </div>

    </footer>



    <script src="myscript.js"></script>

</body>

</html>