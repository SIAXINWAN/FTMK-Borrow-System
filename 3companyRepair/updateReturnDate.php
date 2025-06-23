<?php
session_start();
include("../connect.php");
include("../Notification/sendEmail.php"); 
date_default_timezone_set("Asia/Kuala_Lumpur");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $serviceID = $_POST["serviceID"];
    $returnDate = date("Y-m-d H:i:s");

    $stmt = $conn->prepare("UPDATE service_history SET ReturnDate = ? WHERE ServiceID = ?");
    $stmt->bind_param("ss", $returnDate, $serviceID);

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

            $formattedDate = date("F j, Y \\a\\t g:i A", strtotime($returnDate));

            $subject = "Equipment Returned Notification";
            $body = "
            Dear $technicianName,<br><br>
            The equipment <b>$equipmentName</b> (Service ID: $serviceID) related to your service request has been returned on <b>$formattedDate</b>.<br>
            You may log in to the <b><a href='https://webapp.utem.edu.my/student/dit/jcats/FTMK-Borrow-System/' target='_blank'>FTMK Borrow System</a></b> to view the updated status.<br><br>
            Thank you.<br><br>

            Best regards,<br>
            FTMK Borrow System<br>
            University Teknikal Malaysia Melaka (UTeM)<br>";
            sendNotification($technicianEmail, $subject, $body);
        }

        $stmt2->close();

        echo "<script>
            alert('Return date updated successfully!');
            window.location.href = 'companyRepairServiceStatus.php?serviceID=$serviceID';
        </script>";
        exit;
    } else {
        echo "<script>
            alert('Error updating return date: " . $stmt->error . "');
            window.history.back();
        </script>";
    }

    $stmt->close();
    $conn->close();
}
