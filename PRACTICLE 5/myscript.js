/* ======================================
   NOTIFICATION BANNER CLOSE
====================================== */

const banner = document.getElementById("banner");
const closeBanner = document.getElementById("closeBanner");

if (closeBanner && banner) {
    closeBanner.addEventListener("click", function () {
        banner.style.display = "none";
    });
}


/* ======================================
   IMAGE / CONTENT SLIDER
====================================== */

const slider = document.getElementById("slider");

const slides = [
    "📖 Welcome to StudentHub FAQ Center",
    "🎓 Student Registration Made Easy",
    "📢 Check Latest Events & Announcements",
    "💳 View Fees & Payment Details",
    "🏆 Access Your Semester Results"
];

let current = 0;

if (slider) {
    setInterval(function () {
        current++;

        if (current >= slides.length) {
            current = 0;
        }

        slider.innerHTML = slides[current];

    }, 3000);
}


/* ======================================
   FAQ COLLAPSIBLE
====================================== */

const faqButtons = document.querySelectorAll(".faq-btn");

faqButtons.forEach(function (button) {

    button.addEventListener("click", function () {

        const answer = this.nextElementSibling;
        const icon = this.querySelector("span");

        document.querySelectorAll(".faq-answer").forEach(function (item) {

            if (item !== answer) {
                item.style.display = "none";
            }

        });

        document.querySelectorAll(".faq-btn span").forEach(function (item) {

            if (item !== icon && item) {
                item.innerHTML = "+";
            }

        });

        if (answer && answer.style.display === "block") {

            answer.style.display = "none";

            if (icon) {
                icon.innerHTML = "+";
            }

        } else if (answer) {

            answer.style.display = "block";

            if (icon) {
                icon.innerHTML = "−";
            }

        }

    });

});


/* ======================================
   MODAL POPUP
====================================== */

const modal = document.getElementById("supportModal");
const openModal = document.getElementById("openModal");
const closeModal = document.getElementById("closeModal");

if (openModal && modal) {

    openModal.addEventListener("click", function () {
        modal.style.display = "block";
    });

}

if (closeModal && modal) {

    closeModal.addEventListener("click", function () {
        modal.style.display = "none";
    });

}

window.addEventListener("click", function (event) {

    if (event.target === modal) {
        modal.style.display = "none";
    }

});


/* ======================================
   DARK / LIGHT THEME
====================================== */

const themeBtn = document.getElementById("themeBtn");

if (localStorage.getItem("theme") === "dark") {

    document.body.classList.add("dark");

    if (themeBtn) {
        themeBtn.innerHTML = "☀ Light Mode";
    }

}

if (themeBtn) {

    themeBtn.addEventListener("click", function () {

        document.body.classList.toggle("dark");

        if (document.body.classList.contains("dark")) {

            themeBtn.innerHTML = "☀ Light Mode";

            localStorage.setItem("theme", "dark");

        } else {

            themeBtn.innerHTML = "🌙 Dark Mode";

            localStorage.setItem("theme", "light");

        }

    });

}


/* ======================================
   HAMBURGER MENU
====================================== */

const menuBtn = document.getElementById("menuBtn");
const navbar = document.getElementById("navbar");

if (menuBtn && navbar) {

    menuBtn.addEventListener("click", function () {

        if (navbar.style.display === "flex") {

            navbar.style.display = "none";

        } else {

            navbar.style.display = "flex";
            navbar.style.flexDirection = "column";
            navbar.style.alignItems = "center";

        }

    });

}


/* ======================================
   REGISTER FORM VALIDATION
====================================== */

const registerForm = document.getElementById("registerForm");

