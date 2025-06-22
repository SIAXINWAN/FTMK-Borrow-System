<?php
include("../connect.php");
include("../Notification/sendEmail.php");
date_default_timezone_set("Asia/Kuala_Lumpur");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $serviceID = $_POST["serviceID"];
    $acceptDate = $_POST["acceptDate"];

    $stmt = $conn->prepare("UPDATE service_history SET AcceptDate = ? WHERE ServiceID = ?");
    $stmt->bind_param("ss", $acceptDate, $serviceID);

    if ($stmt->execute()) {
        $stmt2 = $conn->prepare("
            SELECT sl.RequesterID, u.Name AS TechnicianName, u.Email AS TechnicianEmail, e.EquipmentName
            FROM servicelog sl
            JOIN users u ON sl.RequesterID = u.UserID
            JOIN equipment e ON sl.EquipmentID = e.EquipmentID
            WHERE sl.ServiceID = ?
        ");
        $stmt2->bind_param("s", $serviceID);
        $stmt2->execute();
        $result = $stmt2->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $technicianName = $row['TechnicianName'];
            $technicianEmail = $row['TechnicianEmail'];
            $equipmentName = $row['EquipmentName'];

            // 邮件通知
            $subject = "Service Pickup Schedule Notification";
            $formattedDate = date("F j, Y \\a\\t g:i A", strtotime($acceptDate));
            $body = "
            Dear $technicianName,<br><br>
            Your service request for <b>$equipmentName</b> (Service ID: $serviceID) has been scheduled for pickup.<br>
            The equipment will be collected on: <b>$formattedDate</b>.<br><br>
            Please ensure that everything is prepared accordingly. You may check the details in the <b><a href='https://webapp.utem.edu.my/student/dit/jcats/FTMK-Borrow-System/' target='_blank'>FTMK Borrow System</a></b>.<br><br>
            Thank you.<br><br>

            Best regards,<br>
            FTMK Borrow System<br>
            University Teknikal Malaysia Melaka (UTeM)<br>";
            sendNotification($technicianEmail, $subject, $body);
        }

        $stmt2->close();
        header("Location: companyRepairServiceStatus.php?serviceID=$serviceID");
        exit;
    } else {
        echo "Error updating AcceptDate: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
