
<?php
session_start();
include("../connect.php");
include("../Notification/sendEmail.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST['id'] ?? '';
    $status = $_POST['status'] ?? '';
    $reason = $_POST['reason'] ?? '';
    $currentUserEmail = $_SESSION['Email'] ?? '';

    if ($id !== '' && ($status === '1' || $status === '0') && trim($reason) !== '') {
        $sql = "UPDATE equipment SET AvailabilityStatus = ? WHERE EquipmentID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $status, $id);

        if ($stmt->execute()) {
            $query = $conn->prepare("SELECT EquipmentName, ModelNumber FROM equipment WHERE EquipmentID = ?");
            $query->bind_param("s", $id);
            $query->execute();
            $result = $query->get_result();
            $equipment = $result->fetch_assoc();
            $query->close();

            $statusText = $status == '1' ? "Available" : "Not Available";

            $getEmails = $conn->prepare("SELECT Email, Name FROM users WHERE Role IN ('Technician', 'Admin')");
            $getEmails->execute();
            $emailResult = $getEmails->get_result();

            $subject = "[Status Change Notification] Equipment: {$equipment['EquipmentName']}";

            while ($row = $emailResult->fetch_assoc()) {
                $userName = $row['Name'];
                $userEmail = $row['Email'];

                if ($userEmail === $currentUserEmail) continue;

                $personalizedMessage = "
Dear $userName,<br><br>
This is to inform you that the status of the following equipment has been changed:<br><br>
<ul>
    <li><strong>Equipment ID:</strong> $id</li>
    <li><strong>Equipment Name:</strong> {$equipment['EquipmentName']}</li>
    <li><strong>Model Number:</strong> {$equipment['ModelNumber']}</li>
    <li><strong>New Status:</strong> $statusText</li>
    <li><strong>Reason:</strong> $reason</li>
</ul>

Best regards,<br>
FTMK Borrow System<br>
UTeM
";

                sendNotification($userEmail, $subject, $personalizedMessage);
            }

            echo "success";
        } else {
            echo "error";
        }
        $stmt->close();
    } else {
        echo "invalid";
    }
}
