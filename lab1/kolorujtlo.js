let decimal = false;

function convert() {
    const input = document.querySelector("#input").value;
    const fromUnit = document.querySelector("#measure1").value;
    const toUnit = document.querySelector("#measure2").value;

    const convertedValue = (input * fromUnit) / toUnit;

    document.querySelector("#display").textContent = convertedValue || 0;
}

function addChar(character) {
    const input = document.querySelector("#input");

    if (character === '.' && !decimal) {
        input.value += '.';
        decimal = true;
    } else if (character !== '.') {
        input.value === "0" ? input.value = character : input.value += character;
    }

    convert();
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