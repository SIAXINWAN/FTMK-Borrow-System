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

            // 4. Update equipment quantity
            $stmt4 = $conn->prepare("UPDATE equipment SET Quantity = Quantity + ? WHERE EquipmentID = ?");
            $stmt4->bind_param("is", $borrowQty, $equipmentId);
            $stmt4->execute();
            $stmt4->close();

            // 5. Check new quantity and update availability
            $stmt5 = $conn->prepare("SELECT Quantity FROM equipment WHERE EquipmentID = ?");
            $stmt5->bind_param("s", $equipmentId);
            $stmt5->execute();
            $result5 = $stmt5->get_result();

            if ($result5 && $result5->num_rows > 0) {
                $newQty = $result5->fetch_assoc()['Quantity'];
                $newStatus = ($newQty > 0) ? 1 : 0;
                $stmt5->close();

                $stmt6 = $conn->prepare("UPDATE equipment SET AvailabilityStatus = ? WHERE EquipmentID = ?");
                $stmt6->bind_param("is", $newStatus, $equipmentId);
                $stmt6->execute();
                $stmt6->close();
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
?>
