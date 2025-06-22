<?php
session_start();
include("../connect.php");
include("../Notification/sendEmail.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $applicationId = $_POST['appId'];
    $remarks = $_POST['remarks'] ?? '';
    $lecturerId = $_SESSION['UserID'];

    $status = ($action === 'approve') ? 'Approved' : 'Rejected';
    $now = date('Y-m-d H:i:s');

    // 1. Update lecturer approval
    $stmt2 = $conn->prepare("UPDATE approval
                        SET Status = ?, Remarks = ?, ApprovalDate = ?
                        WHERE ApplicationID = ? AND ApproverRole = 'Lecturer' AND ApproverID = ?");
    $stmt2->bind_param("sssis", $status, $remarks, $now, $applicationId, $lecturerId);
    $success = $stmt2->execute();

    if ($success) {
        if ($action === 'approve') {
            // 2. Notify Admin
            $stmt3 = $conn->prepare("SELECT Name, Email FROM users WHERE Role = ?");
            $role3 = 'Admin';
            $stmt3->bind_param('s', $role3);
            $stmt3->execute();
            $adminResult = $stmt3->get_result();

            while ($admin = mysqli_fetch_assoc($adminResult)) {
                $adminEmail = $admin['Email'];
                $adminName = $admin['Name'];

                $subject = "New Borrow Application Pending Your Approval";
                $body = "
                Dear $adminName,<br><br>
                A borrow application has been approved by the lecturer and is now awaiting your action.<br>
                Please log in to the <b><a href='https://webapp.utem.edu.my/student/dit/jcats/FTMK-Borrow-System/' target='_blank'>FTMK Borrow System</a></b> to review the application.<br><br>
                Thank you.<br><br>

                Best regards,<br>
                FTMK Borrow System<br>
                University Teknikal Malaysia Melaka (UTeM)<br>";

                sendNotification($adminEmail, $subject, $body);
            }

            http_response_code(200);
            echo "success";
        } else {
            // 3. Mark application as rejected
            $status = 'Rejected';
            $stmt4 = $conn->prepare("UPDATE borrow_applications SET ApplicationStatus = ? WHERE ApplicationID = ?");
            $stmt4->bind_param('si', $status, $applicationId);
            $success4 = $stmt4->execute();

            // 4. Cancel admin approval
            $status5 = 'Cancelled';
            $Arole = 'Admin';
            $stmt5 = $conn->prepare("UPDATE approval 
                          SET Status = ?, ApprovalDate = ?
                          WHERE ApplicationID = ? AND ApproverRole = ?");
            $stmt5->bind_param('ssis', $status5, $now, $applicationId, $Arole);
            $cancelResult = $stmt5->execute();

            $status7 = 'Cancelled';
            $SRole = 'Security Office';
            $stmt7 = $conn->prepare("UPDATE approval 
                          SET Status = ?, ApprovalDate = ?
                          WHERE ApplicationID = ? AND ApproverRole = ?");
            $stmt7->bind_param('ssis', $status7, $now, $applicationId, $SRole);
            $cancel2Result = $stmt7->execute();

            $stmtQty = $conn->prepare("SELECT EquipmentID, Quantity FROM borrow_applications WHERE ApplicationID = ?");
            $stmtQty->bind_param("i", $applicationId);
            $stmtQty->execute();
            $qtyResult = $stmtQty->get_result();

            if ($qtyRow = $qtyResult->fetch_assoc()) {
                $equipmentId = $qtyRow['EquipmentID'];
                $quantity = $qtyRow['Quantity'];

                // ✅ 先在还回去之前获取原始状态
                $stmtCheckBefore = $conn->prepare("SELECT Quantity, AvailabilityStatus FROM equipment WHERE EquipmentID = ?");
                $stmtCheckBefore->bind_param("s", $equipmentId);
                $stmtCheckBefore->execute();
                $beforeResult = $stmtCheckBefore->get_result();
                $beforeData = $beforeResult->fetch_assoc();
                $beforeQty = (int) $beforeData['Quantity'];
                $beforeStatus = (int) $beforeData['AvailabilityStatus'];
                $stmtCheckBefore->close();

                // 加回数量
                $stmtUpdateEq = $conn->prepare("UPDATE equipment SET Quantity = Quantity + ? WHERE EquipmentID = ?");
                $stmtUpdateEq->bind_param("is", $quantity, $equipmentId);
                $stmtUpdateEq->execute();
                $stmtUpdateEq->close();

                // ✅ 精准判断：只有系统设为 unavailable，且数量恢复时才改 status
                if ($beforeQty === 0 && $beforeStatus === 0) {
                    $stmtRestore = $conn->prepare("UPDATE equipment SET AvailabilityStatus = 1 WHERE EquipmentID = ?");
                    $stmtRestore->bind_param("s", $equipmentId);
                    $stmtRestore->execute();
                    $stmtRestore->close();
                }
            }

            // 6. Notify student
            $stmt6 = $conn->prepare("SELECT u.Email, u.Name, e.EquipmentName
                FROM borrow_applications b
                JOIN users u ON u.UserID = b.UserID
                JOIN equipment e ON e.EquipmentID = b.EquipmentID
                WHERE b.ApplicationID = ?");
            $stmt6->bind_param('i', $applicationId);
            $stmt6->execute();
            $studentResult = $stmt6->get_result();

            if ($student = mysqli_fetch_assoc($studentResult)) {
                $studentEmail = $student['Email'];
                $studentName = $student['Name'];
                $equipmentName = $student['EquipmentName'];

                $subject = "Your Borrow Application Has Been Rejected";
                $body = "
                    Dear $studentName,<br><br>
                    Your equipment ($equipmentName) borrow application has been <b>rejected</b> by the lecturer.<br><br>
                    <b>Reason:</b> $remarks<br><br>
                    You may contact your lecturer for more information.<br><br>
                    Thank you.<br><br>

                    Best regards,<br>
                    FTMK Borrow System<br>
                    University Teknikal Malaysia Melaka (UTeM)<br>";

                sendNotification($studentEmail, $subject, $body);
            }

            http_response_code(200);
            echo "success";
        }
    } else {
        echo "failed at lecturer approval update";
    }
}
