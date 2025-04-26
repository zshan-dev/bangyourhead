<?php
require_once 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $email = $_POST['email'];
    $fullName = $_POST['fullName'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $address = $_POST['address'];
    
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

        // Insert new user
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