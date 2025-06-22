<?php
include("../connect.php");
session_start();
$officerId = $_SESSION['UserID'];
$filter = $_GET['filter'] ?? 'all';

$sql = "SELECT 
    a.ApplicationID, 
    u.Name AS BorrowerName, 
    u.Role,
    e.EquipmentName, 
    a.Purpose,
    a.Quantity
FROM borrow_applications a
JOIN users u ON a.UserID = u.UserID
JOIN equipment e ON a.EquipmentID = e.EquipmentID
JOIN approval sec ON a.ApplicationID = sec.ApplicationID
JOIN approval adm ON a.ApplicationID = adm.ApplicationID
WHERE sec.ApproverRole = 'Security Office'
AND sec.Status = 'Pending'
AND adm.ApproverRole = 'Admin'
AND adm.Status = 'Approved'";

$params = [];
$types = "";

if ($filter === 'lecturer') {
    $sql .= " AND u.Role = ?";
    $params[] = 'Lecturer';
    $types .= "s";
} elseif ($filter === 'student') {
    $sql .= " AND u.Role = ?";
    $params[] = 'Student';
    $types .= "s";
}

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Security Office Approval - FTMK Borrow System</title>
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
            margin: auto;
            border-collapse: collapse;
        }

        section th,
        section td {
            text-align: center;
            padding: 8px;
            border: 1.5px solid black;
        }

        section th {
            background-color: rgb(53, 52, 52);
            color: white;
        }

        .buttonBox {
            display: flex;
            justify-content: center;
            gap: 10px;
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
            background-color: green;
        }

        .cross {
            background-color: red;
        }

        .buttonStyle {
            height: 50px;
            width: 50px;
            border: none;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #filterTable tr {
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }

        #filterTable td {
            margin: 10px;
        }
    </style>
</head>

<body>
    <header>
        <table>
            <tr>
                <td><a href="securityOfficeMainPage.php"><img src="../0images/ftmkLogo_Yellow.png" height="80px"></a></td>
                <td>
                    <h1 style="text-align: center;">Security Office Borrow Approvals</h1>
                </td>
            </tr>
        </table>
    </header>

    <center>
        <form id="filterForm" method="get" action="">
            <table id="filterTable">
                <tr>
                    <td>
                        <h3>Filter</h3>
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
        <table>
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

            $("#filter").val("<?php echo $filter; ?>");

            $(".approve").click(function() {
                const tr = $(this).closest("tr");
                const appId = tr.data("app-id");
                const name = tr.find("td:nth-child(2)").text();

                if (confirm("Are you sure you want to APPROVE " + name + "'s application?")) {
                    $.post("securityApproval.php", {
                        action: "approve",
                        appId: appId
                    }, function(response) {
                        if (response === "success") {
                            tr.remove();
                            renumber();
                        } else {
                            alert("Approval failed.");
                        }
                    });
                }
            });

            $(".reject").click(function() {
                const tr = $(this).closest("tr");
                const appId = tr.data("app-id");
                const name = tr.find("td:nth-child(2)").text();
                const reason = prompt("Enter a reason to reject " + name + "'s application:");

                if (reason === null || reason.trim() === "") {
                    alert("Rejection reason is required.");
                    return;
                }

                $.post("securityApproval.php", {
                    action: "reject",
                    appId: appId,
                    remarks: reason
                }, function(response) {
                    if (response === "success") {
                        tr.remove();
                        renumber();
                    } else {
                        alert("Rejection failed.");
                    }
                });
            });

            function renumber() {
                $("section table tbody tr").each(function(index) {
                    $(this).find("td:first").text(index + 1);
                });
            }
        });
    </script>
</body>

</html>