if (registerForm) {

    const resetBtn = document.getElementById("resetBtn");

    /* ---------- RESET ---------- */

    if (resetBtn) {

        resetBtn.addEventListener("click", function () {

            document.querySelectorAll(".error-msg").forEach(function (el) {
                el.innerText = "";
            });

            const successMsg = document.getElementById("successMsg");

            if (successMsg) {
                successMsg.innerText = "";
            }

            const passwordStrength =
                document.getElementById("passwordStrength");

            if (passwordStrength) {
                passwordStrength.innerText = "";
                passwordStrength.className = "password-strength";
            }

        });

    }


    /* ---------- PASSWORD STRENGTH ---------- */

    const passwordInput = document.getElementById("password");
    const passwordStrength =
        document.getElementById("passwordStrength");

    if (passwordInput && passwordStrength) {

        passwordInput.addEventListener("input", function () {

            const password = passwordInput.value;

            if (password === "") {

                passwordStrength.innerText = "";
                passwordStrength.className = "password-strength";

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

        });

    }


    /* ---------- SUBMIT ---------- */

    registerForm.addEventListener("submit", function (event) {

        event.preventDefault();

        let isValid = true;


        /* Clear old errors */

        document.querySelectorAll(".error-msg").forEach(function (el) {
            el.innerText = "";
        });

        const successMsg = document.getElementById("successMsg");

        if (successMsg) {
            successMsg.innerText = "";
        }


        /* ---------- ELEMENTS ---------- */

        const nameInput = document.getElementById("name");
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


        /* ---------- VALUES ---------- */

        const nameVal =
            nameInput ? nameInput.value.trim() : "";

        const enrollmentVal =
            enrollmentInput ? enrollmentInput.value.trim() : "";

        const rollVal =
            rollInput ? rollInput.value.trim() : "";

        const emailVal =
            emailInput ? emailInput.value.trim() : "";

        const mobileVal =
            mobileInput ? mobileInput.value.trim() : "";

        const courseVal =
            courseInput ? courseInput.value : "";

        const yearVal =
            yearInput ? yearInput.value : "";

        const passwordVal =
            passwordInput ? passwordInput.value : "";

        const confirmVal =
            confirmInput ? confirmInput.value : "";


        /* ======================================
           1. FULL NAME
        ====================================== */

        const nameRegex = /^[A-Za-z\s]+$/;

        if (nameInput) {

            if (nameVal === "") {

                document.getElementById("nameError").innerText =
                    "Please enter your Full Name.";

                isValid = false;

            }

            else if (!nameRegex.test(nameVal)) {

                document.getElementById("nameError").innerText =
                    "Full Name must contain only alphabets.";

                isValid = false;

            }

        }


        /* ======================================
           2. ENROLLMENT NUMBER
        ====================================== */

        const enrollmentError =
            document.getElementById("enrollmentError");

        const enrollmentPattern =
            /^[Dd]?[0-9]{2}[a-zA-Z]{2}[0-9]{3}$/;

        if (enrollmentInput) {

            if (enrollmentVal === "") {

                enrollmentError.innerText =
                    "Please enter Enrollment Number.";

                isValid = false;

            }

            else if (!enrollmentPattern.test(enrollmentVal)) {

                enrollmentError.innerText =
                    "Invalid format! Example: 25CS063 or D25CS063.";

                isValid = false;

            }

        }


        /* ======================================
           3. ROLL NUMBER
        ====================================== */

        if (rollInput) {

            const rollError =
                document.getElementById("Roll-no.");

            const rollPattern = /^[0-9]{1,3}$/;

            if (rollVal === "") {

                rollError.innerText =
                    "Please enter Roll Number.";

                isValid = false;

            }

            else if (!rollPattern.test(rollVal)) {

                rollError.innerText =
                    "Roll Number must contain 1 to 3 digits.";

                isValid = false;

            }

        }


        /* ======================================
           4. EMAIL
        ====================================== */

        const emailRegex =
            /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/;

        if (emailInput) {

            if (emailVal === "") {

                document.getElementById("emailError").innerText =
                    "Please enter your Email Address.";

                isValid = false;

            }

            else if (!emailRegex.test(emailVal)) {

                document.getElementById("emailError").innerText =
                    "Email must contain lowercase letters only with proper format.";

                isValid = false;

            }

        }


        /* ======================================
           5. MOBILE
        ====================================== */

        const mobileRegex = /^[6-9][0-9]{9}$/;

        if (mobileInput) {

            if (mobileVal === "") {

                document.getElementById("mobileError").innerText =
                    "Please enter your Mobile Number.";

                isValid = false;

            }

            else if (!mobileRegex.test(mobileVal)) {

                document.getElementById("mobileError").innerText =
                    "Please enter a valid 10-digit Indian Mobile Number.";

                isValid = false;

            }

        }


        /* ======================================
           6. COURSE
        ====================================== */

        if (courseInput) {

            if (courseVal === "") {

                document.getElementById("courseError").innerText =
                    "Please select a Course.";

                isValid = false;

            }

        }


        /* ======================================
           7. ACADEMIC YEAR
        ====================================== */

        if (yearInput) {

            if (yearVal === "") {

                document.getElementById("yearError").innerText =
                    "Please select your Academic Year.";

                isValid = false;

            }

        }


        /* ======================================
           8. GENDER
        ====================================== */

        if (genderInputs.length > 0) {

            let genderSelected = false;

            for (let g of genderInputs) {

                if (g.checked) {

                    genderSelected = true;
                    break;

                }

            }

            if (!genderSelected) {

                document.getElementById("genderError").innerText =
                    "Please select your Gender.";

                isValid = false;

            }

        }


        /* ======================================
           9. PASSWORD
        ====================================== */

        const passwordRegex =
            /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

        if (passwordInput) {

            if (passwordVal === "") {

                document.getElementById("passwordError").innerText =
                    "Please enter a Password.";

                isValid = false;

            }

            else if (!passwordRegex.test(passwordVal)) {

                document.getElementById("passwordError").innerText =
                    "Password must be min 8 chars with 1 uppercase, 1 lowercase, 1 digit, and 1 special symbol.";

                isValid = false;

            }

        }


        /* ======================================
           10. CONFIRM PASSWORD
        ====================================== */

        if (confirmInput) {

            if (confirmVal === "") {

                document.getElementById("confirmError").innerText =
                    "Password and Confirm Password do not match.";

                isValid = false;

            }

            else if (passwordVal !== confirmVal) {

                document.getElementById("confirmError").innerText =
                    "Password and Confirm Password do not match.";

                isValid = false;

            }

        }


        /* ======================================
           11. TERMS
        ====================================== */

        if (termsInput) {

            if (!termsInput.checked) {

                document.getElementById("termsError").innerText =
                    "You must agree to the Terms and Conditions.";

                isValid = false;

            }

        }


        /* ======================================
           SUCCESS
        ====================================== */

        if (isValid) {

            if (successMsg) {

                successMsg.innerText =
                    "✓ Registration Completed Successfully!";

            }

            registerForm.reset();

            if (passwordStrength) {

                passwordStrength.innerText = "";

                passwordStrength.className =
                    "password-strength";

            }

        }

    });

}