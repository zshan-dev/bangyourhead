/**
 * Session Status Checker
 * 
 * @author Your Name (Your Student Number)
 * @date 2024-04-25
 * 
 * Checks if a user is currently logged in and returns their session data
 * 
 * @return JSON Response with format:
 * {
 *   loggedIn: boolean,
 *   firstName?: string,
 *   lastName?: string,
 *   email?: string
 * }
 */

<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header('Content-Type: application/json');
    echo json_encode(['loggedIn' => false]);
    exit();
}

// User is logged in, return user info
header('Content-Type: application/json');
echo json_encode([
    'loggedIn' => true,
    'firstName' => $_SESSION['first_name'],
    'lastName' => $_SESSION['last_name'],
    'email' => $_SESSION['email']
]);
?> 