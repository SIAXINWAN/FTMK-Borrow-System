<?php
include '../connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userID = $_POST['userID'] ?? '';
    $ic = $_POST['ic'] ?? '';

    $checkStmt = $conn->prepare("SELECT * FROM users WHERE UserID = ?");
    $checkStmt->bind_param("s", $userID);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult && $checkResult->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "already_registered"]);
        $checkStmt->close();
        $conn->close();
        exit();
    }
    $checkStmt->close();

    $stmt = $conn->prepare("SELECT * FROM dummy WHERE UserID = ? AND IC = ?");
    $stmt->bind_param("ss", $userID, $ic);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode([
            "success" => true,
            "userID" => $row['UserID'],
            "name" => $row['Name'],
            "email" => $row['Email'],
            "phone" => $row['Phone'],
            "role" => $row['Role']
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "not_found"]);
    }

    $stmt->close();
    $conn->close();
}
