<?php

session_start();
include("../connect.php");
$statusExclude = 'Completed';

$sql = "SELECT sl.*, e.EquipmentName, u.Name as CompanyName 
        FROM servicelog sl 
        JOIN equipment e ON sl.EquipmentID = e.EquipmentID
        JOIN users u ON sl.CompanyID = u.UserID
        WHERE sl.Status != ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $statusExclude);
$stmt->execute();

$result = $stmt->get_result();

$no = 1;

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Request List - FTMK Borrow System</title>
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
            margin: 50px auto;
            padding: 15px;
            text-align: center;
        }

        th {
            background-color: rgb(53, 52, 52);
            color: white;
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
        <a href="<?php echo $homeLink; ?>"> <img src="../0images/ftmkLogo_Yellow.png" alt="FTMK Logo" class="logo" /></a>
        <h1>Service Request List</h1>
    </header>

    <div id="service">
        <table id="serviceTable" class="serviceTable">
            <tr>
                <th>No</th>
                <th>Request Date</th>
                <th>Company Name</th>
                <th>Equipment Name</th>
                <th>Equipment's Problem</th>
                <th>Service Status</th>
            </tr>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $no++ . "</td>";
                        echo "<td>" . htmlspecialchars($row['RequestDate']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['CompanyName']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['EquipmentName']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Description']) . "</td>";
                        echo "<td><a href='checkServiceStatus.php?serviceID=" . $row['ServiceID'] . "'><button class='buttonStatus'>Check Status</button></a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No service requests found.</td></tr>";
                }
                ?>
            </tbody>

        </table>
    </div>
</body>

</html>