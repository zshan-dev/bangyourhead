<?php

include "connect.php";

$username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_SPECIAL_CHARS);
$password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_SPECIAL_CHARS);

$cmd = "SELECT password FROM users WHERE username = ?";
$stmt = $dbh->prepare($cmd);
$args = [$username];
$succes = $stmt->execute($args);

$row = $stmt->fetch(); 

if ($row === false) {

    // If user does not exist, insert them
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $cmd = "INSERT INTO users (username, password) VALUES (?, ?)";
    $stmt = $dbh->prepare($cmd);
    $args = [$username, $hashed_password];
    $succes = $stmt->execute($args);

    if ($succes) {
        echo "New user registered successfully!";
    } else {
        echo "Error: Could not Register uUser.";
    }

} else {

    echo "$username Already in Use!";
}

?>

