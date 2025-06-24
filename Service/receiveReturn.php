<?php
session_start();
include("../connect.php");
include("../Notification/sendEmail.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['serviceID']) && isset($_SESSION['role']) && $_SESSION['role'] === 'Technician') {
        $serviceID = $_POST['serviceID'];

        $stmt1 = $conn->prepare("UPDATE service_history SET ReceivedReturn = 'Done' WHERE ServiceID = ?");
        $stmt1->bind_param("i", $serviceID);

        $stmt2 = $conn->prepare("UPDATE servicelog SET Status = 'Completed' WHERE ServiceID = ?");
        $stmt2->bind_param("i", $serviceID);

        if ($stmt1->execute() && $stmt2->execute()) {
            $stmt1->close();
            $stmt2->close();

            $stmt3 = $conn->prepare("
                SELECT sl.CompanyID, sl.EquipmentID, sl.Quantity, e.EquipmentName, u.Name AS CompanyName, u.Email AS CompanyEmail
                FROM servicelog sl
                JOIN equipment e ON sl.EquipmentID = e.EquipmentID
                JOIN users u ON sl.CompanyID = u.UserID
                WHERE sl.ServiceID = ?
            ");
            $stmt3->bind_param("i", $serviceID);
            $stmt3->execute();
            $result = $stmt3->get_result();

            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $companyName = $row['CompanyName'];
                $companyEmail = $row['CompanyEmail'];
                $equipmentName = $row['EquipmentName'];
                $equipmentID = $row['EquipmentID'];
                $quantityReturned = $row['Quantity']; 

                $stmtCheck = $conn->prepare("SELECT Quantity, AvailabilityStatus FROM equipment WHERE EquipmentID = ?");
                $stmtCheck->bind_param("s", $equipmentID);
                $stmtCheck->execute();
                $checkResult = $stmtCheck->get_result();
                $equipmentRow = $checkResult->fetch_assoc();
                $beforeQty = (int)$equipmentRow['Quantity'];
                $beforeStatus = (int)$equipmentRow['AvailabilityStatus'];
                $stmtCheck->close();

                $stmt4 = $conn->prepare("UPDATE equipment SET Quantity = Quantity + ? WHERE EquipmentID = ?");
                $stmt4->bind_param("is", $quantityReturned, $equipmentID);
                $stmt4->execute();
                $stmt4->close();

                if ($beforeQty === 0 && $beforeStatus === 0) {
                    $stmtStatus = $conn->prepare("UPDATE equipment SET AvailabilityStatus = 1 WHERE EquipmentID = ?");
                    $stmtStatus->bind_param("s", $equipmentID);
                    $stmtStatus->execute();
                    $stmtStatus->close();
                }

                $subject = "Equipment Received Confirmation";
                $body = "
                Dear $companyName,<br><br>
                This is to confirm that the repaired equipment <b>$equipmentName</b> (Service ID: $serviceID) has been successfully received by the technician.<br>
                The equipment has been returned to the system stock.<br><br>
                Thank you for your cooperation and service.<br><br>

                Best regards,<br>
                FTMK Borrow System<br>
                University Teknikal Malaysia Melaka (UTeM)<br>";
                sendNotification($companyEmail, $subject, $body);
            }

            $stmt3->close();
            header("Location: showServiceList.php");
            exit();
        } else {
            echo "Error updating record: " . $conn->error;
        }
    } else {
        echo "Unauthorized access.";
    }
} else {
    echo "Invalid request.";
}
