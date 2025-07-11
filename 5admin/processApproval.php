<?php
include("../connect.php");
include("../Notification/sendEmail.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $approvalId = $_POST["approvalId"];
    $decision = $_POST["decision"];
    $approverId = $_POST["approverId"];
    $remarks = $_POST["remarks"] ?? null;
    $approvalDate = date("Y-m-d H:i:s");

    // Get ServiceID and related info
    $stmt = $conn->prepare("
    SELECT sa.ServiceID, sl.EquipmentID, sl.Quantity, sl.RequesterID, sl.CompanyID, 
           e.EquipmentName, u.Email AS TechnicianEmail, u.Name AS TechnicianName,
           c.Email AS CompanyEmail, c.Name AS CompanyName
    FROM service_approval sa
    JOIN servicelog sl ON sa.ServiceID = sl.ServiceID
    JOIN equipment e ON sl.EquipmentID = e.EquipmentID
    JOIN users u ON sl.RequesterID = u.UserID
    JOIN users c ON sl.CompanyID = c.UserID
    WHERE sa.ApprovalID = ?
");
    $stmt->bind_param("i", $approvalId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $data = $result->fetch_assoc();
        $stmt->close();

        $serviceId = $data['ServiceID'];
        $equipmentId = $data['EquipmentID'];
        $equipmentName = $data['EquipmentName'];

        $technicianEmail = $data['TechnicianEmail'];
        $technicianName = $data['TechnicianName'];
        $companyEmail = $data['CompanyEmail'];
        $companyName = $data['CompanyName'];
        $equipmentQuantity = $data['Quantity'];

        // Update approval table
        $updateSql = "UPDATE service_approval SET 
            ApproverID = ?, 
            ApprovalDate = ?, 
            Decision = ?, 
            Remarks = ?
            WHERE ApprovalID = ?";
        $stmt2 = $conn->prepare($updateSql);
        $stmt2->bind_param("ssssi", $approverId, $approvalDate, $decision, $remarks, $approvalId);
        $success = $stmt2->execute();
        $stmt2->close();

        if ($success) {
            // Update servicelog status
            $statusUpdate = ($decision === 'Approved') ? 'Approved' : 'Rejected';
            $stmt3 = $conn->prepare("UPDATE servicelog SET Status = ? WHERE ServiceID = ?");
            $stmt3->bind_param("si", $statusUpdate, $serviceId);
            $stmt3->execute();
            $stmt3->close();

            // === Handle email notifications ===
            if ($decision === 'Approved') {
                // Email to technician
                $subjectT = "Service Request Approved";
                $bodyT = "
                Dear $technicianName,<br><br>
                Your service request for equipment <b>$equipmentName</b> (Service ID: $serviceId) has been approved by the Admin.<br>
                Please proceed with the next steps via the <b><a href='https://webapp.utem.edu.my/student/dit/jcats/FTMK-Borrow-System/' target='_blank'>FTMK Borrow System</a></b>.<br><br>
                Thank you.<br><br>
                Best regards,<br>
                FTMK Borrow System<br>
                University Teknikal Malaysia Melaka (UTeM)<br>";
                sendNotification($technicianEmail, $subjectT, $bodyT);

                // Email to company
                $subjectC = "Action Required: Approved Service Request for Equipment";

                $bodyC = "
Dear $companyName,<br><br>

We are pleased to inform you that a service request involving the equipment <b>$equipmentName</b> (Service ID: <b>$serviceId</b>) has been <b>approved</b>.<br><br>

Please proceed to take the necessary servicing action as soon as possible. A technician will coordinate with you shortly through the <b><a href='https://webapp.utem.edu.my/student/dit/jcats/FTMK-Borrow-System/' target='_blank'>FTMK Borrow System</a></b>.<br><br>

Thank you for your prompt attention to this matter.<br><br>

Best regards,<br>
FTMK Borrow System<br>
Universiti Teknikal Malaysia Melaka (UTeM)<br>
";

                sendNotification($companyEmail, $subjectC, $bodyC);
            } elseif ($decision === 'Rejected') {
                $stmtCheck = $conn->prepare("SELECT Quantity, AvailabilityStatus FROM equipment WHERE EquipmentID = ?");
                $stmtCheck->bind_param("s", $equipmentId);
                $stmtCheck->execute();
                $checkResult = $stmtCheck->get_result();
                $equipmentRow = $checkResult->fetch_assoc();
                $beforeQty = (int) $equipmentRow['Quantity'];
                $beforeStatus = (int) $equipmentRow['AvailabilityStatus'];
                $stmtCheck->close();

                $stmt4 = $conn->prepare("UPDATE equipment SET Quantity = Quantity + ? WHERE EquipmentID = ?");
                $stmt4->bind_param("is", $equipmentQuantity, $equipmentId);
                $stmt4->execute();
                $stmt4->close();

                if ($beforeQty === 0 && $beforeStatus === 0) {
                    $stmtRestore = $conn->prepare("UPDATE equipment SET AvailabilityStatus = 1 WHERE EquipmentID = ?");
                    $stmtRestore->bind_param("s", $equipmentId);
                    $stmtRestore->execute();
                    $stmtRestore->close();
                }


                // Email to technician
                $subject = "Service Request Rejected";
                $body = "
    Dear $technicianName,<br><br>
    Your service request for equipment <b>$equipmentName</b> (Service ID: $serviceId) has been rejected by the Admin.<br>
    <b>Reason:</b> $remarks<br><br>
    You may log in to the <b><a href='https://webapp.utem.edu.my/student/dit/jcats/FTMK-Borrow-System/' target='_blank'>FTMK Borrow System</a></b> for further details.<br><br>
    Thank you.<br><br>
    Best regards,<br>
    FTMK Borrow System<br>
    University Teknikal Malaysia Melaka (UTeM)<br>";
                sendNotification($technicianEmail, $subject, $body);
            }

            echo "success";
        } else {
            echo "fail";
        }
    } else {
        echo "fail";
    }

    $conn->close();
}
