const rows = document.getElementById("appointmentRows");
const dashboardMessage = document.getElementById("dashboardMessage");
const editSection = document.getElementById("editSection");
const editForm = document.getElementById("editForm");
const editDate = document.getElementById("editDate");
const editMechanic = document.getElementById("editMechanic");
const editId = document.getElementById("editId");

function showMessage(message, type) {
    dashboardMessage.className = "message " + type;
    dashboardMessage.textContent = message;
}

async function loadAppointments() {
    try {
        const response = await fetch("../backend/get_appointments.php");
        const result = await response.json();
        if (response.status === 401) {
            window.location.href = "index.html";
            return;
        }
        if (!response.ok) throw new Error(result.message || "Could not load appointments.");

        rows.innerHTML = "";
        if (result.appointments.length === 0) {
            rows.innerHTML = '<tr><td colspan="6">There are no appointments yet.</td></tr>';
            return;
        }

        result.appointments.forEach(function (appointment) {
            const tr = document.createElement("tr");
            addCell(tr, appointment.client_name);
            addCell(tr, appointment.phone);
            addCell(tr, appointment.car_registration);
            addCell(tr, appointment.appointment_date);
            addCell(tr, appointment.mechanic_name);

            const actionCell = document.createElement("td");
            const button = document.createElement("button");
            button.className = "edit-button";
            button.textContent = "Edit";
            button.addEventListener("click", function () {
                openEditForm(appointment);
            });
            actionCell.appendChild(button);
            tr.appendChild(actionCell);
            rows.appendChild(tr);
        });
    } catch (error) {
        showMessage(error.message, "error");
    }
}

function addCell(row, value) {
    const cell = document.createElement("td");
    cell.textContent = value;
    row.appendChild(cell);
}

async function loadMechanicOptions(selectedDate, selectedMechanic) {
    const response = await fetch("../backend/get_mechanics.php?date=" + encodeURIComponent(selectedDate));
    const result = await response.json();
    if (!response.ok) throw new Error(result.message || "Could not load mechanics.");

    editMechanic.innerHTML = "";
    result.mechanics.forEach(function (mechanic) {
        const option = document.createElement("option");
        option.value = mechanic.id;
        option.textContent = mechanic.name + " — " + mechanic.free_slots + " slot(s) free";
        
        if (Number(mechanic.id) === Number(selectedMechanic)) {
            option.textContent = mechanic.name + " — current mechanic";
        } else if (Number(mechanic.free_slots) === 0) {
            option.disabled = true;
        }
        editMechanic.appendChild(option);
    });
    editMechanic.value = selectedMechanic;
}

async function openEditForm(appointment) {
    editId.value = appointment.id;
    editDate.value = appointment.appointment_date;
    document.getElementById("editCar").textContent =
        "Client: " + appointment.client_name + " · Car: " + appointment.car_registration;
    editSection.hidden = false;
    try {
        await loadMechanicOptions(appointment.appointment_date, appointment.mechanic_id);
        editSection.scrollIntoView({ behavior: "smooth" });
    } catch (error) {
        showMessage(error.message, "error");
    }
}

editDate.addEventListener("change", async function () {
    if (editDate.value) {
        try {
            await loadMechanicOptions(editDate.value, editMechanic.value);
        } catch (error) {
            showMessage(error.message, "error");
        }
    }
});

editForm.addEventListener("submit", async function (event) {
    event.preventDefault();
    try {
        const response = await fetch("../backend/update_appointment.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                id: editId.value,
                appointment_date: editDate.value,
                mechanic_id: editMechanic.value
            })
        });
        const result = await response.json();
        if (response.status === 401) {
            window.location.href = "index.html";
            return;
        }
        if (!response.ok) throw new Error(result.message || "Update failed.");

        showMessage(result.message, "success");
        editSection.hidden = true;
        loadAppointments();
    } catch (error) {
        showMessage(error.message, "error");
    }
});

document.getElementById("cancelEdit").addEventListener("click", function () {
    editSection.hidden = true;
});

document.getElementById("refreshButton").addEventListener("click", loadAppointments);

document.getElementById("logoutButton").addEventListener("click", async function () {
    await fetch("../backend/logout.php");
    window.location.href = "index.html";
});

fetch("../backend/check_login.php").then(async function (response) {
    if (!response.ok) {
        window.location.href = "index.html";
        return;
    }
    const result = await response.json();
    if (result.logged_in) loadAppointments();
}).catch(function () {
    window.location.href = "index.html";
});