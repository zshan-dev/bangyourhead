/**
 * Login Handler
 * 
 * @author Your Name (Your Student Number)
 * @date 2024-04-25
 * 
 * Handles user authentication by validating email and password.
 * Sets session variables upon successful login.
 */

<?php
session_start();
include "connect.php";

/**
 * Authenticates a user and creates their session
 * 
 * @param {String} $email User's email address
 * @param {String} $user_password User's password attempt
 * @returns {Array} JSON response with success status, message and redirect URL
 */
function authenticate_user($email, $user_password) {
    global $dbh;
    
    $cmd = "SELECT email, password, first_name, last_name, is_admin FROM users WHERE email = ?";
    $stmt = $dbh->prepare($cmd);
    $args = [$email];
    $success = $stmt->execute($args);

    $user_row = $stmt->fetch(); 

    if (!$user_row) {
        return [
            'success' => false,
            'message' => 'Invalid email or password. Please try again.',
            'redirect' => '/bangyourhead/login.html'
        ];
    }

    if (password_verify($user_password, $user_row["password"])) {
        $_SESSION['email'] = $user_row['email'];
        $_SESSION['first_name'] = $user_row['first_name'];
        $_SESSION['last_name'] = $user_row['last_name'];
        $_SESSION['is_admin'] = $user_row['is_admin'];
        
        return [
            'success' => true,
            'message' => 'Login successful! Welcome, ' . $user_row['first_name'] . '!',
            'redirect' => '/bangyourhead/calendar.html'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Invalid email or password. Please try again.',
            'redirect' => '/bangyourhead/login.html'
        ];
    }
}

header('Content-Type: application/json');

$email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
$user_password = $_POST['password'];

$result = authenticate_user($email, $user_password);
echo json_encode($result);
exit();
?>