const loginForm = document.getElementById("loginForm");
const loginMessage = document.getElementById("loginMessage");

loginForm.addEventListener("submit", async function (event) {
    event.preventDefault();
    loginMessage.className = "message";
    const formData = new FormData(loginForm);
    const data = Object.fromEntries(formData.entries());

    try {
        const response = await fetch("../backend/admin_login.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        if (!response.ok) {
            throw new Error(result.message || "Login failed.");
        }
        window.location.href = "dashboard.html";
    } catch (error) {
        loginMessage.className = "message error";
        loginMessage.textContent = error.message;
    }
});
