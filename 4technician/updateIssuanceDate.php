<?php
include("../connect.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $borrowId = $_POST['id'];
$borrowDate = date('Y-m-d');

// Get role info
$stmt1 = $conn->prepare("SELECT u.Role 
                         FROM borrow_history bh
                         JOIN users u ON bh.UserID = u.UserID
                         WHERE bh.BorrowID = ?");
$stmt1->bind_param("i", $borrowId);
$stmt1->execute();
$infoResult = $stmt1->get_result();
$stmt1->close();

if ($infoResult && $infoResult->num_rows > 0) {
    $role = $infoResult->fetch_assoc()['Role'];

    $days = ($role === 'Lecturer') ? 105 : 7;
    $dueDate = date('Y-m-d', strtotime("+$days days"));

    // Update borrow date and due date
    $stmt2 = $conn->prepare("UPDATE borrow_history 
                             SET BorrowDate = ?, DueDate = ?
                             WHERE BorrowID = ?");
    $stmt2->bind_param("ssi", $borrowDate, $dueDate, $borrowId);

    if ($stmt2->execute()) {
        echo "success";
    } else {
        echo "error: " . $stmt2->error;
    }

    $stmt2->close();
} else {
    echo "error: User info not found.";
}

$conn->close();

}