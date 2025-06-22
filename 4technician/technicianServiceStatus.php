<?php
session_start();
include("../connect.php");

if (isset($_GET['serviceID'])) {
    $serviceID = $_GET['serviceID'];
}

$stmt = $conn->prepare("SELECT sa.*, sl.EquipmentID, e.EquipmentName 
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

        .header {
            background-color: #ffcc00;
            padding: 15px 30px;
            display: flex;
            align-items: center;
        }

        .logo {
            height: 70px;
            margin-right: 20px;
        }

        .header h1 {
            flex: 1;
            text-align: center;
            color: #000;
            font-weight: bold;
        }

        .container {
            background-color: white;
            width: 70%;
            margin: 40px auto;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .info label {
            display: block;
            margin: 10px 0 5px;
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

        .status {
            margin: 30px 0;
        }

        .status-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .status-label {
            width: 320px;
        }

        .condition {
            padding: 5px 15px;
            color: black;
            border-radius: 4px;
            font-weight: bold;
            min-width: 100px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .acceptance {
            background-color: limegreen;
        }

        .pickup {
            background-color: deepskyblue;
        }

        .service {
            background-color: mediumorchid;
        }

        .incomplete {
            background-color: mediumorchid;
            margin-left: 20px;
        }

        .date-picker {
            margin-right: 20px;
            padding: 5px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .text {
            color: black;
            font-size: 18px;
            margin: 10px 0 5px;
        }

        .pickup-box {
            border: 1px solid #000;
            padding: 15px;
        }

        .note-box {
            border: 1px solid #000;
            padding: 15px;
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
            background-color: limegreen;
        }

        .status-pending {
            background-color: gray;
        }

        .status-incomplete {
            background-color: orangered;
        }

        .status-failed {
            background-color: red;
            color: white;
        }
    </style>
</head>

<body>



    <div class="header">
        <a href="../Service/showServiceList.php">
            <img src="../0images/ftmkLogo_Yellow.png" alt="FTMK Logo" class="logo" />
        </a>
        <h1>Checking Repair & Service Status</h1>
    </div>

    <div class="container">
        <div class="info">
            <label>Equipment Details:</label>
        </div>

        <table class="equipment-table">
            <tr>
                <th>ID</th>
                <th>Name</th>
            </tr>
            <tr>
                <?php
                echo "<td>" . htmlspecialchars($row['EquipmentID']) . " </td>";
                echo "<td>" . htmlspecialchars($row['EquipmentName']) . " </td>";
                ?>
            </tr>
        </table>



        <div class="status-item">
            <div class="status-label">Admin Approval</div>
            <button
                class="status-button <?php echo ($adminDecision === 'Approved') ? 'status-approved' : 'status-pending'; ?>"
                disabled>
                <?php echo ($adminDecision === 'Approved') ? 'Approved' : 'Pending'; ?>
            </button>
        </div>


        <div class="status-item">

            <div class="status-label">Service Request Acceptance</div>
            <button
                class="status-button <?php echo ($acceptDate) ? 'status-approved' : 'status-pending'; ?>"
                disabled>
                <?php echo ($acceptDate) ? 'Confirmed' : 'Pending'; ?>
            </button>
            <?php if ($acceptDate): ?>
                <h4 style="padding-left: 20px;"><?php echo date("Y-m-d H:i", strtotime($acceptDate)); ?></h4>
            <?php endif; ?>
        </div>

        <div class="status-item">
            <div class="status-label">Pickup Equipment</div>
            <button
                class="status-button <?php echo ($actionTaken === 'Done') ? 'status-approved' : 'status-pending'; ?>"
                disabled>
                <?php echo ($actionTaken === 'Done') ? 'Done' : 'Pending'; ?>
            </button>
        </div>

        <div class="status-item">
            <div class="status-label">Equipment Service & Repair Status</div>
            <?php
            $repairDisplay = $repairStatus ?? 'Pending';

            if ($repairDisplay === 'Completed') {
                $repairClass = 'status-approved';
            } elseif ($repairDisplay === 'Failed') {
                $repairClass = 'status-failed';
            } else {
                $repairClass = 'status-pending';
            }
            ?>
            <button class="status-button <?php echo $repairClass; ?>" disabled>
                <?php echo htmlspecialchars($repairDisplay); ?>
            </button>
        </div>

        <?php if ($note): ?>
            <div class="text">Notes:</div>
            <div class="note-box">
                <?php echo nl2br(htmlspecialchars($note)); ?>
            </div>
        <?php endif; ?>



        <div class="text" style="font-weight: bold;padding-top:8px">Return Equipment:</div>
        <table class="equipment-table">
            <tr>
                <td>Company Repair</td>
                <td>
                    <button class="status-button <?php echo $returnDate ? 'status-approved' : 'status-pending'; ?>" disabled>
                        <?php echo $returnDate ? 'Done' : 'Pending'; ?>
                    </button>
                </td>
            </tr>

            <tr>
                <td>FTMK</td>
                <td>
                    <?php if (!$returnDate): ?>
                        <button class="status-button status-pending" disabled>Pending</button>

                    <?php elseif ($receivedReturn !== 'Done'): ?>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Technician'): ?>
                            <form method="POST" action="../Service/receiveReturn.php" onsubmit="return confirm('Confirm received return?');">
                                <input type="hidden" name="serviceID" value="<?php echo $serviceID; ?>">
                                <button type="submit" class="status-button status-approved">Received</button>
                            </form>
                        <?php else: ?>
                            <button class="status-button status-pending" disabled>Pending</button>
                        <?php endif; ?>

                    <?php else: ?>
                        <button class="status-button status-approved" disabled>Received</button>
                    <?php endif; ?>
                </td>
            </tr>


        </table>


    </div>

    <script>
        function validateReturn() {
            const returnDate = "<?php echo $returnDate; ?>";
            if (!returnDate) {
                alert("You cannot receive this return yet. Return date is not set.");
                return false;
            }
            return true;
        }
    </script>
</body>

</html>