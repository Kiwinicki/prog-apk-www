function getTheDate() {
    const today = new Date();
    // Use `getFullYear()` for correct year and formatting with `padStart()`
    const formattedDate = `${(today.getMonth() + 1).toString().padStart(2, '0')} / ${today.getDate().toString().padStart(2, '0')} / ${(today.getFullYear() % 100).toString().padStart(2, '0')}`;
    document.getElementById("data").textContent = formattedDate;
}

let timerID = null;

function stopClock() {
    if (timerID) {
        clearTimeout(timerID);
        timerID = null;
    }
}

function startClock() {
    stopClock(); // Clear previous timer if any
    getTheDate(); // Show the date once
    showTime(); // Start the time display
}

function showTime() {
    const now = new Date();
    let hours = now.getHours();
    const minutes = now.getMinutes().toString().padStart(2, '0');
    const seconds = now.getSeconds().toString().padStart(2, '0');
    const isPM = hours >= 12;

    // Adjust hours for 12-hour format
    hours = (hours % 12) || 12; // Convert 0 to 12 for midnight
    const timeValue = `${hours}:${minutes}:${seconds} ${isPM ? 'P.M.' : 'A.M.'}`;

    document.getElementById("zegarek").textContent = timeValue;

    // Update the time every second
    timerID = setTimeout(showTime, 1000);
}