<?php
session_start();
include "connect.php";

header('Content-Type: application/json');

$email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
$user_password = $_POST['password']; // Get raw password for verification

// Debug: Check if we're receiving the data
error_log("Login attempt - Email: " . $email);

$cmd = "SELECT email, password, first_name, last_name FROM users WHERE email = ?";
$stmt = $dbh->prepare($cmd);
$args = [$email];
$success = $stmt->execute($args);

$user_row = $stmt->fetch(); 

// Debug: Check if user was found
if (!$user_row) {
    error_log("No user found with email: " . $email);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email or password. Please try again.',
        'redirect' => '/bangyourhead/login.html'
    ]);
    exit();
}

// Debug: Check password verification
if (password_verify($user_password, $user_row["password"])) {
    // Store user information in session
    $_SESSION['email'] = $user_row['email'];
    $_SESSION['first_name'] = $user_row['first_name'];
    $_SESSION['last_name'] = $user_row['last_name'];
    
    error_log("Login successful for: " . $email);
    echo json_encode([
        'success' => true,
        'message' => 'Login successful! Welcome, ' . $user_row['first_name'] . '!',
        'redirect' => '/bangyourhead/calendar.html'
    ]);
    exit();
} else {
    error_log("Password verification failed for: " . $email);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email or password. Please try again.',
        'redirect' => '/bangyourhead/login.html'
    ]);
    exit();
}
?>