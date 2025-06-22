<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrow Application Approval - FTMK Borrow System</title>
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
            clear: both;
            margin-left: auto;
            margin-right: auto;
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
        }

        .tick {
            background-color: greenyellow;
        }

        .cross {
            background-color: red;
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

        #filterTable tr {
            display: flex;
            flex-direction: row;
            border: 0;
            justify-content: flex-end;
            align-items: center;
        }

        #filterTable td {
            margin: 10px;
        }

        .buttonBox {
            display: flex;
        }
    </style>
</head>

<body>
    <header>
        <table>
            <tr>
                <td><a href="adminMainPage.php"><img src="../0images/ftmkLogo_Yellow.png" height="80px"></a></td>
                <td>
                    <h1 style="text-align: center;">Borrow Application Approval</h1>
                </td>
            </tr>
        </table>
    </header>
    <center>
        <form id="filterForm" method="get" action="">
            <table id="filterTable">
                <tr>
                    <td>
                        <h3>Filter </h3>
                    </td>
                    <td>
                        <select id="filter" name="filter">
                            <option value="all">All</option>
                            <option value="lecturer">Lecturer</option>
                            <option value="student">Student</option>
                        </select>
                    </td>
                </tr>
            </table>
        </form>
    </center>
    <section>
        <table cellspacing="0">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Borrower's Name</th>
                    <th>Role</th>
                    <th>Equipment Name</th>
                    <th>Quantity</th>
                    <th>Reason</th>
                    <th>Approval</th>
                </tr>
            </thead>
            <tbody>
                <?php
                include("../connect.php");
                session_start();

                $adminId = $_SESSION['UserID'];
                $filter = $_GET['filter'] ?? 'all';

                $role = null;
                if ($filter === 'lecturer') {
                    $role = 'Lecturer';
                } elseif ($filter === 'student') {
                    $role = 'Student';
                }

                if ($role) {
                    $sql = "SELECT 
                        a.ApplicationID, 
                        u.Name AS BorrowerName, 
                        u.Role,
                        e.EquipmentName, 
                        a.Purpose, a.Quantity
                    FROM borrow_applications a
                    JOIN users u ON a.UserID = u.UserID
                    JOIN equipment e ON a.EquipmentID = e.EquipmentID
                    JOIN approval ap ON a.ApplicationID = ap.ApplicationID
                    WHERE ap.ApproverRole = 'Admin' 
                    AND ap.Status = 'Pending'
                    AND u.Role = ?
                    AND (
                        u.Role = 'Lecturer' OR
                        EXISTS (
                            SELECT 1 FROM approval 
                            WHERE ApplicationID = a.ApplicationID 
                            AND ApproverRole = 'Lecturer' 
                            AND Status = 'Approved'
                        )
                    )";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("s", $role);
                } else {
                    $sql = "SELECT 
                        a.ApplicationID, 
                        u.Name AS BorrowerName, 
                        u.Role,
                        e.EquipmentName, 
                        a.Purpose, a.Quantity
                    FROM borrow_applications a
                    JOIN users u ON a.UserID = u.UserID
                    JOIN equipment e ON a.EquipmentID = e.EquipmentID
                    JOIN approval ap ON a.ApplicationID = ap.ApplicationID
                    WHERE ap.ApproverRole = 'Admin' 
                    AND ap.Status = 'Pending'
                    AND (
                        u.Role = 'Lecturer' OR
                        EXISTS (
                            SELECT 1 FROM approval 
                            WHERE ApplicationID = a.ApplicationID 
                            AND ApproverRole = 'Lecturer' 
                            AND Status = 'Approved'
                        )
                    )";
                    $stmt = $conn->prepare($sql);
                }

                $stmt->execute();
                $result = $stmt->get_result();

                $counter = 1;

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr data-app-id='{$row['ApplicationID']}'>";
                        echo "<td>" . $counter++ . "</td>";
                        echo "<td>" . htmlspecialchars($row['BorrowerName']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Role']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['EquipmentName']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Quantity']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Purpose']) . "</td>";
                        echo "<td>
                            <div class='buttonBox'>
                                <button class='buttonStyle approve'><i class='fa fa-check iconStyle tick'></i></button>
                                <button class='buttonStyle reject'><i class='fa fa-times iconStyle cross'></i></button>
                            </div>
                        </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No pending applications.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </section>

    <script>
        $(document).ready(function() {
            $("#filter").on("change", function() {
                $("#filterForm").submit();
            });

            const currentFilter = "<?php echo $filter; ?>";
            $("#filter").val(currentFilter);

            $(".approve").click(function() {
                let tr = $(this).closest("tr");
                let appId = tr.data("app-id");
                let studentName = tr.find("td:nth-child(2)").text();

                if (confirm("Are you sure you want to APPROVE " + studentName + "'s application?")) {
                    $.post("adminApproval.php", {
                        action: "approve",
                        appId: appId
                    }, function(response) {
                        console.log("Server response:", response); // DEBUG 看清楚回应
                        if (response.trim() === "success") { 
                            tr.remove();
                            numbering();
                        } else {
                            alert("Approval failed: " + response); 
                        }
                    });

                }
            });

            $(".reject").click(function() {
                let tr = $(this).closest("tr");
                let appId = tr.data("app-id");
                let studentName = tr.find("td:nth-child(2)").text();

                let reason = prompt("Please enter a reason for rejecting " + studentName + "'s application:");

                if (reason === null || reason.trim() === "") {
                    alert("Rejection reason is required.");
                    return;
                }

                $.post("adminApproval.php", {
                    action: "reject",
                    appId: appId,
                    remarks: reason
                }, function(response) {
                    if (response === "success") {
                        tr.remove();
                        numbering();
                    } else {
                        alert("Rejection failed.");
                    }
                });
            });

        });

        function numbering() {
            var tbody = document.querySelector("section table tbody");
            var rows = Array.from(tbody.querySelectorAll("tr"));

            var counter = 1;
            rows.forEach(element => {
                element.cells[0].textContent = counter;
                counter += 1;
            });
        }
    </script>
</body>

</html>