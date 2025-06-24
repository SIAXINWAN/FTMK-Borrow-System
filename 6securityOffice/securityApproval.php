<?php
session_start();
include("../connect.php");
include("../Notification/sendEmail.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $applicationId = $_POST['appId'];
    $remarks = $_POST['remarks'] ?? null;
    $securityId = $_SESSION['UserID'];
    $now = date('Y-m-d H:i:s');
    $status = ($action === 'approve') ? 'Approved' : 'Rejected';

    // Update approval table
    $stmt = $conn->prepare("UPDATE approval 
                            SET Status = ?, Remarks = ?, ApprovalDate = ?, ApproverID = ?
                            WHERE ApplicationID = ? AND ApproverRole = 'Security Office'");
    $stmt->bind_param("ssssi", $status, $remarks, $now, $securityId, $applicationId);
    $success = $stmt->execute();
    $stmt->close();

    if (!$success) {
        echo "Failed to update approval: " . $conn->error;
        exit;
    }

    // Fetch user & equipment info
    $stmt = $conn->prepare("SELECT b.UserID, b.EquipmentID, b.Quantity, u.Name, u.Email, u.Role
                            FROM borrow_applications b
                            JOIN users u ON b.UserID = u.UserID
                            WHERE b.ApplicationID = ?");
    $stmt->bind_param("i", $applicationId);
    $stmt->execute();
    $res = $stmt->get_result();
    $info = $res->fetch_assoc();
    $stmt->close();

    $userId = $info['UserID'];
    $equipmentId = $info['EquipmentID'];
    $borrowQty = $info['Quantity'];
    $userName = $info['Name'];
    $userEmail = $info['Email'];
    $userRole = $info['Role'];

    if ($status === 'Approved') {
        // Insert into borrow history if not already inserted
        $stmt = $conn->prepare("SELECT 1 FROM borrow_history WHERE ApplicationID = ?");
        $stmt->bind_param("i", $applicationId);
        $stmt->execute();
        $res = $stmt->get_result();
        $exists = $res->num_rows > 0;
        $stmt->close();

        if (!$exists) {
            $stmt = $conn->prepare("INSERT INTO borrow_history 
                (ApplicationID, BorrowDate, DueDate, ReturnDate) 
                VALUES (?, NULL, NULL, NULL)");
            $stmt->bind_param("i", $applicationId);
            $stmt->execute();
            $stmt->close();
        }




        // Update application status
        $stmt = $conn->prepare("UPDATE borrow_applications SET ApplicationStatus = 'Approved' WHERE ApplicationID = ?");
        $stmt->bind_param("i", $applicationId);
        $stmt->execute();
        $stmt->close();

        // Notify borrower
        $subject = "Your Borrow Application Has Been Approved";
        $body = "
            Dear $userName,<br><br>
            Your borrow application has been fully approved by all required parties including the Security Office.<br><br>
            You may now collect your equipment at the designated location.<br><br>
            Thank you.<br><br>
            Best regards,<br>
            FTMK Borrow System<br>
            UTeM
        ";
        sendNotification($userEmail, $subject, $body);
        echo "success";
    } else {
        // Update application as rejected
        $stmt = $conn->prepare("UPDATE borrow_applications SET ApplicationStatus = 'Rejected' WHERE ApplicationID = ?");
        $stmt->bind_param("i", $applicationId);
        $stmt->execute();
        $stmt->close();

        $stmtBefore = $conn->prepare("SELECT Quantity, AvailabilityStatus FROM equipment WHERE EquipmentID = ?");
        $stmtBefore->bind_param("i", $equipmentId);
        $stmtBefore->execute();
        $beforeResult = $stmtBefore->get_result();
        $beforeData = $beforeResult->fetch_assoc();
        $beforeQty = (int)$beforeData['Quantity'];
        $beforeStatus = (int)$beforeData['AvailabilityStatus'];
        $stmtBefore->close();

        $stmt = $conn->prepare("UPDATE equipment SET Quantity = Quantity + ? WHERE EquipmentID = ?");
        $stmt->bind_param("is", $borrowQty, $equipmentId);
        $stmt->execute();
        $stmt->close();


        if ($beforeQty === 0 && $beforeStatus === 0) {
            $stmtRestore = $conn->prepare("UPDATE equipment SET AvailabilityStatus = 1 WHERE EquipmentID = ?");
            $stmtRestore->bind_param("i", $equipmentId);
            $stmtRestore->execute();
            $stmtRestore->close();
        }

        $subject = "Your Borrow Application Has Been Rejected";
        $body = "
            Dear $userName,<br><br>
            Your borrow application has been <b>rejected</b> by the Security Office.<br><br>
            <b>Reason:</b> $remarks<br><br>
            If you have any questions, kindly reach out to the Security Office.<br><br>
            Best regards,<br>
            FTMK Borrow System<br>
            UTeM
        ";
        sendNotification($userEmail, $subject, $body);
        echo "success";
    }

    $conn->close();
}
