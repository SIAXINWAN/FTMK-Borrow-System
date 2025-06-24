<?php
session_start();
include("../connect.php");

if (isset($_GET['serviceID'])) {
    $serviceID = $_GET['serviceID'];
}

$stmt = $conn->prepare("SELECT sa.*, sl.*, e.EquipmentName 
                        FROM service_approval sa
                        JOIN servicelog sl ON sa.ServiceID = sl.ServiceID
                        JOIN equipment e ON sl.EquipmentID = e.EquipmentID
                        WHERE sa.ServiceID = ?");
$stmt->bind_param("i", $serviceID);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

$row = $result->fetch_assoc();

$stmt1 = $conn->prepare("SELECT Decision FROM service_approval WHERE ServiceID = ? AND Decision IS NOT NULL");
$stmt1->bind_param("i", $serviceID);
$stmt1->execute();
$adminApprovalResult = $stmt1->get_result();
$adminApprovalRow = $adminApprovalResult->fetch_assoc();
$stmt1->close();

$adminDecision = $adminApprovalRow ? $adminApprovalRow['Decision'] : 'Pending';

$stmt2 = $conn->prepare("SELECT * FROM service_history WHERE ServiceID = ?");
$stmt2->bind_param("i", $serviceID);
$stmt2->execute();
$historyResult = $stmt2->get_result();
$history = $historyResult->fetch_assoc();
$stmt2->close();

$acceptDate = $history['AcceptDate'];
$actionTaken = $history['ActionTaken'];
$repairStatus = $history['Status'];
$note = $history['Note'];
$returnDate = $history['ReturnDate'];
$receivedReturn = $history['ReceivedReturn'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrow Equipment - FTMK Borrow System</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
        }

        header {
            background-color: #ffcc00;
            padding: 15px 20px;
            display: flex;
            align-items: center;
        }

        header h1 {
            margin: 0 auto;
            color: #000;
            font-weight: bold;
        }

        .logo {
            height: 80px;
        }

        .container {
            background-color: white;
            width: 70%;
            margin: 40px auto;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .equipment-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .equipment-table th,
        .equipment-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }

        .status-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .status-label {
            width: 320px;
        }

        .status-button {
            padding: 5px 15px;
            color: black;
            border-radius: 4px;
            font-weight: bold;
            min-width: 100px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            border: none;
        }

        .status-approved {
            background-color: #00b894;
           
        }

        .status-pending {
            color: grey;
            font-weight: bold;
        }

        .status-incomplete {
            background-color: #d63031;
            
            color: white;
        }

        .text {
            font-size: 18px;
            margin: 10px 0 5px;
        }

        .note-box {
            border: 1px solid #000;
            padding: 15px;
        }
    </style>
</head>

<body>
    <header>
        <a href="../Service/showServiceList.php">
            <img src="../0images/ftmkLogo_Yellow.png" alt="FTMK Logo" class="logo" />
        </a>
        <h1>Checking Repair & Service Status</h1>
    </header>

    <div class="container">
        <table class="equipment-table">
            <tr>
                <th>Name</th>
                <th>Description</th>
            </tr>
            <tr>
                <td><?= htmlspecialchars($row['EquipmentName']) ?></td>
                <td><?= htmlspecialchars($row['Description']) ?></td>
            </tr>
        </table>

        <div class="status-item">
            <div class="status-label">Admin Approval</div>
            <label class="<?= ($adminDecision === 'Approved') ? 'status-approved' : 'status-pending' ?>">
                <?= ($adminDecision === 'Approved') ? 'Approved' : 'Pending' ?>
            </label>
        </div>

        <div class="status-item">
            <div class="status-label">Service Request Acceptance</div>
            <label class="<?= $acceptDate ? 'status-approved' : 'status-pending' ?>">
                <?= $acceptDate ? 'Confirmed' : 'Pending' ?>
            </label>
            <?php if ($acceptDate): ?>
                <span style="padding-left: 20px; font-weight: bold; color: #555;">
                    <?= date("Y-m-d H:i", strtotime($acceptDate)) ?>
                </span>
            <?php endif; ?>
        </div>

        <div class="status-item">
            <div class="status-label">Pickup Equipment</div>
            <label class="<?= ($actionTaken === 'Done') ? 'status-approved' : 'status-pending' ?>">
                <?= ($actionTaken === 'Done') ? 'Done' : 'Pending' ?>
            </label>
        </div>

        <div class="status-item">
            <div class="status-label">Equipment Service & Repair Status</div>
            <?php
            $repairDisplay = $repairStatus ?? 'Pending';
            if ($repairDisplay === 'Completed') {
                $repairClass = 'status-approved';
            } elseif ($repairDisplay === 'Failed' || $repairDisplay === 'Incomplete') {
                $repairClass = 'status-incomplete';
            } else {
                $repairClass = 'status-pending';
            }
            ?>
            <label class="<?= $repairClass ?>">
                <?= htmlspecialchars($repairDisplay) ?>
            </label>
        </div>

        <?php if ($note): ?>
            <div class="text">Notes:</div>
            <div class="note-box">
                <?= nl2br(htmlspecialchars($note)) ?>
            </div>
        <?php endif; ?>

        <div class="text" style="font-weight: bold; padding-top: 8px">Return Equipment:</div>
        <table class="equipment-table">
            <tr>
                <td>Company Repair</td>
                <td>
                    <label class="<?= $returnDate ? 'status-approved' : 'status-pending' ?>">
                        <?= $returnDate ? 'Done' : 'Pending' ?>
                    </label>
                </td>
            </tr>
            <tr>
                <td>FTMK</td>
                <td>
                    <?php if (!$returnDate): ?>
                        <label class="status-pending">Pending</label>
                    <?php elseif ($receivedReturn !== 'Done'): ?>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Technician'): ?>
                            <label class="status-button status-click">Received</label>
                            <!-- Optional: include note to user that action should be taken on another page -->
                        <?php else: ?>
                            <label class="status-pending">Pending</label>
                        <?php endif; ?>
                    <?php else: ?>
                        <label class="status-approved">Received</label>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>

</body>

</html>