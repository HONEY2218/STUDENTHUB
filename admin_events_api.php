
<?php
require_once "session_check.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AJAX Event Management - StudentHub</title>
    <link rel="stylesheet" href="style4.css">

    <style>
        .api-container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 20px;
        }

        .api-form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .api-form input,
        .api-form select,
        .api-form textarea {
            width: 100%;
            padding: 10px;
            margin: 7px 0 15px;
            box-sizing: border-box;
        }

        .api-form button,
        .action-btn {
            padding: 9px 14px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: white;
            margin: 3px;
        }

        .save-btn {
            background: #198754;
        }

        .edit-btn {
            background: #0d6efd;
        }

        .delete-btn {
            background: #dc3545;
        }

        .cancel-btn {
            background: #6c757d;
        }

        .api-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        .api-table th,
        .api-table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .api-table th {
            background: #e9ecef;
        }

        .message {
            padding: 12px;
            margin: 12px 0;
            border-radius: 5px;
            display: none;
        }

        .success {
            background: #d1e7dd;
            color: #0f5132;
        }

        .error {
            background: #f8d7da;
            color: #842029;
        }

        .loading {
            color: #0d6efd;
            font-weight: bold;
        }

        .table-wrapper {
            overflow-x: auto;
        }
    </style>
</head>

<body>

<header>
    <h1>StudentHub</h1>
    <p>CHARUSAT Campus, Changa, Gujarat</p>
</header>

<nav>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="admin_students.php">Students</a>
    <a href="admin_events.php">Event Management</a>
    <a href="logout.php">Logout</a>
</nav>

<main class="api-container">

    <h2>AJAX / Fetch API Event Management</h2>

    <p>
        Manage Events asynchronously without reloading the page.
    </p>

    <div id="messageBox" class="message"></div>
    <p id="loading" class="loading" style="display:none;">
        Loading events...
    </p>

    <section class="api-form">

        <h3 id="formTitle">Add New Event</h3>

        <form id="eventForm" novalidate>

            <input type="hidden" id="eventId">

            <label>Event Name</label>
            <input type="text" id="eventName" required>

            <label>Event Date</label>
            <input type="date" id="eventDate" required>

            <label>Venue</label>
            <input type="text" id="venue" required>

            <label>Category</label>
            <select id="category" required>
                <option value="">Select Category</option>
                <option value="Technical">Technical</option>
                <option value="Cultural">Cultural</option>
                <option value="Sports">Sports</option>
                <option value="Workshop">Workshop</option>
                <option value="Seminar">Seminar</option>
                <option value="Other">Other</option>
            </select>

            <label>Description</label>
            <textarea id="description" rows="4"></textarea>

            <label>Status</label>
            <select id="status" required>
                <option value="open">Open</option>
                <option value="closed">Closed</option>
            </select>

            <button type="submit" class="save-btn" id="submitBtn">
                Add Event
            </button>

            <button type="button" class="cancel-btn"
                    id="cancelBtn" style="display:none;">
                Cancel Edit
            </button>

        </form>
    </section>

    <h3>All Events</h3>

    <input
    type="text"
    id="searchInput"
    placeholder="Search events by name or venue..."
    style="width:100%; padding:12px; margin:10px 0;"
>

    <div class="table-wrapper">
        <table class="api-table">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Event Name</th>
                    <th>Date</th>
                    <th>Venue</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody id="eventTableBody">
                <tr>
                    <td colspan="7">Loading...</td>
                </tr>
            </tbody>

        </table>
    </div>

</main>

<footer>
    <p>&copy; <?php echo date("Y"); ?> StudentHub - CHARUSAT</p>
</footer>

<script>
const API_URL = "api/events.php";

let csrfToken = "";
let events = [];

const form = document.getElementById("eventForm");
const messageBox = document.getElementById("messageBox");
const loading = document.getElementById("loading");
const tableBody = document.getElementById("eventTableBody");

function showMessage(message, type = "success") {
    messageBox.textContent = message;
    messageBox.className = "message " + type;
    messageBox.style.display = "block";
}

function setLoading(value) {
    loading.style.display = value ? "block" : "none";
}


async function apiRequest(method, body = null, search = "") {

    let url = API_URL;

    if (method === "GET" && search !== "") {
        url += "?search=" + encodeURIComponent(search);
    }

    const options = {
        method: method,
        headers: {
            "Accept": "application/json"
        }
    };

    if (body !== null) {
        options.headers["Content-Type"] = "application/json";
        options.headers["X-CSRF-Token"] = csrfToken;
        options.body = JSON.stringify(body);
    }

    const response = await fetch(url, options);
    const result = await response.json();

    if (!response.ok || !result.success) {
        throw new Error(result.message || "Request failed.");
    }

    return result;
}


  

