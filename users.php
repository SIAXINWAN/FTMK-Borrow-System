<?php
require('connect.php');

$id = $_POST['matricNo'];
$nm = $_POST['name'];
$eml = $_POST['email'];
$phone = $_POST['phone'];
$role = $_POST['role'] ?? 'Student';
$pass = $_POST['password'];

$hashedPassword = password_hash($pass, PASSWORD_DEFAULT);


if (empty($id) || empty($nm) || empty($eml) || empty($phone) || empty($pass) || empty($role)) {
    echo "<p style='color:red;'>❌ Please fill in all required fields.</p>";
    exit;
}


$sql = "INSERT INTO users (UserID, Name, Email, Phone, Password, Role)
        VALUES ('$id','$nm', '$eml','$phone','$hashedPassword','$role')";

if ($conn->query($sql) === TRUE) {
    echo "<div style='text-align: center; padding-top: 100px;'>";
    echo " <img src='0images/happy.webp' alt='sad' width='150' />";
    echo "<h2>New record created successfully.</h2>";
    echo "  <p>Redirecting to login page...</p>";
    echo "  </div>";
    echo "<meta http-equiv='refresh' content='3;URl=loginPage.php'>";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
