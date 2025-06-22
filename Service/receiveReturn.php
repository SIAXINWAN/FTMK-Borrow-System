<?php
session_start();
include("../connect.php");
include("../Notification/sendEmail.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['serviceID']) && isset($_SESSION['role']) && $_SESSION['role'] === 'Technician') {
        $serviceID = $_POST['serviceID'];

        // 更新状态
        $stmt1 = $conn->prepare("UPDATE service_history SET ReceivedReturn = 'Done' WHERE ServiceID = ?");
        $stmt1->bind_param("i", $serviceID);

        $stmt2 = $conn->prepare("UPDATE servicelog SET Status = 'Completed' WHERE ServiceID = ?");
        $stmt2->bind_param("i", $serviceID);

        if ($stmt1->execute() && $stmt2->execute()) {
            $stmt1->close();
            $stmt2->close();

            // 获取公司信息和设备信息
            $stmt3 = $conn->prepare("
                SELECT sl.CompanyID, sl.EquipmentID, sl.Quantity, e.EquipmentName, u.Name AS CompanyName, u.Email AS CompanyEmail
                FROM servicelog sl
                JOIN equipment e ON sl.EquipmentID = e.EquipmentID
                JOIN users u ON sl.CompanyID = u.UserID
                WHERE sl.ServiceID = ?
            ");
            $stmt3->bind_param("i", $serviceID);
            $stmt3->execute();
            $result = $stmt3->get_result();

            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $companyName = $row['CompanyName'];
                $companyEmail = $row['CompanyEmail'];
                $equipmentName = $row['EquipmentName'];
                $equipmentID = $row['EquipmentID'];
                $quantityReturned = $row['Quantity']; // 归还数量

                // 1️⃣ 更新设备数量，加回库存
                // 1️⃣ 查询归还前的库存状态
                $stmtCheck = $conn->prepare("SELECT Quantity, AvailabilityStatus FROM equipment WHERE EquipmentID = ?");
                $stmtCheck->bind_param("s", $equipmentID);
                $stmtCheck->execute();
                $checkResult = $stmtCheck->get_result();
                $equipmentRow = $checkResult->fetch_assoc();
                $beforeQty = (int)$equipmentRow['Quantity'];
                $beforeStatus = (int)$equipmentRow['AvailabilityStatus'];
                $stmtCheck->close();

                // 2️⃣ 加回设备数量
                $stmt4 = $conn->prepare("UPDATE equipment SET Quantity = Quantity + ? WHERE EquipmentID = ?");
                $stmt4->bind_param("is", $quantityReturned, $equipmentID);
                $stmt4->execute();
                $stmt4->close();

                // 3️⃣ 恢复状态（如果之前是自动设为 unavailable）
                if ($beforeQty === 0 && $beforeStatus === 0) {
                    $stmtStatus = $conn->prepare("UPDATE equipment SET AvailabilityStatus = 1 WHERE EquipmentID = ?");
                    $stmtStatus->bind_param("s", $equipmentID);
                    $stmtStatus->execute();
                    $stmtStatus->close();
                }


                // 2️⃣ Email 通知
                $subject = "Equipment Received Confirmation";
                $body = "
                Dear $companyName,<br><br>
                This is to confirm that the repaired equipment <b>$equipmentName</b> (Service ID: $serviceID) has been successfully received by the technician.<br>
                The equipment has been returned to the system stock.<br><br>
                Thank you for your cooperation and service.<br><br>

                Best regards,<br>
                FTMK Borrow System<br>
                University Teknikal Malaysia Melaka (UTeM)<br>";
                sendNotification($companyEmail, $subject, $body);
            }

            $stmt3->close();
            header("Location: showServiceList.php");
            exit();
        } else {
            echo "Error updating record: " . $conn->error;
        }
    } else {
        echo "Unauthorized access.";
    }
} else {
    echo "Invalid request.";
}
