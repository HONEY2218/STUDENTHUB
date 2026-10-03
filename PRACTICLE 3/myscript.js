/* ======================================   
   NOTIFICATION BANNER CLOSE   
====================================== */   
// [DOM SELECTION]: 
const banner = document.getElementById("banner");   
const closeBanner = document.getElementById("closeBanner");   

if (closeBanner && banner) {
    // [EVENT LISTENER]: 
    closeBanner.addEventListener("click", function () {
        banner.style.display = "none";
    });
}

/* ======================================
   IMAGE / CONTENT SLIDER
====================================== */
const slider = document.getElementById("slider");

// [ARRAY]: 
const slides = [
    "📖 Welcome to StudentHub FAQ Center",
    "🎓 Student Registration Made Easy",
    "📢 Check Latest Events & Announcements",
    "💳 View Fees & Payment Details",
    "🏆 Access Your Semester Results"
];

let current = 0;

if (slider) {
    // [TIMER / SETINTERVAL]: 
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
// [DOM SELECTION]: 
const faqButtons = document.querySelectorAll(".faq-btn");

// [ARRAY ITERATION]:
faqButtons.forEach(function(button){
    button.addEventListener("click", function(){
        // [DOM TRAVERSAL]: 
        const answer = this.nextElementSibling;
        const icon = this.querySelector("span");

  
        document.querySelectorAll(".faq-answer").forEach(function(item){   
            if(item !== answer){   
                item.style.display = "none";   
            }   
        });   

      
        document.querySelectorAll(".faq-btn span").forEach(function(item){   
            if(item !== icon && item){   
                item.innerHTML = "+";   
            }   
        });   

        // [CONDITIONAL TOGGLE]: 
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

// [GLOBAL WINDOW EVENT]: 
window.addEventListener("click", function(event){
    if(event.target === modal){
        modal.style.display = "none";
    }
});

/* ======================================
   DARK / LIGHT THEME
====================================== */
const themeBtn = document.getElementById("themeBtn");

// [LOCAL STORAGE READ]: 
if (localStorage.getItem("theme") === "dark") {
    document.body.classList.add("dark");
    if (themeBtn) {
        themeBtn.innerHTML = "☀ Light Mode";
    }
}

if (themeBtn) {
    themeBtn.addEventListener("click", function () {
        // [CLASS TOGGLE]: 
        document.body.classList.toggle("dark");

        // [LOCAL STORAGE WRITE]: 
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

// [STYLE MANIPULATION]: 
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
    // [FORM SUBMIT EVENT]: 
    registerForm.addEventListener("submit", function (event) {
        const mobileInput = document.getElementById("mobile");
        const passwordInput = document.getElementById("password");
        const confirmInput = document.getElementById("confirm");

        const mobileValue = mobileInput ? mobileInput.value.trim() : "";   
        const passwordValue = passwordInput ? passwordInput.value : "";   
        const confirmValue = confirmInput ? confirmInput.value : "";   

        // [REGULAR EXPRESSION / REGEX]: 
        const mobileRegex = /^[0-9]{10}$/;   
        if (mobileInput && !mobileRegex.test(mobileValue)) {   
            alert("Please enter a valid 10-digit mobile number");   
            mobileInput.focus();   
            // [PREVENT DEFAULT]: 
            event.preventDefault();   
            return false;   
        }   

        // [VALUE COMPARISON]: 
        if (passwordInput && confirmInput && passwordValue !== confirmValue) {   
            alert("Password and Confirm Password do not match");   
            confirmInput.focus();   
            event.preventDefault();    
            return false;   
        }   
    });
}

/* ======================================
   AUTOMATIC IMAGE CAROUSEL
====================================== */
// [EVENT]: 
document.addEventListener("DOMContentLoaded", function () {
    const carouselElement = document.querySelector('#campusCarousel');
    if (carouselElement) {
        // [BOOTSTRAP JS API]: 
        new bootstrap.Carousel(carouselElement, {
            interval: 3000, /
            ride: 'carousel'
        });
    }
});