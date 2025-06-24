<?php
include("../connect.php");

$filter = $_GET['filter'] ?? 'All';

$sql = "SELECT bh.BorrowID, u.Name AS UserName, u.Phone, e.EquipmentName, bh.DueDate, u.Role ,ba.*
        FROM borrow_history bh
        JOIN borrow_applications ba ON bh.ApplicationID = ba.ApplicationID
        JOIN users u ON ba.UserID = u.UserID
        JOIN equipment e ON ba.EquipmentID = e.EquipmentID
        WHERE bh.ReturnDate IS NULL AND bh.DueDate < CURDATE()";

if ($filter !== 'All') {
    $sql .= " AND u.Role = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $filter);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

$no = 1;

while ($row = $result->fetch_assoc()) {
    echo "<tr>
            <td>" . $no++ . "</td>
            <td>{$row['UserName']}</td>
            <td>{$row['Phone']}</td>
            <td>{$row['EquipmentName']}</td>
            <td>{$row['DueDate']}</td>
            <td>{$row['Purpose']}</td>
          </tr>";
}
