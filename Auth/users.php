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

// Step 1: Check if user already exists
$checkStmt = $conn->prepare("SELECT * FROM users WHERE UserID = ?");
$checkStmt->bind_param("s", $id);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult && $checkResult->num_rows > 0) {
    // Lecturer or existing user - just update password
    $updateStmt = $conn->prepare("UPDATE users SET Password = ? WHERE UserID = ?");
    $updateStmt->bind_param("ss", $hashedPassword, $id);

    if ($updateStmt->execute()) {
        echo "<div style='text-align: center; padding-top: 100px;'>";
        echo " <img src='../0images/happy.webp' alt='happy' width='150' />";
        echo "<h2>Welcome to Our System!</h2>";
        echo "<h3>Thank you for registering. Your account has been created successfully. Please log in to get started.</h3>";
        echo "<p>Redirecting to login page...</p>";
        echo "</div>";
        echo "<meta http-equiv='refresh' content='3;URL=../index.php'>";
    } else {
        echo "Error inserting user: " . $updateStmt->error;
    }

    $updateStmt->close();
} else {
    // Student or new user - insert new user
    $insertStmt = $conn->prepare("INSERT INTO users (UserID, Name, Email, Phone, Password, Role)
                                  VALUES (?, ?, ?, ?, ?, ?)");
    $insertStmt->bind_param("ssssss", $id, $nm, $eml, $phone, $hashedPassword, $role);

    if ($insertStmt->execute()) {
        echo "<div style='text-align: center; padding-top: 100px;'>";
        echo " <img src='../0images/happy.webp' alt='happy' width='150' />";
        echo "<h2>Welcome to Our System!</h2>";
        echo "<h3>Thank you for registering. Your account has been created successfully. Please log in to get started.</h3>";
        echo "<p>Redirecting to login page...</p>";
        echo "</div>";
        echo "<meta http-equiv='refresh' content='3;URL=../index.php'>";
    } else {
        echo "Error inserting user: " . $insertStmt->error;
    }

    $insertStmt->close();
}

$checkStmt->close();
$conn->close();
