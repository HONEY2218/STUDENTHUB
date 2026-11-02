
// =====================================
// PRACTICAL 6 - FETCH EVENT DATA
// =====================================

export async function fetchEvents() {

    try {

        // Fetch JSON file
        const response = await fetch("events.json");

        // Check response
        if (!response.ok) {
            throw new Error("Unable to load events data.");
        }

        // Convert JSON into JavaScript object
        const data = await response.json();

        // Return events array
        return data.events;

    } catch (error) {

        console.error("Fetch Error:", error);

        throw error;
    }
}
