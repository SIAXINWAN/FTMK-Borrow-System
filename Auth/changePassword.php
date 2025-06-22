<?php
session_start();
include("../connect.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userID = $_POST["UserID"];
    $currentPassword = $_POST["currentPassword"];
    $newPassword = $_POST["newPassword"];
    $confirmPassword = $_POST["confirmPassword"];

    $stmt = $conn->prepare("SELECT Password FROM users WHERE UserID = ?");
    $stmt->bind_param("s", $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $hashedPassword = $row["Password"];

        if (!password_verify($currentPassword, $hashedPassword)) {
            header("Location: profilePage.php?error=wrong_password");
            exit();
        }

        if ($newPassword !== $confirmPassword) {
            header("Location: profilePage.php?error=password_mismatch");
            exit();
        }

        if (password_verify($newPassword, $hashedPassword)) {
            header("Location: profilePage.php?error=same_as_old");
            exit();
        }

        $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $updateStmt = $conn->prepare("UPDATE users SET Password = ? WHERE UserID = ?");
        $updateStmt->bind_param("ss", $newHashedPassword, $userID);

        if ($updateStmt->execute()) {
            header("Location: profilePage.php?success=password_updated");
            exit();
        } else {
            header("Location: profilePage.php?error=update_failed");
            exit();
        }

    } else {
        header("Location: profilePage.php?error=user_not_found");
        exit();
    }
} else {
    header("Location: profilePage.php?error=invalid_request");
    exit();
}
