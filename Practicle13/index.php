
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Practical 13 - Event CRUD</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 25px;
            background: #f4f6f9;
            color: #222;
        }
        h1 { color: #174a7e; }
        .panel {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 6px #ddd;
        }
        input, button {
            padding: 10px;
            margin: 5px 0;
            box-sizing: border-box;
        }
        input { width: 100%; }
        button {
            cursor: pointer;
            border: 0;
            border-radius: 4px;
            background: #1769aa;
            color: white;
        }
        .danger { background: #c62828; }
        .edit { background: #e5a000; color: black; }
        .secondary { background: #666; }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th { background: #174a7e; color: white; }
        #message { padding: 12px; margin: 10px 0; }
        .success { background: #d9f5df; }
        .error { background: #ffe0e0; }
        .hidden { display: none; }
        .actions button { margin-right: 5px; }
    </style>
    <script src="script.js" defer></script>
</head>
<body>
    <h1>Practical 13: Event Management</h1>
    <p>PHP JSON API and Fetch-Based CRUD Without Page Reload</p>

    <div id="message" role="status" aria-live="polite" class="hidden"></div>
    <p id="loading" class="hidden">Loading, please wait...</p>

    <section class="panel">
        <h2 id="formTitle">Add Event</h2>

        <form id="eventForm">
            <input type="hidden" id="eventId">

            <label for="title">Event Title</label>
            <input id="title" maxlength="100" required>

            <label for="category">Category</label>
            <input id="category" maxlength="50" required>

            <label for="eventDate">Event Date</label>
            <input type="date" id="eventDate" required>

            <label for="venue">Venue</label>
            <input id="venue" maxlength="100" required>

            <button type="submit" id="saveButton">Add Event</button>
            <button type="button" id="cancelButton" class="secondary hidden">
                Cancel Edit
            </button>
        </form>
    </section>

    <section class="panel">
        <h2>Event List</h2>
        <button type="button" id="refreshButton">Refresh List</button>

        <div style="overflow-x:auto">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Date</th>
                        <th>Venue</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="eventsBody">
                    <tr><td colspan="6">Events will appear here.</td></tr>
                </tbody>
            </table>
        </div>
    </section>
</body>
</html>
