
// =====================================
// PRACTICAL 6 - SEARCH, FILTER & SORT
// =====================================

// Search events
export function searchEvents(events, keyword) {

    return events.filter(event =>
        event.title.toLowerCase().includes(keyword.toLowerCase()) ||
        event.venue.toLowerCase().includes(keyword.toLowerCase()) ||
        event.category.toLowerCase().includes(keyword.toLowerCase())
    );
}


// Filter events by category
export function filterEvents(events, category) {

    if (category === "All") {
        return events;
    }

    return events.filter(event =>
        event.category === category
    );
}


// Sort events by date
export function sortEvents(events, order) {

    return [...events].sort((a, b) => {

        if (order === "latest") {
            return new Date(b.date) - new Date(a.date);
        }

        return new Date(a.date) - new Date(b.date);

    });
}


// Pagination
export function paginateEvents(events, page, perPage) {

    const start = (page - 1) * perPage;

    return events.slice(start, start + perPage);
}
