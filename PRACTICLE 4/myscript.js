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
   FAQ COLLAPSIBLE (ONE OPEN AT A TIME)
====================================== */
const faqButtons = document.querySelectorAll(".faq-btn");

faqButtons.forEach(function(button){
    button.addEventListener("click", function(){
        const answer = this.nextElementSibling;
        const icon = this.querySelector("span");

        // બીજો કોઈ ઓપન રહેલો જવાબ બંધ કરી દેશે
        document.querySelectorAll(".faq-answer").forEach(function(item){
            if(item !== answer){
                item.style.display = "none";
            }
        });

        // બાકી બધા બટન પર '+' કરી દેશે
        document.querySelectorAll(".faq-btn span").forEach(function(item){
            if(item !== icon && item){
                item.innerHTML = "+";
            }
        });

        // ક્લિક કરેલો આન્સર ટોગલ (ઓપન / ક્લોઝ) કરશે
        if(answer && answer.style.display === "block"){
            answer.style.display = "none";
            if(icon) icon.innerHTML = "+";
        } else if(answer) {
            answer.style.display = "block";
            if(icon) icon.innerHTML = "−";
        }
    });
});

/* ======================================
   MODAL POPUP (CONTACT SUPPORT)
====================================== */
const modal = document.getElementById("supportModal");
const openModal = document.getElementById("openModal");
const closeModal = document.getElementById("closeModal");

if(openModal && modal){
    openModal.addEventListener("click", function(){
        modal.style.display = "block";
    });
}

if(closeModal && modal){
    closeModal.addEventListener("click", function(){
        modal.style.display = "none";
    });
}

window.addEventListener("click", function(event){
    if(event.target === modal){
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

if(menuBtn && navbar){
    menuBtn.addEventListener("click", function(){
        if(navbar.style.display === "flex"){
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
    registerForm.addEventListener("submit", function (event) {
        const mobileInput = document.getElementById("mobile");
        const passwordInput = document.getElementById("password");
        const confirmInput = document.getElementById("confirm");

        const mobileValue = mobileInput ? mobileInput.value.trim() : "";
        const passwordValue = passwordInput ? passwordInput.value : "";
        const confirmValue = confirmInput ? confirmInput.value : "";

        const mobileRegex = /^[0-9]{10}$/;
        if (mobileInput && !mobileRegex.test(mobileValue)) {
            alert("Please enter a valid 10-digit mobile number");
            mobileInput.focus();
            event.preventDefault();
            return false;
        }

        if (passwordInput && confirmInput && passwordValue !== confirmValue) {
            alert("Password and Confirm Password do not match"); // Fixes Syntax Error here
            confirmInput.focus();
            event.preventDefault(); 
            return false;
        }
    });
}

