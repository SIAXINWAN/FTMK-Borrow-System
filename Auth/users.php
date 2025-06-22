<?php
require('../connect.php');

$id = $_POST['matricNo'];
$nm = $_POST['name'];
$eml = $_POST['email'];
$phone = $_POST['phone'];
$role = $_POST['role'] ?? '';
$pass = $_POST['password'];

$hashedPassword = password_hash($pass, PASSWORD_DEFAULT);

if (empty($id) || empty($nm) || empty($eml) || empty($phone) || empty($pass) || empty($role)) {
    echo "<p style='color:red;'>❌ Please fill in all required fields.</p>";
    exit;
}

$sql = "INSERT INTO users (UserID, Name, Email, Phone, Password, Role)
        VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssss", $id, $nm, $eml, $phone, $hashedPassword, $role);

if ($stmt->execute()) {
    echo "<div style='text-align: center; padding-top: 100px;'>";
    echo " <img src='../0images/happy.webp' alt='happy' width='150' />";
    echo "<h2>Welcome to Our System!</h2>";
    echo "<h3>Thank you for registering. Your account has been created successfully. Please log in to get started.</h3>";
    echo "  <p>Redirecting to login page...</p>";
    echo "</div>";
    echo "<meta http-equiv='refresh' content='3;URL=../index.php'>";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
