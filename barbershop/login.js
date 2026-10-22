// login validation

// Already logged in? Go straight to home.
if (localStorage.getItem("loggedIn") === "yes") {
    window.location.href = "home.html";
}

var loginForm = document.getElementById("loginForm");
var errorText = document.getElementById("error");

loginForm.addEventListener("submit", function (e) {
    e.preventDefault();

    var username = document.getElementById("username").value.trim();
    var password = document.getElementById("password").value;

    if (username === "" || password === "") {
        errorText.textContent = "Please fill in username and password.";
        return;
    }

    // Temporary hard-coded account (backend can check the database later)
    if (username === "admin" && password === "admin123") {
        localStorage.setItem("loggedIn", "yes");
        window.location.href = "home.html";
    } else {
        errorText.textContent = "Wrong username or password.";
    }
});