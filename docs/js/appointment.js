const dateInput = document.getElementById("appointmentDate");
const mechanicSelect = document.getElementById("mechanicSelect");
const availabilityMessage = document.getElementById("availabilityMessage");
const appointmentForm = document.getElementById("appointmentForm");
const formMessage = document.getElementById("formMessage");

const today = new Date();
const localToday = new Date(today.getTime() - today.getTimezoneOffset() * 60000)
    .toISOString().split("T")[0];
dateInput.min = localToday;

dateInput.addEventListener("change", loadMechanics);

async function loadMechanics() {
    const date = dateInput.value;
    mechanicSelect.innerHTML = '<option value="">Loading mechanics...</option>';
    availabilityMessage.textContent = "";

    if (!date) {
        mechanicSelect.innerHTML = '<option value="">Choose a date first</option>';
        return;
    }

    try {
        const response = await fetch("backend/get_mechanics.php?date=" + encodeURIComponent(date));
        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || "Could not load mechanics.");
        }

        mechanicSelect.innerHTML = '<option value="">Choose a mechanic</option>';
        result.mechanics.forEach(function (mechanic) {
            const option = document.createElement("option");
            option.value = mechanic.id;
            option.textContent = mechanic.name + " — " + mechanic.free_slots + " slot(s) free";
            option.disabled = mechanic.free_slots === 0;
            mechanicSelect.appendChild(option);
        });

        availabilityMessage.textContent = "Each mechanic can accept up to 4 appointments on this date.";
        if (result.mechanics.every(function (mechanic) { return mechanic.free_slots === 0; })) {
            availabilityMessage.textContent = "All mechanics are fully booked on this date. Please choose another date.";
        }
    } catch (error) {
        mechanicSelect.innerHTML = '<option value="">Could not load mechanics</option>';
        availabilityMessage.textContent = error.message;
    }
}

appointmentForm.addEventListener("submit", async function (event) {
    event.preventDefault();
    formMessage.className = "message";
    formMessage.textContent = "";

    const formData = new FormData(appointmentForm);
    const data = Object.fromEntries(formData.entries());

    try {
        const response = await fetch("backend/book_appointment.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data)
        });
        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || "The appointment could not be booked.");
        }

        formMessage.className = "message success";
        formMessage.textContent = result.message;
        appointmentForm.reset();
        mechanicSelect.innerHTML = '<option value="">Choose a date first</option>';
        availabilityMessage.textContent = "Your appointment request was saved.";
        dateInput.min = localToday;
    } catch (error) {
        formMessage.className = "message error";
        formMessage.textContent = error.message;
    }
});