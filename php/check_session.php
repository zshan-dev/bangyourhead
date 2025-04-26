<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header('Content-Type: application/json');
    echo json_encode(['loggedIn' => false]);
    exit();
}

// User is logged in, return user info
echo json_encode([
    'loggedIn' => true,
    'firstName' => $_SESSION['first_name'],
    'lastName' => $_SESSION['last_name'],
    'email' => $_SESSION['email']
]);
?> 