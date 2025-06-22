<?php
include("../connect.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $historyId = $_POST['id'];
    $returnDate = date('Y-m-d');

    // 1. Update ReturnDate
    $stmt1 = $conn->prepare("UPDATE borrow_history SET ReturnDate = ? WHERE BorrowID = ?");
    $stmt1->bind_param("si", $returnDate, $historyId);
    $stmt1->execute();

    if ($stmt1->affected_rows > 0) {
        $stmt1->close();

        // 2. Get EquipmentID and ApplicationID
        $stmt2 = $conn->prepare("SELECT EquipmentID, ApplicationID FROM borrow_history WHERE BorrowID = ?");
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

            // ✅ Step 1: 查 "归还之前" 的状态
            $stmtCheck = $conn->prepare("SELECT Quantity, AvailabilityStatus FROM equipment WHERE EquipmentID = ?");
            $stmtCheck->bind_param("s", $equipmentId);
            $stmtCheck->execute();
            $result = $stmtCheck->get_result();
            $equipment = $result->fetch_assoc();
            $beforeQty = (int)$equipment['Quantity'];
            $beforeStatus = (int)$equipment['AvailabilityStatus'];
            $stmtCheck->close();

            // ✅ Step 2: 先加回 Quantity
            $stmtUpdate = $conn->prepare("UPDATE equipment SET Quantity = Quantity + ? WHERE EquipmentID = ?");
            $stmtUpdate->bind_param("is", $borrowQty, $equipmentId);
            $stmtUpdate->execute();
            $stmtUpdate->close();

            // ✅ Step 3: 判断是否需要恢复 status
            if ($beforeQty === 0 && $beforeStatus === 0) {
                $stmtStatus = $conn->prepare("UPDATE equipment SET AvailabilityStatus = 1 WHERE EquipmentID = ?");
                $stmtStatus->bind_param("s", $equipmentId);
                $stmtStatus->execute();
                $stmtStatus->close();
            }



            echo "success";
        } else {
            echo "error: Equipment info not found.";
        }
    } else {
        echo "error: Return update failed.";
    }

    $conn->close();
}
