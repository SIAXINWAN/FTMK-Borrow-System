<?php

include("../connect.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST['id'] ?? '';
    $status = $_POST['status'] ?? '';

    if ($id !== '' && ($status === '1' || $status === '0')) {
        $sql = "UPDATE equipment SET AvailabilityStatus = ? WHERE EquipmentID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $status, $id); 
        
        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "error";
        }

        $stmt->close();
    } else {
        echo "invalid";
    }
}
?>
