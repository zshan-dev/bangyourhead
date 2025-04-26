/**
 * Login Form Handler
 * 
 * @author Your Name (Your Student Number)
 * @date 2024-04-25
 * @description Manages the login form submission and authentication process:
 * - Validates form input
 * - Submits credentials to server
 * - Handles authentication response
 * - Manages user feedback and redirection
 */

/**
 * Initializes login form event handlers when the page loads
 */
window.addEventListener("load", function () {
    let signin = document.getElementById("signin");

    /**
     * Handles the login form submission
     * Validates input, submits to server, and processes response
     * @param {Event} event - The form submission event
     */
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

/**
 * Displays a message to the user with specified color
 * 
 * @param {string} message - The message to display
 * @param {string} color - The color of the message (e.g., "red" for errors, "green" for success)
 * @returns {void}
 */
function showMessage(message, color) {
    let msgElement = document.getElementById("login-message");
    msgElement.innerText = message;
    msgElement.style.color = color;
}