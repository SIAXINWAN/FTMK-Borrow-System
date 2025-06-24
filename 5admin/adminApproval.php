<?php
session_start();
include("../connect.php");
include("../Notification/sendEmail.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $applicationId = $_POST['appId'];
    $remarks = $_POST['remarks'] ?? '';
    $adminId = $_SESSION['UserID'];
    $status = ($action === 'approve') ? 'Approved' : 'Rejected';
    $now = date('Y-m-d H:i:s');

    // Update Admin's approval
    $stmt1 = $conn->prepare("UPDATE approval 
        SET Status = ?, Remarks = ?, ApprovalDate = ?, ApproverID = ? 
        WHERE ApplicationID = ? AND ApproverRole = 'Admin'");
    $stmt1->bind_param("ssssi", $status, $remarks, $now, $adminId, $applicationId);
    $stmt1->execute();

    if ($stmt1->affected_rows >= 0) {
        $stmt1->close();

        // Get applicant info
        $stmt2 = $conn->prepare("SELECT ba.UserID, ba.Quantity, ba.EquipmentID, u.Role, u.Name, u.Email, e.EquipmentName
            FROM borrow_applications ba
            JOIN users u ON ba.UserID = u.UserID
            JOIN equipment e ON ba.EquipmentID = e.EquipmentID
            WHERE ba.ApplicationID = ?");
        $stmt2->bind_param("i", $applicationId);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        $info = $result2->fetch_assoc();
        $stmt2->close();

        $userId = $info['UserID'];
        $userRole = $info['Role'];
        $userEmail = $info['Email'];
        $userName = $info['Name'];
        $equipmentName = $info['EquipmentName'];
        $equipmentId = $info['EquipmentID'];
        $quantity = $info['Quantity'];

        if ($action === 'approve') {
            // Notify security office
            $stmt3 = $conn->prepare("SELECT Name, Email FROM users WHERE Role = 'Security Office'");
            $stmt3->execute();
            $result3 = $stmt3->get_result();

            while ($row = $result3->fetch_assoc()) {
                $secEmail = $row['Email'];
                $secName = $row['Name'];

                $subject = "New Borrow Application Requires Final Approval";
                $body = "
                Dear $secName,<br><br>
                A borrow application has been approved by the Admin and is now awaiting your final approval.<br>
                Please log in to the <a href='https://webapp.utem.edu.my/student/dit/jcats/FTMK-Borrow-System/' target='_blank'><b>FTMK Borrow System</b></a>.<br><br>
                Thank you.<br><br>
                Best regards,<br>
                FTMK Borrow System<br>
                UTeM
                ";

                sendNotification($secEmail, $subject, $body);
            }

            echo "success";
        } else {
            // Rejected
            $stmt4 = $conn->prepare("UPDATE borrow_applications SET ApplicationStatus = 'Rejected' WHERE ApplicationID = ?");
            $stmt4->bind_param("i", $applicationId);
            $stmt4->execute();
            $stmt4->close();

            // Cancel Security Office approval
            $stmt5 = $conn->prepare("UPDATE approval 
                SET Status = 'Cancelled', ApprovalDate = ? 
                WHERE ApplicationID = ? AND ApproverRole = 'Security Office'");
            $stmt5->bind_param("si", $now, $applicationId);
            $stmt5->execute();
            $stmt5->close();

            $stmtQty = $conn->prepare("SELECT Quantity, AvailabilityStatus FROM equipment WHERE EquipmentID = ?");
            $stmtQty->bind_param("s", $equipmentId);
            $stmtQty->execute();
            $qtyResult = $stmtQty->get_result();
            $equipmentData = $qtyResult->fetch_assoc();
            $beforeQty = (int) $equipmentData['Quantity'];
            $currentStatus = (int) $equipmentData['AvailabilityStatus'];
            $stmtQty->close();


            // Add back equipment quantity
            // Add back equipment quantity
            $stmt6 = $conn->prepare("UPDATE equipment SET Quantity = Quantity + ? WHERE EquipmentID = ?");
            $stmt6->bind_param("is", $quantity, $equipmentId);
            $stmt6->execute();
            $stmt6->close();

            if ($beforeQty === 0 && $currentStatus === 0) {
                $stmtStatus = $conn->prepare("UPDATE equipment SET AvailabilityStatus = 1 WHERE EquipmentID = ?");
                $stmtStatus->bind_param("s", $equipmentId);
                $stmtStatus->execute();
                $stmtStatus->close();
            }


            $subject = "Your Borrow Application Has Been Rejected";
            $body = "
            Dear $userName,<br><br>
            Your borrow application for <b>$equipmentName</b> has been <b>rejected</b> by the Admin.<br><br>
            <b>Reason:</b> $remarks<br><br>
            If you have any questions, please contact the Admin.<br><br>
            Thank you.<br><br>
            Best regards,<br>
            FTMK Borrow System<br>
            UTeM
            ";

            sendNotification($userEmail, $subject, $body);

            echo "success";
        }
    } else {
        http_response_code(500);
        echo "Approval update failed.";
    }
}
