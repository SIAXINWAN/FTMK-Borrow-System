<?php
include 'connect.php'; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userID = $_POST['userID'] ?? '';
    $ic = $_POST['ic'] ?? '';

    $sql = "SELECT * FROM dummy WHERE UserID = '$userID' AND IC ='$ic' ";
    $result=$conn->query($sql);


    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode([
            "success" => true,
            "userID" => $row['UserID'],
            "name" => $row['Name'],
            "email" => $row['Email'],
            "phone" => $row['Phone']
        ]);
    } else {
        echo json_encode(["success" => false]);
    }

    $conn->close();
}
?>
