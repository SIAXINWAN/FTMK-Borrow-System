<?php

session_start();
include('../connect.php');

$id = $_SESSION['UserID'];

$stmt = $conn->prepare("SELECT sh.*, sl.*, e.*  
                        FROM service_history sh
                        JOIN servicelog sl ON sh.ServiceId = sl.ServiceId
                        JOIN equipment e ON sl.EquipmentID = e.EquipmentID
                        WHERE sl.Status = 'Completed' AND sl.CompanyID = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();



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
        <a href="companyRepairMainPage.php"><img src="../0images/ftmkLogo_Yellow.png" alt="FTMK Logo" class="logo" /></a>
        <h1>Service History</h1>
    </header>

    <div id="filter">

    </div>

    <div id="service">
        <table id="serviceTable" class="serviceTable">
            <tr>
                <th>No</th>
                <th>Equipment ID</th>
                <th>Equipment Name</th>
                <th>Model Number</th>
                <th>Borrow Date</th>
                <th>Return Date</th>
                <th>Status</th>
            </tr>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $no++ . "</td>";
                        echo "<td>" . $row['EquipmentID'] . "</td>";
                        echo "<td>" . $row['EquipmentName'] . "</td>";
                        echo "<td>" . $row['ModelNumber'] . "</td>";
                        echo "<td>" . $row['AcceptDate'] . "</td>";
                        echo "<td>" . $row['ReturnDate'] . "</td>";
                        echo "<td><a href='../Service/checkServiceStatus.php?serviceID=" . $row['ServiceID'] . "'><button class='buttonStatus'>Check Status</button></a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo '<tr><td colspan="7" style="text-align: center;;">No service history found.</td></tr>';
                }
                ?>
            </tbody>


        </table>
    </div>
</body>

</html>