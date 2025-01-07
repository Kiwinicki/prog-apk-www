function getTheDate() {
    const today = new Date();
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
    stopClock();
    getTheDate();
    showTime();
}

function showTime() {
    const now = new Date();
    let hours = now.getHours();
    const minutes = now.getMinutes().toString().padStart(2, '0');
    const seconds = now.getSeconds().toString().padStart(2, '0');
    const isPM = hours >= 12;

    hours = (hours % 12) || 12;
    const timeValue = `${hours}:${minutes}:${seconds} ${isPM ? 'P.M.' : 'A.M.'}`;

    document.getElementById("zegarek").textContent = timeValue;
    timerID = setTimeout(showTime, 1000);
}

let decimal = false;

function convert() {
    const input = document.querySelector("#input").value;
    const fromUnit = document.querySelector("#measure1").value;
    const toUnit = document.querySelector("#measure2").value;

    const convertedValue = (input * fromUnit) / toUnit;

    document.querySelector("#display").textContent = convertedValue || 0;
}

function clearForm() {
    const form = document.forms["converterForm"];
    form.input.value = 0;
    document.querySelector("#display").textContent = 0;
    decimal = false;
}


function changeBackground(hexNumber) {
    document.body.style.backgroundColor = hexNumber;
}