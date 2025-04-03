window.addEventListener("load", function () {
    let signin = document.getElementById("signin");

    signin.addEventListener("click", function (event) {
        event.preventDefault(); // Prevent default form submission

        let username = document.getElementById("username").value.trim();
        let password = document.getElementById("password").value.trim();

        if (!username || !password) {
            showMessage("Please fill in both fields.", "red");
            return;
        }

        let formData = new FormData();
        formData.append("username", username);
        formData.append("password", password);

        fetch("php/login.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if (data.includes("successful")) {
                showMessage("Login successful! Redirecting...", "green");

                // Redirect to index.html after 2 seconds
                setTimeout(() => {
                    window.location.href = "index.html";
                }, 2000);
            } else {
                showMessage("Invalid username or password.", "red");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            showMessage("An error occurred. Please try again.", "red");
        });
    });
});

function showMessage(message, color) {
    let msgElement = document.getElementById("login-message");
    msgElement.innerText = message;
    msgElement.style.color = color;
}
