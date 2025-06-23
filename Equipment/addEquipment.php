<?php
session_start();
require('../connect.php');

$message = "";
$idError = "";
$old = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nm = $_POST['name'];
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $type = $_POST['type'];
    $id = $_POST['id'];
    $quantity = $_POST['quantity'];
    $desc  = $_POST['desc'];
    $date = $_POST['date'];
    $uploadPath = '';

    $old = compact('nm', 'brand', 'model', 'type', 'id', 'quantity', 'desc', 'date');

    $checkSql = "SELECT EquipmentID FROM equipment WHERE EquipmentID = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("s", $id);  
    $stmt->execute();
    $result = $stmt->get_result();


    if ($result->num_rows > 0) {
        $_SESSION['idError'] = "This Equipment ID already exists.";
        $_SESSION['old'] = $old;
    } else {
        if (isset($_FILES['picture']) && $_FILES['picture']['error'] == 0) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $uploadPath = $uploadDir . uniqid('img_') . '_' . basename($_FILES['picture']['name']);
            move_uploaded_file($_FILES['picture']['tmp_name'], $uploadPath);
        }

        $sql = "INSERT INTO equipment (EquipmentID, EquipmentName, Brand, Type, ModelNumber, Description, Quantity, AvailabilityStatus, Picture, Date)
                VALUES ('$id','$nm', '$brand','$type','$model','$desc','$quantity',1,'$uploadPath','$date')";

        if ($conn->query($sql) === TRUE) {
            $_SESSION['success'] = "Equipment <strong>$nm</strong> added successfully!";
        } else {
            $_SESSION['error'] = "Insert failed: " . htmlspecialchars($conn->error);
            $_SESSION['old'] = $old;
        }
    }

    $conn->close();
    header("Location: equipmentAddition.php");
    exit();
}
