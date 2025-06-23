<?php
include("../connect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $serviceID = $_POST["serviceID"];
    $actionTaken = "Done";

    $stmt = $conn->prepare("UPDATE service_history SET ActionTaken = ? WHERE ServiceID = ?");
    $stmt->bind_param("ss", $actionTaken, $serviceID);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Pickup updated!";

        header("Location: companyRepairServiceStatus.php?serviceID=$serviceID");
        exit;
    } else {
        echo "Error updating ActionTaken: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
