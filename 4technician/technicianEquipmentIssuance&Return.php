<?php
include("../connect.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment Issuance and Return - FTMK Borrow System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
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

        #filterTable {
            width: 80%;
            margin: 30px auto 0;
            text-align: right;
        }

        section table {
            width: 80%;
            margin-left: auto;
            margin-right: auto;
            margin-top: 20px;
        }

        section table th,
        section table td {
            text-align: center;
        }

        section th {
            background-color: rgb(53, 52, 52);
            color: white;
        }

        section table,
        section th,
        section td {
            border: 1.5px solid black;
            border-collapse: collapse;
        }

        section tr {
            height: 50px;
        }

        section td {
            text-align: center;
            vertical-align: middle;
        }

        .iconStyle {
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            width: 30px;
            height: 30px;
            background-color: greenyellow;
        }

        .buttonStyle {
            height: 50px;
            width: 50px;
            background-color: white;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            margin: 0 auto;
        }

        .no-data-row td {
            text-align: center;
            font-style: italic;
            color: #555;
            background-color: #f0f0f0
        }
    </style>
</head>

<body>
    <header>
        <a href="technicianMainPage.php"><img src="../0images/ftmkLogo_Yellow.png" height="80px"></a>
        <h1 style="text-align: center;">Equipment Issuance & Return</h1>
    </header>

    <table id="filterTable">
        <td><label for="filter">Filter </label>
            <select id="filterSelect">
                <option value="all">All</option>
                <option value="current">Current</option>
                <option value="past">Past</option>
            </select>
        </td>
    </table>

    <section>
        <table id="dataTable" cellspacing="0">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Borrower's Name</th>
                    <th>Equipment Name</th>
                    <th>Quantity</th>
                    <th>Issuance Date</th>
                    <th>Issuance Action</th>
                    <th>Due Date</th>
                    <th>Return Date</th>
                    <th>Return Action</th>
                </tr>
            </thead>
            <tbody>
                <?php


                $stmt = $conn->prepare("SELECT bh.BorrowID, ba.ApplicationID,ba.Quantity, u.Name AS BorrowerName, e.EquipmentName, bh.ReturnDate, bh.BorrowDate,bh.DueDate
                        FROM borrow_applications ba
                        JOIN users u ON ba.UserID = u.UserID
                        JOIN equipment e ON ba.EquipmentID = e.EquipmentID
                        JOIN borrow_history bh ON bh.ApplicationID = ba.ApplicationID
                        WHERE ba.ApplicationStatus = 'Approved'");
                $stmt->execute();
                $result = $stmt->get_result();
                $stmt->close();

                $no = 1;

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr class='dataRow'>";
                        echo "<td>$no</td>";
                        echo "<td>" . htmlspecialchars($row['BorrowerName']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['EquipmentName']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Quantity']) . "</td>";

                        $borrowDate = $row['BorrowDate'];
                        $dueDate = $row['DueDate'];
                        $returnDate = $row['ReturnDate'];

                        if (empty($borrowDate)) {
                            echo "<td>-</td>";
                            echo "<td><button class='buttonStyle issuance' data-id='{$row['BorrowID']}' data-due='{$row['DueDate']}'><i class='fa fa-check iconStyle'></i></button></td>";
                            echo "<td>" . (!empty($dueDate) ? date('d/m/Y', strtotime($dueDate)) : '-') . "</td>";
                            echo "<td>-</td><td>-</td>";
                        } else {
                            echo "<td>" . date('d/m/Y', strtotime($borrowDate)) . "</td><td></td>";
                            echo "<td>" . (!empty($dueDate) ? date('d/m/Y', strtotime($dueDate)) : '-') . "</td>";

                            $today = date("Y-m-d");
                            if (empty($returnDate) || $returnDate === "0000-00-00") {
                                if (!empty($dueDate) && $today > $dueDate) {
                                    echo "<td><span style='color: red; font-weight: bold;'>Late</span></td>";
                                } else {
                                    echo "<td>-</td>";
                                }
                                echo "<td><button class='buttonStyle returnAction' data-id='{$row['BorrowID']}' data-due='{$row['DueDate']}'><i class='fa fa-check iconStyle'></i></button></td>";
                            } else {
                                $formatted = date('d/m/Y', strtotime($returnDate));
                                if ($returnDate > $dueDate) {
                                    echo "<td><span style='color: red; font-weight: bold;'>$formatted</span></td><td></td>";
                                } else {
                                    echo "<td>$formatted</td><td></td>";
                                }
                            }
                        }
                        echo "</tr>";
                        $no++;
                    }
                } else {
                    echo "<tr class='no-data-row'><td colspan='9'>No approved applications found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </section>

    <script>
        $(document).ready(function() {
            $(document).on('click', ".issuance", function() {
                var button = $(this);
                var row = button.closest("tr");
                var dueDateRaw = button.data("due"); // Format: yyyy-mm-dd
                var todayFormatted = todayDate(); // dd/mm/yyyy
                var confirmIssuance = window.confirm("Are you sure you issued the equipment?");

                if (confirmIssuance) {
                    $.post("updateIssuanceDate.php", {
                        id: button.data("id")
                    }, function(response) {
                        let res = JSON.parse(response);
                        if (res.status === "success") {
                            alert("Issuance date updated successfully!");

                            // Format dates
                            const format = (dateStr) => {
                                let [y, m, d] = dateStr.split("-");
                                return `${d}/${m}/${y}`;
                            };

                            row.find("td:eq(4)").text(format(res.borrowDate)); // Issuance Date
                            row.find("td:eq(5)").html(""); // Clear button
                            row.find("td:eq(6)").text(format(res.dueDate)); // Due Date
                            row.find("td:eq(7)").text("-"); // Return Date
                            row.find("td:eq(8)").html(`
    <button class='buttonStyle returnAction' data-id='${button.data("id")}' data-due='${res.dueDate}'>
        <i class='fa fa-check iconStyle'></i>
    </button>
`);

                        } else {
                            alert("Failed to update issuance date: " + (res.message || ""));
                        }
                    });

                }
            });

            $(document).on("click", ".returnAction", function() {
                var button = $(this);
                var row = button.closest("tr")[0];
                var today = todayDate();
                var todayRaw = new Date().toISOString().split('T')[0]; // yyyy-mm-dd
                var dueRaw = button.data("due"); // yyyy-mm-dd

                let isLate = false;
                if (dueRaw && todayRaw > dueRaw) {
                    isLate = true;
                }

                if (confirm("Are you sure the equipment has been returned?")) {
                    row.cells[7].innerHTML = isLate ?
                        `<span style='color: red; font-weight: bold;'>${today}</span>` :
                        today;
                    row.cells[8].innerHTML = "";

                    $.post("updateReturnDate.php", {
                        id: button.data("id")
                    }, function(res) {
                        if (res.status === "success") {
                            alert("Return date updated successfully!");
                        } else {
                            alert("Failed to update return date: " + (res.message || ""));
                        }
                    }, 'json');

                }
            });


            $('#filterSelect').on('change', function() {
                var val = this.value;
                $('#dataTable .dataRow').each(function() {
                    var returnDateText = $(this).find('td:eq(6)').text().trim();
                    if (val === 'all') {
                        $(this).show();
                    } else if (val === 'current') {
                        $(this).toggle(returnDateText === '-' || returnDateText === 'Late');
                    } else if (val === 'past') {
                        $(this).toggle(returnDateText !== '-' && returnDateText !== 'Late');
                    }
                });
            });
        });

        function todayDate() {
            var date = new Date();
            var day = String(date.getDate()).padStart(2, '0');
            var month = String(date.getMonth() + 1).padStart(2, '0');
            var year = date.getFullYear();
            return `${day}/${month}/${year}`;
        }
    </script>
</body>

</html>