<?php

include("../connect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $serviceID = $_POST["serviceID"];
    $status = $_POST["status"];
    $note = $_POST["note"];

    $stmt = $conn->prepare("UPDATE service_history 
                        SET Status = ?, Note = ? 
                        WHERE ServiceID = ?");
    $stmt->bind_param("sss", $status, $note, $serviceID);

    if ($stmt->execute()) {
        header("Location: companyRepairServiceStatus.php?serviceID=$serviceID");
        exit;
    } else {
        echo "Error updating service status: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
