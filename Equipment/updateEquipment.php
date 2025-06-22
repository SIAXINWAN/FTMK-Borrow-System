<?php
session_start();
include("../connect.php");

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $name = $_POST['name'] ?? '';
    $brand = $_POST['brand'] ?? '';
    $model = $_POST['model'] ?? '';
    $type = $_POST['type'] ?? '';
    $id = $_POST['id'] ?? '';
    $quantity = $_POST['quantity'] ?? '1';
    $desc = $_POST['desc'] ?? '';
    $date = $_POST['date'] ?? '';

    $uploadPath = null;
    $picture_sql = "";

    if (isset($_FILES['picture']) && $_FILES['picture']['error'] === 0) {
        $uploadDir = 'uploads/';
        $originalName = basename($_FILES['picture']['name']);
        $newFileName = uniqid('img_') . '_' . $originalName;
        $uploadPath = $uploadDir . $newFileName;

        if (!move_uploaded_file($_FILES['picture']['tmp_name'], $uploadPath)) {
            echo "<p style='text-align:center;color:red'>Image upload failed.</p>";
            exit;
        }
    }

    if ($uploadPath) {
        $sql = "UPDATE equipment SET 
            EquipmentName = ?, 
            Brand = ?, 
            ModelNumber = ?, 
            Type = ?, 
            Quantity = ?, 
            Description = ?, 
            Picture = ?, 
            Date = ?
            WHERE EquipmentID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssissss", $name, $brand, $model, $type, $quantity, $desc, $uploadPath, $date, $id);
    } else {
        $sql = "UPDATE equipment SET 
            EquipmentName = ?, 
            Brand = ?, 
            ModelNumber = ?, 
            Type = ?, 
            Quantity = ?, 
            Description = ?, 
            Date = ?
            WHERE EquipmentID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssisss", $name, $brand, $model, $type, $quantity, $desc, $date, $id);
    }

    if ($stmt->execute()) {
        $_SESSION['success'] = "Equipment $name updated successfully.";
        header("Location: ../5admin/adminEquipmentInventory.php");
        exit;
    } else {
        echo "Update error: " . $stmt->error;
    }

    $stmt->close();
}
