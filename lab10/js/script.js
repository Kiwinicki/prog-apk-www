let timerInterval;
let elapsedTime = 0;

function formatTime(seconds) {
    const hrs = String(Math.floor(seconds / 3600)).padStart(2, '0');
    const mins = String(Math.floor((seconds % 3600) / 60)).padStart(2, '0');
    const secs = String(seconds % 60).padStart(2, '0');
    return `${hrs}:${mins}:${secs}`;
}

function updateDisplay() {
    document.getElementById('timer').textContent = formatTime(elapsedTime);
}

function startTimer() {
    if (timerInterval) return;
    timerInterval = setInterval(() => {
        elapsedTime++;
        updateDisplay();
    }, 1000);
}

function stopTimer() {
    clearInterval(timerInterval);
    timerInterval = null;
}

function resetTimer() {
    stopTimer();
    elapsedTime = 0;
    updateDisplay();
}

updateDisplay();

let timerID = null;

function stopClock() {
    if (timerID) {
        clearTimeout(timerID);
        timerID = null;
    }
}

function startClock() {
    stopClock();
    getTheDate();
    showTime();
}