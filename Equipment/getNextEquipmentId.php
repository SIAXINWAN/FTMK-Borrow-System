<?php
include("../connect.php");

if (isset($_GET['type'])) {
    $type = $_GET['type'];

    $prefixMap = [
        "Camera" => "EC",
        "Camera Accessory" => "ECA",
        "Audio Equipment" => "EA",
        "Computing" => "EL",
        "Presentation" => "EP",
        "Wireless Equipment" => "EW",
        "Dongle" => "ED"
    ];

    $prefix = $prefixMap[$type] ?? '';
    if ($prefix === '') {
        echo json_encode(["nextId" => ""]);
        exit;
    }

    $stmt = $conn->prepare("SELECT EquipmentID FROM equipment 
                            WHERE EquipmentID REGEXP CONCAT('^', ?, '[0-9]{3}$') 
                            ORDER BY EquipmentID DESC LIMIT 1");
    $stmt->bind_param("s", $prefix);
    $stmt->execute();
    $result = $stmt->get_result();

    $lastId = $result->fetch_assoc()['EquipmentID'] ?? '';
    $stmt->close();
    $conn->close();

    if ($lastId) {
        $number = intval(substr($lastId, strlen($prefix))) + 1;
    } else {
        $number = 1;
    }

    $nextId = $prefix . str_pad($number, 3, "0", STR_PAD_LEFT);

    echo json_encode(["nextId" => $nextId]);
}
