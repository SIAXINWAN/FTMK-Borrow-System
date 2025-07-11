<?php
session_start();
include("../connect.php");
include("../Notification/sendEmail.php");

$technicianID = $_SESSION['UserID'];
$companyID = $_POST['company'];
$equipmentID = $_GET['id'];
$requesterID = $_POST['staffNo'];
$description = $_POST['reason'];
$quantity = $_POST['quantity'];
$requestDate = date('Y-m-d H:i:s');
$status = "Pending";

$stmt = $conn->prepare("INSERT INTO servicelog 
                        (CompanyID, EquipmentID, RequesterID, Description, RequestDate, Status,Quantity) 
                        VALUES (?, ?, ?, ?, ?, ?,?)");
$stmt->bind_param("ssssssi", $companyID, $equipmentID, $technicianID, $description, $requestDate, $status, $quantity);

if ($stmt->execute()) {
    $serviceID = $conn->insert_id;
    $stmt->close();

    $stmt2 = $conn->prepare("INSERT INTO service_approval 
                             (ServiceID, ApproverID, ApprovalDate, Decision, Remarks) 
                             VALUES (?, NULL, NULL, 'Pending', NULL)");
    $stmt2->bind_param("i", $serviceID);

    if ($stmt2->execute()) {
        $stmt2->close();

        $stmt3 = $conn->prepare("INSERT INTO service_history (ServiceID) VALUES (?)");
        $stmt3->bind_param("i", $serviceID);
        $stmt3->execute();
        $stmt3->close();

        $updateQty = $conn->prepare("UPDATE equipment SET Quantity = Quantity - ? WHERE EquipmentID = ?");
        $updateQty->bind_param("is", $quantity, $equipmentID);
        $updateQty->execute();
        $updateQty->close();

        $checkQty = $conn->prepare("SELECT Quantity FROM equipment WHERE EquipmentID = ?");
        $checkQty->bind_param("s", $equipmentID);
        $checkQty->execute();
        $result = $checkQty->get_result();
        if ($row = $result->fetch_assoc()) {
            if ((int)$row['Quantity'] <= 0) {
                $setUnavailable = $conn->prepare("UPDATE equipment SET AvailabilityStatus = 0 WHERE EquipmentID = ?");
                $setUnavailable->bind_param("s", $equipmentID);
                $setUnavailable->execute();
                $setUnavailable->close();
            }
        }
        $checkQty->close();


        $adminResult = $conn->query("SELECT Name, Email FROM users WHERE Role = 'Admin'");
        while ($admin = $adminResult->fetch_assoc()) {
            $adminEmail = $admin['Email'];
            $adminName = $admin['Name']; 

            $subject = "New Service Request Pending Your Approval";
            $body = "
Dear $adminName,<br><br>
A new service request (Service ID: $serviceID) has been submitted and is now awaiting your approval.<br>
Please log in to the <b><a href='https://webapp.utem.edu.my/student/dit/jcats/FTMK-Borrow-System/' target='_blank'>FTMK Borrow System</a></b> to review the application.<br><br>
Thank you.<br><br>

Best regards,<br>
FTMK Borrow System<br>
University Teknikal Malaysia Melaka (UTeM)<br>";
            sendNotification($adminEmail, $subject, $body);
        }



        header("Location: technicianServiceStatus.php?serviceID=$serviceID");
        exit;
    } else {
        echo "Error inserting into service_approval: " . $conn->error;
    }
} else {
    echo "Error inserting into servicelog: " . $conn->error;
}
