<?php
session_start();
include("../connect.php");

$sql = "SELECT u.*, e.EquipmentName, s.* ,ba.*
        FROM borrow_history u
        JOIN borrow_applications ba ON u.ApplicationID = ba.ApplicationID
        JOIN equipment e ON ba.EquipmentID = e.EquipmentID
        JOIN users s ON ba.UserID = s.UserID
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

        .no-data-row td {
            font-style: italic;
            background-color: #f9f9f9;
            color: #555;
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
                default:
                    $homeLink = "../index.php";
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
            <thead>
                <tr>
                    <th>No</th>
                    <th>Borrower Name</th>
                    <th>Borrower Role</th>
                    <th>Equipment Name</th>
                    <th>Borrow Date</th>
                    <th>Due Date</th>
                    <th>Return Date</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($row['Name']); ?></td>
                        <td><?php echo htmlspecialchars($row['Role']); ?></td>
                        <td><?php echo htmlspecialchars($row['EquipmentName']); ?></td>
                        <td><?php echo $row['BorrowDate'] ? htmlspecialchars($row['BorrowDate']) : '-'; ?></td>
                        <td><?php echo $row['DueDate'] ? htmlspecialchars($row['DueDate']) : '-'; ?></td>
                        <td>
                            <?php
                            $today = date("Y-m-d");
                            $dueDate = $row['DueDate'];
                            $returnDate = $row['ReturnDate'];

                            if (empty($returnDate) || $returnDate === '0000-00-00') {
                                if ($today > $dueDate && !empty($dueDate)) {
                                    echo "<span style='color: red; font-weight: bold;'>Late</span>";
                                } else {
                                    echo "-";
                                }
                            } else {
                                echo htmlspecialchars($returnDate);
                            }
                            ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <script>
        function filterTable() {
            const filter = document.getElementById("filterSelect").value;
            const tableBody = document.getElementById("tableBody");
            const rows = tableBody.getElementsByTagName("tr");

            let visibleCount = 0;

            // Remove existing "no data" row if any
            const oldNoDataRow = document.querySelector(".no-data-row");
            if (oldNoDataRow) oldNoDataRow.remove();

            for (let i = 0; i < rows.length; i++) {
                const returnDate = rows[i].cells[6].innerText.trim();
                const isReturned = !(returnDate === '' || returnDate === '-' || returnDate === 'Late');

                if (
                    filter === "all" ||
                    (filter === "past" && isReturned) ||
                    (filter === "current" && !isReturned)
                ) {
                    rows[i].style.display = "";
                    visibleCount++;
                } else {
                    rows[i].style.display = "none";
                }
            }

            // If no rows are visible, show "no data" row
            if (visibleCount === 0) {
                const newRow = document.createElement("tr");
                newRow.className = "no-data-row";

                const td = document.createElement("td");
                td.colSpan = 7;
                td.textContent = "No records found for selected filter.";
                td.style.textAlign = "center";

                newRow.appendChild(td);
                tableBody.appendChild(newRow);
            }
        }
    </script>
</body>

</html>