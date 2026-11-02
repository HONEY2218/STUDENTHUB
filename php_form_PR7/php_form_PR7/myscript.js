/* =========================================================
   7. REGISTRATION FORM VALIDATION
========================================================= */

const registerForm =
    document.getElementById("registerForm");


if (registerForm) {


    /* ==========================================
       FORM SETTINGS
    ========================================== */

    registerForm.action = "process.php";

    registerForm.method = "POST";



    /* ==========================================
       GET FORM ELEMENTS
    ========================================== */

    const nameInput =
        document.getElementById("name");

    const enrollmentInput =
        document.getElementById("enrollment");

    const rollInput =
        document.getElementById("Roll-no.");

    const emailInput =
        document.getElementById("email");

    const mobileInput =
        document.getElementById("mobile");

    const courseInput =
        document.getElementById("course");

    const yearInput =
        document.getElementById("year");

    const genderInputs =
        document.getElementsByName("gender");

    const passwordInput =
        document.getElementById("password");

    const confirmInput =
        document.getElementById("confirm");

    const termsInput =
        document.getElementById("terms");

    const resetBtn =
        document.getElementById("resetBtn");

    const successMsg =
        document.getElementById("successMsg");

    const passwordStrength =
        document.getElementById("passwordStrength");



    /* ==========================================
       ERROR ELEMENTS
    ========================================== */

    const nameError =
        document.getElementById("nameError");

    const enrollmentError =
        document.getElementById("enrollmentError");

    const rollError =
        document.getElementById("rollNoError");

    const emailError =
        document.getElementById("emailError");

    const mobileError =
        document.getElementById("mobileError");

    const courseError =
        document.getElementById("courseError");

    const yearError =
        document.getElementById("yearError");

    const genderError =
        document.getElementById("genderError");

    const passwordError =
        document.getElementById("passwordError");

    const confirmError =
        document.getElementById("confirmError");

    const termsError =
        document.getElementById("termsError");



    /* ==========================================
       7.1 FULL NAME
    ========================================== */

    function validateName() {

        const value =
            nameInput.value.trim();


        if (value === "") {

            nameError.innerText = "";

            return true;

        }


        const pattern =
            /^[A-Za-z\s]+$/;


        if (!pattern.test(value)) {

            nameError.innerText =
                "Full Name must contain only alphabets.";

            return false;

        }


        nameError.innerText = "";

        return true;

    }



    /* ==========================================
       7.2 ENROLLMENT
    ========================================== */

    function validateEnrollment() {

        const value =
            enrollmentInput.value.trim();


        if (value === "") {

            enrollmentError.innerText = "";

            return true;

        }


        const pattern =
            /^[Dd]?[0-9]{2}[A-Za-z]{2}[0-9]{3}$/;


        if (!pattern.test(value)) {

            enrollmentError.innerText =
                "Invalid format! Example: 25CS063 or D25CS063.";

            return false;

        }


        enrollmentError.innerText = "";

        return true;

    }



    /* ==========================================
       7.3 ROLL NUMBER
    ========================================== */

    function validateRoll() {

        const value =
            rollInput.value.trim();


        if (value === "") {

            rollError.innerText = "";

            return true;

        }


        const pattern =
            /^[0-9]{1,3}$/;


        if (!pattern.test(value)) {

            rollError.innerText =
                "Roll Number must contain 1 to 3 digits.";

            return false;

        }


        rollError.innerText = "";

        return true;

    }



    /* ==========================================
       7.4 EMAIL
    ========================================== */

    function validateEmail() {

        const value =
            emailInput.value.trim();


        if (value === "") {

            emailError.innerText = "";

            return true;

        }


        const pattern =
            /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/;


        if (!pattern.test(value)) {

            emailError.innerText =
                "Email must contain lowercase letters only with proper format.";

            return false;

        }


        emailError.innerText = "";

        return true;

    }



    /* ==========================================
       7.5 MOBILE
    ========================================== */

    function validateMobile() {

        const value =
            mobileInput.value.trim();


        if (value === "") {

            mobileError.innerText = "";

            return true;

        }


        const pattern =
            /^[6-9][0-9]{9}$/;


        if (!pattern.test(value)) {

            mobileError.innerText =
                "Please enter a valid 10-digit Indian Mobile Number.";

            return false;

        }


        mobileError.innerText = "";

        return true;

    }



    /* ==========================================
       7.6 COURSE
    ========================================== */

    function validateCourse() {

        if (courseInput.value === "") {

            courseError.innerText =
                "Please select a Course.";

            return false;

        }


        courseError.innerText = "";

        return true;

    }



    /* ==========================================
       7.7 ACADEMIC YEAR
    ========================================== */

    function validateYear() {

        if (yearInput.value === "") {

            yearError.innerText =
                "Please select your Academic Year.";

            return false;

        }


        yearError.innerText = "";

        return true;

    }



    /* ==========================================
       7.8 GENDER
    ========================================== */

    function validateGender() {

        let selected = false;


        for (let gender of genderInputs) {

            if (gender.checked) {

                selected = true;

                break;

            }

        }


        if (!selected) {

            genderError.innerText =
                "Please select your Gender.";

            return false;

        }


        genderError.innerText = "";

        return true;

    }



    /* ==========================================
       7.9 PASSWORD
    ========================================== */

    function validatePassword() {

        const value =
            passwordInput.value;


        if (value === "") {

            passwordError.innerText = "";

            return true;

        }


        const pattern =
            /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;


        if (!pattern.test(value)) {

            passwordError.innerText =
                "Password must be min 8 chars with 1 uppercase, 1 lowercase, 1 digit, and 1 special symbol.";

            return false;

        }


        passwordError.innerText = "";

        return true;

    }



    /* ==========================================
       7.10 CONFIRM PASSWORD
    ========================================== */

    function validateConfirmPassword() {

        const password =
            passwordInput.value;

        const confirm =
            confirmInput.value;


        if (confirm === "") {

            confirmError.innerText = "";

            return true;

        }


        if (password !== confirm) {

            confirmError.innerText =
                "Password and Confirm Password do not match.";

            return false;

        }


        confirmError.innerText = "";

        return true;

    }



    /* ==========================================
       7.11 TERMS
    ========================================== */

    function validateTerms() {

        if (!termsInput.checked) {

            termsError.innerText =
                "You must agree to the Terms and Conditions.";

            return false;

        }


        termsError.innerText = "";

        return true;

    }



    /* ==========================================
       7.12 PASSWORD STRENGTH
    ========================================== */

    function checkPasswordStrength() {

        const password =
            passwordInput.value;


        if (password === "") {

            passwordStrength.innerText = "";

            passwordStrength.className =
                "password-strength";

            return;

        }


        let score = 0;


        if (password.length >= 8) {

            score++;

        }


        if (/[a-z]/.test(password)) {

            score++;

        }


        if (/[A-Z]/.test(password)) {

            score++;

        }


        if (/[0-9]/.test(password)) {

            score++;

        }


        if (/[@$!%*?&]/.test(password)) {

            score++;

        }


        if (score <= 2) {

            passwordStrength.innerText =
                "Password Strength: Weak";

            passwordStrength.className =
                "password-strength weak";

        }

        else if (score <= 4) {

            passwordStrength.innerText =
                "Password Strength: Medium";

            passwordStrength.className =
                "password-strength medium";

        }

        else {

            passwordStrength.innerText =
                "Password Strength: Strong";

            passwordStrength.className =
                "password-strength strong";

        }

    }



    /* ==========================================
       LIVE VALIDATION
    ========================================== */

    nameInput.addEventListener(
        "input",
        validateName
    );


    enrollmentInput.addEventListener(
        "input",
        validateEnrollment
    );


    rollInput.addEventListener(
        "input",
        validateRoll
    );


    emailInput.addEventListener(
        "input",
        validateEmail
    );


    mobileInput.addEventListener(
        "input",
        validateMobile
    );


    courseInput.addEventListener(
        "change",
        validateCourse
    );


    yearInput.addEventListener(
        "change",
        validateYear
    );


    for (let gender of genderInputs) {

        gender.addEventListener(
            "change",
            validateGender
        );

    }


    passwordInput.addEventListener(
        "input",
        function () {

            validatePassword();

            validateConfirmPassword();

            checkPasswordStrength();

        }
    );


    confirmInput.addEventListener(
        "input",
        validateConfirmPassword
    );


    termsInput.addEventListener(
        "change",
        validateTerms
    );



    /* ==========================================
       FORM SUBMIT
    ========================================== */

    registerForm.addEventListener(
        "submit",
        function (event) {

            let isValid = true;



            if (
                nameInput.value.trim() === ""
            ) {

                nameError.innerText =
                    "Please enter your Full Name.";

                isValid = false;

            }

            else if (!validateName()) {

                isValid = false;

            }



            if (
                enrollmentInput.value.trim() === ""
            ) {

                enrollmentError.innerText =
                    "Please enter Enrollment Number.";

                isValid = false;

            }

            else if (!validateEnrollment()) {

                isValid = false;

            }



            if (
                rollInput.value.trim() === ""
            ) {

                rollError.innerText =
                    "Please enter Roll Number.";

                isValid = false;

            }

            else if (!validateRoll()) {

                isValid = false;

            }



            if (
                emailInput.value.trim() === ""
            ) {

                emailError.innerText =
                    "Please enter your Email Address.";

                isValid = false;

            }

            else if (!validateEmail()) {

                isValid = false;

            }



            if (
                mobileInput.value.trim() === ""
            ) {

                mobileError.innerText =
                    "Please enter your Mobile Number.";

                isValid = false;

            }

            else if (!validateMobile()) {

                isValid = false;

            }



            if (!validateCourse()) {

                isValid = false;

            }



            if (!validateYear()) {

                isValid = false;

            }



            if (!validateGender()) {

                isValid = false;

            }



            if (
                passwordInput.value === ""
            ) {

                passwordError.innerText =
                    "Please enter a Password.";

                isValid = false;

            }

            else if (!validatePassword()) {

                isValid = false;

            }



            if (
                confirmInput.value === ""
            ) {

                confirmError.innerText =
                    "Please enter Confirm Password.";

                isValid = false;

            }

            else if (!validateConfirmPassword()) {

                isValid = false;

            }



            if (!validateTerms()) {

                isValid = false;

            }



            /* =================================
               INVALID → STAY ON PAGE
            ================================= */

            if (!isValid) {

                event.preventDefault();

                return;

            }


            /*
               VALID DATA:

               Do NOT use preventDefault().

               Form will normally submit to:

               process.php
            */

        }
    );



    /* ==========================================
       RESET
    ========================================== */

    if (resetBtn) {

        resetBtn.addEventListener(
            "click",
            function () {

                document
                    .querySelectorAll(".error-msg")
                    .forEach(function (element) {

                        element.innerText = "";

                    });


                if (successMsg) {

                    successMsg.innerText = "";

                }


                if (passwordStrength) {

                    passwordStrength.innerText = "";

                    passwordStrength.className =
                        "password-strength";

                }

            }
        );

    }

}