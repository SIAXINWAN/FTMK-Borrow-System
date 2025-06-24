<?php
include("../connect.php");

$role = $_GET['role'] ?? 'All';
$year = $_GET['year'] ?? 'All';
$month = $_GET['month'] ?? 'All';

$sql = "SELECT e.EquipmentName, COUNT(*) AS BorrowCount,ba.*
        FROM borrow_history bh
        JOIN borrow_applications ba ON ba.ApplicationID = bh.ApplicationID
        JOIN equipment e ON ba.EquipmentID = e.EquipmentID
        JOIN users u ON ba.UserID = u.UserID
        WHERE 1=1";

if ($role !== 'All') {
    $sql .= " AND u.Role = '" . $conn->real_escape_string($role) . "'";
}

if ($year !== 'All') {
    $sql .= " AND YEAR(bh.BorrowDate) = " . intval($year);
}

if ($month !== 'All') {
    $sql .= " AND MONTH(bh.BorrowDate) = " . intval($month);
}

$sql .= " GROUP BY e.EquipmentName ORDER BY BorrowCount DESC";

$result = $conn->query($sql);
$no = 1;

while ($row = $result->fetch_assoc()) {
    echo "<tr>
            <td>" . $no++ . "</td>
            <td>{$row['EquipmentName']}</td>
            <td>{$row['BorrowCount']}</td>
          </tr>";
}
