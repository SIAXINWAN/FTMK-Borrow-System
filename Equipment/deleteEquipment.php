<?php
include("../connect.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST['id'] ?? '';

    if ($id !== '') {
        $sql = "DELETE FROM equipment WHERE EquipmentID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $id); 

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