// GET: Load all events
async function loadEvents() {
    setLoading(true);

    try {
        const searchText = document.getElementById("searchInput").value;
const result = await apiRequest("GET", null, searchText);

        events = result.data.events;
        csrfToken = result.data.csrf_token;

        displayEvents();

    } catch (error) {
        showMessage(error.message, "error");
        tableBody.innerHTML =
            '<tr><td colspan="7">Unable to load events.</td></tr>';
    } finally {
        setLoading(false);
    }
}

// Display events in table
function displayEvents() {

    tableBody.innerHTML = "";

    if (events.length === 0) {
        tableBody.innerHTML =
            '<tr><td colspan="7">No events found.</td></tr>';
        return;
    }

    events.forEach(event => {

        const row = document.createElement("tr");

        const values = [
            event.event_id,
            event.event_name,
            event.event_date,
            event.venue,
            event.category,
            event.status
        ];

        values.forEach(value => {
            const cell = document.createElement("td");
            cell.textContent = value ?? "";
            row.appendChild(cell);
        });

        const actionCell = document.createElement("td");

        const editButton = document.createElement("button");
        editButton.textContent = "Edit";
        editButton.className = "action-btn edit-btn";
        editButton.addEventListener("click", () => editEvent(event.event_id));

        const deleteButton = document.createElement("button");
        deleteButton.textContent = "Delete";
        deleteButton.className = "action-btn delete-btn";
        deleteButton.addEventListener("click", () => deleteEvent(event.event_id));

        actionCell.append(editButton, deleteButton);
        row.appendChild(actionCell);

        tableBody.appendChild(row);
    });
}

// Clear form
function resetForm() {
    form.reset();

    document.getElementById("eventId").value = "";
    document.getElementById("formTitle").textContent = "Add New Event";
    document.getElementById("submitBtn").textContent = "Add Event";
    document.getElementById("cancelBtn").style.display = "none";
}

// POST: Add or PUT: Update
form.addEventListener("submit", async function(e) {

    e.preventDefault();

    const id = document.getElementById("eventId").value;

    const data = {
        event_name: document.getElementById("eventName").value.trim(),
        event_date: document.getElementById("eventDate").value,
        venue: document.getElementById("venue").value.trim(),
        category: document.getElementById("category").value,
        description: document.getElementById("description").value.trim(),
        status: document.getElementById("status").value
    };

    if (
    !data.event_name ||
    !data.event_date ||
    !data.venue ||
    !data.category ||
    !data.status
) {
    showMessage("Please fill all required fields.", "error");

    messageBox.scrollIntoView({
        behavior: "smooth",
        block: "center"
    });

    return;
}

    try {
        setLoading(true);

        if (id) {
            data.event_id = Number(id);

            await apiRequest("PUT", data);

            showMessage("Event updated successfully!");
        } else {
            await apiRequest("POST", data);

            showMessage("Event added successfully!");
        }

        resetForm();
        await loadEvents();

    } catch (error) {
        showMessage(error.message, "error");
    } finally {
        setLoading(false);
    }
});

// Load event details into form for editing
function editEvent(id) {

    const event = events.find(item => Number(item.event_id) === Number(id));

    if (!event) {
        showMessage("Event not found.", "error");
        return;
    }

    document.getElementById("eventId").value = event.event_id;
    document.getElementById("eventName").value = event.event_name;
    document.getElementById("eventDate").value = event.event_date;
    document.getElementById("venue").value = event.venue;
    document.getElementById("category").value = event.category;
    document.getElementById("description").value = event.description || "";
    document.getElementById("status").value = event.status;

    document.getElementById("formTitle").textContent = "Edit Event";
    document.getElementById("submitBtn").textContent = "Update Event";
    document.getElementById("cancelBtn").style.display = "inline-block";

    document.getElementById("formTitle").scrollIntoView({
        behavior: "smooth"
    });
}

// Cancel editing
document.getElementById("cancelBtn").addEventListener("click", resetForm);

// DELETE: Remove event
async function deleteEvent(id) {

    const confirmed = confirm(
        "Are you sure you want to delete this event?"
    );

    if (!confirmed) {
        return;
    }

    try {
        setLoading(true);

        await apiRequest("DELETE", {
            event_id: Number(id)
        });

        showMessage("Event deleted successfully!");

        await loadEvents();

    } catch (error) {
        showMessage(error.message, "error");
    } finally {
        setLoading(false);
    }
}

// Debounce Search
let searchTimer;

document.getElementById("searchInput").addEventListener("input", function() {

    clearTimeout(searchTimer);

    searchTimer = setTimeout(function() {
        loadEvents();
    }, 500);

});
// Initial loading
loadEvents();
</script>

</body>
</html>
