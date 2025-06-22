<?php
include '../connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userID = $_POST['userID'] ?? '';
    $ic = $_POST['ic'] ?? '';

    // Step 1: Check if user exists in `users` table
    $checkStmt = $conn->prepare("SELECT * FROM users WHERE UserID = ?");
    $checkStmt->bind_param("s", $userID);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult && $checkResult->num_rows > 0) {
        $userRow = $checkResult->fetch_assoc();

        // Check if password is empty/null (lecturer belum daftar)
        if (empty($userRow['Password'])) {
            // Go to dummy table to get more info
            $dummyStmt = $conn->prepare("SELECT * FROM dummy WHERE UserID = ? AND IC = ?");
            $dummyStmt->bind_param("ss", $userID, $ic);
            $dummyStmt->execute();
            $dummyResult = $dummyStmt->get_result();

            if ($dummyResult && $dummyResult->num_rows > 0) {
                $row = $dummyResult->fetch_assoc();
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

            $dummyStmt->close();
        } else {
            echo json_encode(["success" => false, "message" => "already_registered"]);
        }

        $checkStmt->close();
        $conn->close();
        exit();
    }
    $checkStmt->close();

    // Step 2: If user not in `users` table, check dummy table (student etc.)
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
