<?php
include("../connect.php");

$role = $_GET['role'] ?? 'All';
$year = $_GET['year'] ?? 'All';
$month = $_GET['month'] ?? 'All';

$sql = "SELECT e.EquipmentName, COUNT(*) AS BorrowCount
        FROM borrow_history bh
        JOIN equipment e ON bh.EquipmentID = e.EquipmentID
        JOIN users u ON bh.UserID = u.UserID
        WHERE 1=1";

// Filter: Role
if ($role !== 'All') {
    $sql .= " AND u.Role = '" . $conn->real_escape_string($role) . "'";
}

// Filter: Year
if ($year !== 'All') {
    $sql .= " AND YEAR(bh.BorrowDate) = " . intval($year);
}

// Filter: Month
if ($month !== 'All') {
    $sql .= " AND MONTH(bh.BorrowDate) = " . intval($month);
}

$sql .= " GROUP BY e.EquipmentName ORDER BY BorrowCount DESC";

$result = $conn->query($sql);

$labels = [];
$data = [];

while ($row = $result->fetch_assoc()) {
    $labels[] = $row['EquipmentName'];
    $data[] = $row['BorrowCount'];
}

echo json_encode([
    'labels' => $labels,
    'data' => $data
], JSON_NUMERIC_CHECK);
