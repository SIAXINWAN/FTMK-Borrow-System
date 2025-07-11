<?php

session_start();
include('../connect.php');

$status = 'Completed';

$sql = "SELECT sh.*, sl.*, e.*, u.Name as CompanyName
        FROM service_history sh
        JOIN servicelog sl ON sh.ServiceID = sl.ServiceID
        JOIN users u ON sl.CompanyId = u.UserId
        JOIN equipment e ON sl.EquipmentID = e.EquipmentID
        WHERE sl.Status = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $status);
$stmt->execute();

$result = $stmt->get_result();


$no = 1;

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service History - FTMK Borrow System</title>
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

        table {
            width: 80%;
            margin: auto;
        }

        .serviceTable,
        .serviceTable th,
        .serviceTable td {
            border: 1px solid black;
            border-collapse: collapse;
            padding: 15px;
            text-align: center;
        }

        th {
            background-color: rgb(53, 52, 52);
            color: white;
        }

        .filterTable {
            text-align: right;
        }

        #filter {
            padding-top: 50px;
            padding-bottom: 20px;
        }

        .buttonStatus {
            height: 30px;
            width: 100px;
            background-color: #ffcc00;
            text-align: center;
            padding: 0;
            border-radius: 10px;
            margin-bottom: 5px;
        }
    </style>
</head>

<body>
    <header>
        <?php
        if (isset($_SESSION['role'])) {
            switch ($_SESSION['role']) {
                case 'Technician':
                    $homeLink = "../4technician/technicianMainPage.php";
                    break;
                case 'Admin':
                    $homeLink = "../5admin/adminMainPage.php";
                    break;
            }
        } else {
            $homeLink = "../index.php";
        }
        ?>
        <a href="<?php echo $homeLink; ?>"><img src="../0images/ftmkLogo_Yellow.png" alt="FTMK Logo" class="logo" /></a>
        <h1>Service History</h1>
    </header>

    <div id="filter">

    </div>

    <div id="service">
        <table id="serviceTable" class="serviceTable">
            <tr>
                <th>No</th>
                <th>Compnay Name</th>
                <th>Equipment Name</th>
                <th>Model Number</th>
                <th>Borrow Date</th>
                <th>Return Date</th>
                <th>Check Status</th>
            </tr>
            <?php
            $rows = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            ?>

            <tbody>
                <?php if (empty($rows)) { ?>
                    <tr class="no-data-row">
                        <td colspan="7" style="text-align: center; font-style: italic; color: #555; background-color: #f0f0f0;">
                            No completed service record found.
                        </td>
                    </tr>
                <?php } else { ?>
                    <?php foreach ($rows as $row) { ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo htmlspecialchars($row['CompanyName']); ?></td>
                            <td><?php echo htmlspecialchars($row['EquipmentName']); ?></td>
                            <td><?php echo htmlspecialchars($row['ModelNumber']); ?></td>
                            <td><?php echo htmlspecialchars($row['AcceptDate']); ?></td>
                            <td><?php echo htmlspecialchars($row['ReturnDate']); ?></td>
                            <td>
                                <a href="checkServiceStatus.php?serviceID=<?php echo $row['ServiceID']; ?>">
                                    <button class="buttonStatus">Check Status</button>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            </tbody>



        </table>
    </div>
</body>

</html>