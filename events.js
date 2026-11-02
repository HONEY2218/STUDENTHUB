
/* =========================================
   PRACTICAL 6 - DYNAMIC EVENT RENDERING
========================================= */

import { fetchEvents } from "./eventData.js";

import {
    searchEvents,
    filterEvents,
    sortEvents,
    paginateEvents
} from "./eventUtils.js";


// =========================================
// 1. GET HTML ELEMENTS
// =========================================

const eventTableBody = document.getElementById("eventTableBody");

const searchInput = document.getElementById("searchInput");

const categoryFilter = document.getElementById("categoryFilter");

const sortSelect = document.getElementById("sortSelect");

const pagination = document.getElementById("pagination");

const loadingMessage = document.getElementById("loadingMessage");

const errorMessage = document.getElementById("errorMessage");

const resultCount = document.getElementById("resultCount");


// =========================================
// 2. GLOBAL VARIABLES
// =========================================

let allEvents = [];

let currentPage = 1;

const eventsPerPage = 5;


// =========================================
// 3. LOAD EVENTS FROM JSON
// =========================================

async function loadEvents() {

    try {

        loadingMessage.textContent = "Loading events...";

        errorMessage.textContent = "";

        const events = await fetchEvents();

        allEvents = events;

        loadingMessage.textContent = "";

        displayEvents();

    } catch (error) {

        loadingMessage.textContent = "";

        errorMessage.textContent =
            "Unable to load events. Please try again.";

        console.error(error);

    }

}


// =========================================
// 4. DISPLAY EVENTS
// =========================================

function displayEvents() {

    let filteredEvents = searchEvents(
        allEvents,
        searchInput.value.trim()
    );

    filteredEvents = filterEvents(
        filteredEvents,
        categoryFilter.value
    );

    filteredEvents = sortEvents(
        filteredEvents,
        sortSelect.value
    );


    // Calculate total pages

    const totalPages = Math.ceil(
        filteredEvents.length / eventsPerPage
    );


    // Keep current page within valid range

    if (totalPages === 0) {
        currentPage = 1;
    } else if (currentPage > totalPages) {
        currentPage = totalPages;
    }


    // Get current page events

    const currentEvents = paginateEvents(
        filteredEvents,
        currentPage,
        eventsPerPage
    );


    // Update result count

    resultCount.textContent =
        "Total Events Found: " + filteredEvents.length;


    // Clear old table rows

    eventTableBody.innerHTML = "";


    // Show message if no events found

    if (currentEvents.length === 0) {

        eventTableBody.innerHTML = `
            <tr>
                <td colspan="5">No Events Found</td>
            </tr>
        `;

        pagination.innerHTML = "";

        return;

    }


    // Create table rows dynamically

    currentEvents.forEach(event => {

        const row = document.createElement("tr");

        row.innerHTML = `
            <td>${event.id}</td>
            <td>${event.title}</td>
            <td>${event.date}</td>
            <td>${event.venue}</td>
            <td>${event.category}</td>
        `;

        eventTableBody.appendChild(row);

    });


    // Display pagination buttons

    displayPagination(totalPages);

}


// =========================================
// 5. PAGINATION BUTTONS
// =========================================

function displayPagination(totalPages) {

    pagination.innerHTML = "";

    if (totalPages <= 1) {
        return;
    }


    // Previous Button

    const previousButton = document.createElement("button");

    previousButton.textContent = "Previous";

    previousButton.disabled = currentPage === 1;

    previousButton.addEventListener("click", function () {

        currentPage--;

        displayEvents();

    });

    pagination.appendChild(previousButton);


    // Page Number Buttons

    for (let i = 1; i <= totalPages; i++) {

        const pageButton = document.createElement("button");

        pageButton.textContent = i;

        if (i === currentPage) {
            pageButton.classList.add("active-page");
        }

        pageButton.addEventListener("click", function () {

            currentPage = i;

            displayEvents();

        });

        pagination.appendChild(pageButton);

    }


    // Next Button

    const nextButton = document.createElement("button");

    nextButton.textContent = "Next";

    nextButton.disabled = currentPage === totalPages;

    nextButton.addEventListener("click", function () {

        currentPage++;

        displayEvents();

    });

    pagination.appendChild(nextButton);

}


// =========================================
// 6. SEARCH EVENT
// =========================================

searchInput.addEventListener("input", function () {

    currentPage = 1;

    displayEvents();

});


// =========================================
// 7. CATEGORY FILTER
// =========================================

categoryFilter.addEventListener("change", function () {

    currentPage = 1;

    displayEvents();

});


// =========================================
// 8. SORT EVENTS
// =========================================

sortSelect.addEventListener("change", function () {

    currentPage = 1;

    displayEvents();

});


// =========================================
// 9. INITIALIZE APPLICATION
// =========================================

loadEvents();
