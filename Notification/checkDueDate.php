<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
include("../connect.php");
include("sendEmail.php");

$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));
echo "Tomorrow's date is: $tomorrow<br>";

$sql = "SELECT bh.BorrowID, u.UserID, e.EquipmentID, u.Email, u.Name, e.EquipmentName, bh.DueDate,ba.*
        FROM borrow_history bh
        JOIN borrow_applications ba ON bh.ApplicationID = ba.ApplicationID
        JOIN users u ON ba.UserID = u.UserID
        JOIN equipment e ON ba.EquipmentID = e.EquipmentID
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

$sql2 = "SELECT bh.BorrowID, u.UserID, e.EquipmentID, u.Email, u.Name, e.EquipmentName, bh.DueDate,ba.*
         FROM borrow_history bh
         JOIN borrow_applications ba ON bh.ApplicationID = ba.ApplicationID
         JOIN users u ON ba.UserID = u.UserID
         JOIN equipment e ON ba.EquipmentID = e.EquipmentID
         WHERE bh.DueDate = ? AND bh.ReturnDate IS NULL";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("s", $tomorrow);
$stmt2->execute();
$result2 = $stmt2->get_result();

while ($row = $result2->fetch_assoc()) {
    $email = $row['Email'];
    $name = $row['Name'];
    $equipment = $row['EquipmentName'];
    $due = $row['DueDate'];

    $subject = "Reminder: Equipment Due Tomorrow";
    $body = "
        Dear $name,<br><br>
        Just a reminder that the equipment <b>$equipment</b> is due on <b>$due</b> (tomorrow).<br>
        Kindly prepare to return it in time.<br><br>
        Thank you.<br><br>
        Best regards,<br>
        FTMK Borrow System
    ";

    sendNotification($email, $subject, $body);
}
$stmt2->close();

$conn->close();

echo "Reminder checked at $today";
