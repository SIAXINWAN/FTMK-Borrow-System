<?php

session_start();
include("../connect.php");

$sql = "SELECT u.*, e.EquipmentName, e.ModelNumber, s.* 
        FROM borrow_history u 
        JOIN equipment e ON u.EquipmentID = e.EquipmentID
        JOIN users s ON u.UserId = s.UserId
        ORDER BY u.BorrowID DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();




$no = 1;
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrow History - FTMK Borrow System</title>
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

        .borrowTable,
        .borrowTable th,
        .borrowTable td {
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
    </style>
</head>

<body>
    <header>
        <?php
        if (isset($_SESSION['role'])) {
            switch ($_SESSION['role']) {
                case 'Security Office':
                    $homeLink = "../6securityOffice/securityOfficeMainPage.php";
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
        <h1>Borrow History</h1>
    </header>

    <div id="filter">
        <table id="filterTable" class="filterTable">
            <td><label for="filter">Filter </label>
                <select id="filterSelect" onchange="filterTable()">
                    <option value="all">All</option>
                    <option value="current">Current</option>
                    <option value="past">Past</option>
                </select>
            </td>
        </table>
    </div>

    <div id="borrow">
        <table id="borrowTable" class="borrowTable">
            <tr>
                <th>No</th>
                <th>Borrower Name</th>
                <th>Borrower Role</th>
                <th>Equipment Name</th>
                <th>Model Number</th>
                <th>Borrow Date</th>
                <th>Return Date</th>
            </tr>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($row['Name']); ?></td>
                        <td><?php echo htmlspecialchars($row['Role']); ?></td>
                        <td><?php echo htmlspecialchars($row['EquipmentName']); ?></td>
                        <td><?php echo htmlspecialchars($row['ModelNumber']); ?></td>
                        <td><?php echo $row['BorrowDate'] ? htmlspecialchars($row['BorrowDate']) : '-'; ?></td>
                        <td><?php echo $row['ReturnDate'] ? htmlspecialchars($row['ReturnDate']) : '-'; ?></td>
                    </tr>
                <?php } ?>
            </tbody>

        </table>
    </div>
    <script>
        function filterTable() {
            const filter = document.getElementById("filterSelect").value;
            const table = document.getElementById("borrowTable");
            const rows = table.getElementsByTagName("tr");

            for (let i = 1; i < rows.length; i++) {
                const returnDate = rows[i].cells[5].innerText.trim();
                const isReturned = returnDate !== '-';

                if (
                    filter === "all" ||
                    (filter === "past" && isReturned) ||
                    (filter === "current" && !isReturned)
                ) {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                }
            }
        }
    </script>

</body>

</html>