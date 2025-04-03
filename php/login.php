<?php

include "connect.php";

$username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_SPECIAL_CHARS);
$password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_SPECIAL_CHARS);

$cmd = "SELECT password FROM users WHERE username = ?";
$stmt = $dbh->prepare($cmd);
$args = [$username];
$succes = $stmt->execute($args);

$row = $stmt->fetch(); 

if ($username == "admin") {

    if ($row && password_verify($password, $row["password"])) {
        echo "Welcome Admin";
    }

} else if ($row && password_verify($password, $row["password"])) {
    echo "Login successful! Welcome, $username.";

} else {
    echo "Wrong Password for $username";

}

?>




