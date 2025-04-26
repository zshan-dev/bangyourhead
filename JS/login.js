window.addEventListener("load", function () {
    let signin = document.getElementById("signin");

    signin.addEventListener("click", function (event) {
        event.preventDefault(); // Prevent default form submission

        let email = document.getElementById("email").value;
        let password = document.getElementById("password").value;

        if (!email || !password) {
            showMessage("Please fill in both fields.", "red");
            return;
        }

        let formData = new FormData();
        formData.append("email", email);
        formData.append("password", password);

        fetch("php/login.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            showMessage(data.message, data.success ? "green" : "red");
            if (data.success) {
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1000);
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