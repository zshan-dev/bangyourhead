/**
 * User Registration Handler
 * 
 * @author Your Name (Your Student Number)
 * @date 2024-04-25
 * 
 * Handles new user registration process:
 * - Validates input data
 * - Checks for existing email
 * - Securely hashes password
 * - Creates new user record
 * 
 * Expected POST parameters:
 * - email: string (valid email address)
 * - fullName: string (first and last name)
 * - password: string (unhashed password)
 * - address: string (user's address)
 * 
 * @return string Success/error message and redirect on success
 */

<?php
require_once 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize form data
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $fullName = filter_var($_POST['fullName'], FILTER_SANITIZE_STRING);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);
    
    // Split full name into first and last name
    $nameParts = explode(' ', $fullName);
    $firstName = $nameParts[0];
    $lastName = isset($nameParts[1]) ? $nameParts[1] : '';

    try {
        // Check if email already exists
        $checkEmail = "SELECT email FROM users WHERE email = ?";
        $stmt = $dbh->prepare($checkEmail);
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            echo "Email already exists";
            exit();
        }

        // Insert new user with prepared statement
        $sql = "INSERT INTO users (email, first_name, last_name, password, address) VALUES (?, ?, ?, ?, ?)";
        $stmt = $dbh->prepare($sql);
        $stmt->execute([$email, $firstName, $lastName, $password, $address]);

        echo "Registration successful";
        header("Location: ../index.html");
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?> 