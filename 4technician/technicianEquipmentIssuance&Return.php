<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment Issuance and Return - FTMK Borrow System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <style>
        table {
            width: 80%;
        }

        header {
            background-color: #ffcc00;
        }

        section table {
            margin-left: auto;
            margin-right: auto;
            margin-top: 50px;
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
    </style>
</head>

<body>
    <header>
        <table>
            <tr>
                <td><a href="technicianMainPage.php"><img src="../0images/ftmkLogo_Yellow.png" height="80px"></a></td>
                <td>
                    <h1 style="text-align: center;">Equipment Issuance & Return</h1>
                </td>
            </tr>
        </table>
    </header>
    <section>
        <table cellspacing="0">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Borrower's Name</th>
                    <th>Equipment Name</th>
                    <th>Issuance Date</th>
                    <th>Issuance Action</th>
                    <th>Return Date</th>
                    <th>Return Action</th>
                </tr>
            </thead>

            <tbody>
                <?php
                include("../connect.php");

                $stmt = $conn->prepare("SELECT bh.BorrowID, ba.ApplicationID, u.Name AS BorrowerName, e.EquipmentName, bh.ReturnDate, bh.BorrowDate
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
                        echo "<tr>";
                        echo "<td>$no</td>";
                        echo "<td>" . htmlspecialchars($row['BorrowerName']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['EquipmentName']) . "</td>";

                        $borrowDate = $row['BorrowDate'];
                        if ($borrowDate == null || $borrowDate == "") {
                            echo "<td>-</td>";
                            echo "<td><button class='buttonStyle issuance' data-id='" . $row['BorrowID'] . "'><i class='fa fa-check iconStyle'></i></button></td>";
                        } else {
                            echo "<td>" . date('d/m/Y', strtotime($borrowDate)) . "</td>";
                            echo "<td></td>";
                        }

                        $returnDate = $row['ReturnDate'];
                        if ($borrowDate == null || $borrowDate == "") {
                            // Not issued yet, no return
                            echo "<td>-</td><td>-</td>";
                        } else {
                            if ($returnDate == null || $returnDate == "") {
                                echo "<td>-</td><td><button class='buttonStyle returnAction' data-id='" . $row['BorrowID'] . "'><i class='fa fa-check iconStyle'></i></button></td>";
                            } else {
                                echo "<td>" . date('d/m/Y', strtotime($returnDate)) . "</td><td></td>";
                            }
                        }

                        echo "</tr>";
                        $no++;
                    }
                } else {
                    echo "<tr><td colspan='7'>No approved applications found.</td></tr>";
                }
                ?>


            </tbody>
        </table>
    </section>

    <script>
        $(document).ready(function() {
            $(".issuance").click(function() {
                var button = $(this);
                var row = button.closest("tr");
                var confirmIssuance = window.confirm("Are you sure you issued the equipment?");
                if (confirmIssuance) {
                    $.post("updateIssuanceDate.php", {
                        id: button.data("id")
                    }, function(response) {
                        if (response.trim() === "success") {
                            alert("Issuance date updated successfully!");

                            var today = todayDate();
                            row.find("td:eq(3)").text(today);
                            row.find("td:eq(4)").html("");

                            var borrowID = button.data("id");
                            var returnBtn = `
                    <button class='buttonStyle returnAction' data-id='${borrowID}'>
                        <i class='fa fa-check iconStyle'></i>
                    </button>
                `;
                            row.find("td:eq(5)").text("-");
                            row.find("td:eq(6)").html(returnBtn);

                        } else {
                            alert("Failed to update issuance date.");
                        }
                    });
                }
            });


            $(document).on('click', '.returnAction', function() {
                var confirmReturn = confirm("Are you sure the equipment has been returned?");
                if (confirmReturn) {
                    var row = $(this).closest("tr")[0];
                    var today = todayDate();
                    row.cells[5].textContent = today;
                    row.cells[6].textContent = "";

                    var historyId = $(this).data('id');
                    $.post("updateReturnDate.php", {
                        id: historyId
                    }, function(response) {
                        if (response.trim() === "success") {
                            alert("Return date updated successfully!");
                        } else {
                            alert("Failed to update return date.");
                        }
                    });
                }
            });
        });

        function todayDate() {
            var date = new Date();
            var day = date.getDate();
            var month = date.getMonth() + 1;
            var year = date.getFullYear();
            return (day < 10 ? '0' + day : day) + '/' + (month < 10 ? '0' + month : month) + '/' + year;
        }
    </script>
</body>

</html>