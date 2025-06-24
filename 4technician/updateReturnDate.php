<?php
header('Content-Type: application/json');

ini_set("display_errors", 1);
error_reporting(E_ALL);

include("../connect.php");
date_default_timezone_set('Asia/Kuala_Lumpur');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $historyId = $_POST['id'];
    $returnDate = date('Y-m-d');

    // 1. Update ReturnDate
    $stmt1 = $conn->prepare("UPDATE borrow_history SET ReturnDate = ? WHERE BorrowID = ?");
    $stmt1->bind_param("si", $returnDate, $historyId);

    if ($stmt1->execute()) {
        $stmt1->close();

        // 2. Get EquipmentID and ApplicationID
        $stmt2 = $conn->prepare("SELECT e.EquipmentID, ba.ApplicationID FROM borrow_history bh
        JOIN borrow_applications ba ON bh.ApplicationID = ba.ApplicationID
        JOIN equipment e ON e.EquipmentID = ba.EquipmentID
         WHERE BorrowID = ?");
        $stmt2->bind_param("i", $historyId);
        $stmt2->execute();
        $result2 = $stmt2->get_result();

        if ($result2 && $result2->num_rows > 0) {
            $row = $result2->fetch_assoc();
            $equipmentId = $row['EquipmentID'];
            $applicationId = $row['ApplicationID'];
            $stmt2->close();

            // 3. Get quantity borrowed
            $stmt3 = $conn->prepare("SELECT Quantity FROM borrow_applications WHERE ApplicationID = ?");
            $stmt3->bind_param("i", $applicationId);
            $stmt3->execute();
            $result3 = $stmt3->get_result();
            $borrowQty = 1;

            if ($result3 && $result3->num_rows > 0) {
                $borrowQty = $result3->fetch_assoc()['Quantity'];
            }
            $stmt3->close();

            $stmtCheck = $conn->prepare("SELECT Quantity, AvailabilityStatus FROM equipment WHERE EquipmentID = ?");
            $stmtCheck->bind_param("s", $equipmentId);
            $stmtCheck->execute();
            $result = $stmtCheck->get_result();
            $equipment = $result->fetch_assoc();
            $beforeQty = (int)$equipment['Quantity'];
            $beforeStatus = (int)$equipment['AvailabilityStatus'];
            $stmtCheck->close();

            $stmtUpdate = $conn->prepare("UPDATE equipment SET Quantity = Quantity + ? WHERE EquipmentID = ?");
            $stmtUpdate->bind_param("is", $borrowQty, $equipmentId);
            $stmtUpdate->execute();
            $stmtUpdate->close();

            if ($beforeQty === 0 && $beforeStatus === 0) {
                $stmtStatus = $conn->prepare("UPDATE equipment SET AvailabilityStatus = 1 WHERE EquipmentID = ?");
                $stmtStatus->bind_param("s", $equipmentId);
                $stmtStatus->execute();
                $stmtStatus->close();
            }




            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Return update failed."]);
        }

        $conn->close();
    }
}
