<?php
session_start();
include("connect.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userID = $_POST["UserID"];
    $name = mysqli_real_escape_string($conn, $_POST["Name"]);
    $email = mysqli_real_escape_string($conn, $_POST["Email"]);
    $phone = mysqli_real_escape_string($conn, $_POST["Phone"]);

    $sql = "UPDATE users SET Name = '$name', Email = '$email', Phone = '$phone' WHERE UserID = '$userID'";

    if ($conn->query($sql) === TRUE) {
        header("Location: profilePage.php");
        exit();
    } else {
        echo "Error updating profile: " . $conn->error;
    }
} else {
    echo "Invalid request.";
}
