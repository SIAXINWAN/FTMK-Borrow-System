<?php
include("../connect.php");
include("sendEmail.php");

$today = date('Y-m-d');

$sql = "SELECT bh.BorrowID, bh.UserID, bh.EquipmentID, u.Email, u.Name, e.EquipmentName, bh.DueDate
        FROM borrow_history bh
        JOIN users u ON bh.UserID = u.UserID
        JOIN equipment e ON bh.EquipmentID = e.EquipmentID
        WHERE bh.DueDate < ? AND bh.ReturnDate IS NULL";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $email = $row['Email'];
    $name = $row['Name'];
    $equipment = $row['EquipmentName'];
    $due = $row['DueDate'];

    $subject = "Overdue Equipment Reminder";
    $body = "
        Dear $name,<br><br>
        This is a reminder that the equipment <b>$equipment</b> was due on <b>$due</b>.<br>
        Please return it as soon as possible.<br><br>
        Thank you.<br><br>
        Best regards,<br>
        FTMK Borrow System
    ";

    sendNotification($email, $subject, $body);
}

$stmt->close();
$conn->close();

echo "Checked at $today";
