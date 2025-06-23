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
            margin-bottom: 20px;
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
            background-color: green;
            padding: 5px 15px;
            color: black;
            border-radius: 4px;
            font-weight: bold;
            min-width: 100px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            border: none;
            display: inline-block;
        }

        .status-click {
            color: white;
            font-weight: bold;
        }

        .status-approved {
            color: green;
            font-weight: bold;
        }

        .status-pending {
            color: grey;
            font-weight: bold;
        }

        .status-incomplete {
            color: red;
            font-weight: bold;
        }

        /* Loading Overlay Styling */
        #loadingOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .spinner-container {
            text-align: center;
            color: white;
            font-size: 20px;
        }

        .spinner {
            border: 6px solid #f3f3f3;
            border-top: 6px solid #ffcc00;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            margin: 0 auto 15px;
            animation: spin 1s linear infinite;
        }

        .spinner-text {
            font-weight: bold;
            letter-spacing: 1px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>



    <header>
        <a href="javascript:window.history.back();">
            <img src="../0images/ftmkLogo_Yellow.png" alt="FTMK Logo" class="logo" />
        </a>

        <h1>Checking Repair & Service Status</h1>
    </header>

    <div class="container">
        <div class="info">
            <label>Equipment Details:</label>
        </div>

        <table class="equipment-table">
            <tr>
                <th>Name</th>
                <th>Description</th>
            </tr>
            <tr>
                <?php
                echo "<td>" . htmlspecialchars($row['EquipmentName']) . " </td>";
                echo "<td>" . htmlspecialchars($row['Description']) . " </td>";
                ?>
            </tr>
        </table>


        <div class="status-item">
            <div class="status-label">Admin Approval</div>
            <label class=" <?php echo ($adminDecision === 'Approved') ? 'status-approved' : 'status-pending'; ?>">
                <?php echo ($adminDecision === 'Approved') ? 'Approved' : 'Pending'; ?>
            </label>
        </div>


        <div class="status-item">
            <div class="status-label">Service Request Acceptance</div>
            <label class="<?php echo ($acceptDate) ? 'status-approved' : 'status-pending'; ?>">
                <?php echo ($acceptDate) ? 'Confirmed' : 'Pending'; ?>
            </label>
            <?php if ($acceptDate): ?>
                <h4 style="padding-left: 20px;"><?php echo date("Y-m-d H:i", strtotime($acceptDate)); ?></h4>
            <?php endif; ?>
        </div>

        <div class="status-item" style="margin-bottom: 40px;">
            <div class="status-label">Pickup Equipment</div>
            <label class=" <?php echo ($actionTaken === 'Done') ? 'status-approved' : 'status-pending'; ?>">
                <?php echo ($actionTaken === 'Done') ? 'Done' : 'Pending'; ?>
            </label>
        </div>

        <div class="status-item">
            <div class="status-label">Equipment Service & Repair Status</div>
            <?php
            $repairDisplay = $repairStatus ?? 'Pending';
            if ($repairDisplay === 'Completed') {
                $repairClass = 'status-approved';
            } elseif ($repairDisplay === 'Incomplete') {
                $repairClass = 'status-incomplete';
            } else {
                $repairClass = 'status-pending';
            }
            ?>
            <label class=" <?php echo $repairClass; ?>">
                <?php echo htmlspecialchars($repairDisplay); ?>
            </label>
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
                    <label class=" <?php echo $returnDate ? 'status-approved' : 'status-pending'; ?>">
                        <?php echo $returnDate ? 'Done' : 'Pending'; ?>
                    </label>
                </td>
            </tr>

            <tr>
                <td>FTMK</td>
                <td>
                    <?php if (!$returnDate): ?>
                        <label class=" status-pending">Pending</label>

                    <?php elseif ($receivedReturn !== 'Done'): ?>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Technician'): ?>
                            <form method="POST" action="../Service/receiveReturn.php" onsubmit="return handleReceiveSubmit();">
                                <input type="hidden" name="serviceID" value="<?php echo $serviceID; ?>">
                                <button type="submit" class="status-button status-click">Received</button>
                            </form>
                        <?php else: ?>
                            <label class="status-button status-pending">Pending</label>
                        <?php endif; ?>

                    <?php else: ?>
                        <label class=" status-approved">Received</label>
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

        function showLoading() {
            const overlay = document.getElementById("loadingOverlay");
            if (overlay) {
                overlay.style.display = "flex";
            }
        }

        function handleReceiveSubmit() {
            const confirmed = confirm("Confirm received return?");
            if (confirmed) {
                showLoading(); // 显示 loading
                return true; // 允许提交
            }
            return false; // 用户取消则阻止提交
        }
    </script>

    <!-- Loading Spinner Overlay -->
    <div id="loadingOverlay" style="display: none;">
        <div class="spinner-container">
            <div class="spinner"></div>
            <div class="spinner-text">Processing...</div>
        </div>
    </div>


</body>

</html>