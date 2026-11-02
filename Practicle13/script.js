
const API_URL = 'api.php';

const form = document.getElementById('eventForm');
const messageBox = document.getElementById('message');
const loadingBox = document.getElementById('loading');
const eventsBody = document.getElementById('eventsBody');
const eventId = document.getElementById('eventId');
const titleInput = document.getElementById('title');
const categoryInput = document.getElementById('category');
const dateInput = document.getElementById('eventDate');
const venueInput = document.getElementById('venue');
const saveButton = document.getElementById('saveButton');
const cancelButton = document.getElementById('cancelButton');
const formTitle = document.getElementById('formTitle');

function showMessage(text, type = 'success') {
    messageBox.textContent = text;
    messageBox.className = type;
}

function setLoading(isLoading) {
    loadingBox.classList.toggle('hidden', !isLoading);
    saveButton.disabled = isLoading;
    document.getElementById('refreshButton').disabled = isLoading;
}

async function apiRequest(action, payload = null) {
    const options = {
        headers: {}
    };

    let url = `${API_URL}?action=${encodeURIComponent(action)}`;

    if (payload !== null) {
        options.method = 'POST';
        options.headers['Content-Type'] = 'application/json';
        options.body = JSON.stringify(payload);
    }

    const response = await fetch(url, options);
    const result = await response.json();

    if (!response.ok || result.status !== 'success') {
        throw new Error(result.message || `HTTP error ${response.status}`);
    }

    return result;
}

async function loadEvents() {
    setLoading(true);

    try {
        const result = await apiRequest('list');
        eventsBody.replaceChildren();

        if (!result.data || result.data.length === 0) {
            const row = document.createElement('tr');
            const cell = document.createElement('td');
            cell.colSpan = 6;
            cell.textContent = 'No events found.';
            row.appendChild(cell);
            eventsBody.appendChild(row);
        } else {
            result.data.forEach(event => {
                const row = document.createElement('tr');

                [
                    event.id,
                    event.title,
                    event.category,
                    event.event_date,
                    event.venue
                ].forEach(value => {
                    const cell = document.createElement('td');
                    cell.textContent = value;
                    row.appendChild(cell);
                });

                const actions = document.createElement('td');

                const editButton = document.createElement('button');
                editButton.textContent = 'Edit';
                editButton.className = 'edit';
                editButton.type = 'button';
                editButton.addEventListener('click', () => startEdit(event));

                const deleteButton = document.createElement('button');
                deleteButton.textContent = 'Delete';
                deleteButton.className = 'danger';
                deleteButton.type = 'button';
                deleteButton.addEventListener('click', () => deleteEvent(event.id));

                actions.append(editButton, deleteButton);
                row.appendChild(actions);
                eventsBody.appendChild(row);
            });
        }

        showMessage('Event list loaded successfully.');
    } catch (error) {
        showMessage(`Unable to load events: ${error.message}`, 'error');
        eventsBody.replaceChildren();

        const row = document.createElement('tr');
        const cell = document.createElement('td');
        cell.colSpan = 6;
        cell.textContent = 'Unable to load data. Please try again.';
        row.appendChild(cell);
        eventsBody.appendChild(row);
    } finally {
        setLoading(false);
    }
}

function startEdit(event) {
    eventId.value = event.id;
    titleInput.value = event.title;
    categoryInput.value = event.category;
    dateInput.value = event.event_date;
    venueInput.value = event.venue;

    formTitle.textContent = 'Edit Event';
    saveButton.textContent = 'Update Event';
    cancelButton.classList.remove('hidden');
    titleInput.focus();
}

function resetForm() {
    form.reset();
    eventId.value = '';
    formTitle.textContent = 'Add Event';
    saveButton.textContent = 'Add Event';
    cancelButton.classList.add('hidden');
}

form.addEventListener('submit', async event => {
    event.preventDefault();

    const payload = {
        title: titleInput.value.trim(),
        category: categoryInput.value.trim(),
        event_date: dateInput.value,
        venue: venueInput.value.trim()
    };

    const action = eventId.value ? 'update' : 'create';

    if (action === 'update') {
        payload.id = Number(eventId.value);
    }

    setLoading(true);

    try {
        const result = await apiRequest(action, payload);
        showMessage(result.message || 'Operation completed successfully.');
        resetForm();
        await loadEvents();
    } catch (error) {
        showMessage(`Operation failed: ${error.message}`, 'error');
    } finally {
        setLoading(false);
    }
});

async function deleteEvent(id) {
    if (!confirm('Are you sure you want to delete this event?')) {
        return;
    }

    setLoading(true);

    try {
        const result = await apiRequest('delete', { id: Number(id) });
        showMessage(result.message);
        await loadEvents();
    } catch (error) {
        showMessage(`Delete failed: ${error.message}`, 'error');
    } finally {
        setLoading(false);
    }
}

cancelButton.addEventListener('click', resetForm);
document.getElementById('refreshButton').addEventListener('click', loadEvents);

loadEvents();
