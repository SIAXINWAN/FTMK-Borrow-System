<?php
session_start();
include("../connect.php");

$stmt = $conn->prepare("SELECT EquipmentID, EquipmentName, ModelNumber, Description, Date, Quantity, AvailabilityStatus FROM equipment");
$stmt->execute();
$result = $stmt->get_result();


$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Equipment Inventory</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <style>
        body {
            font-family: Arial;
        }

        header {
            background-color: #ffcc00;
            padding: 10px;
        }

        header table {
            width: 100%;
        }

        header h1 {
            margin: 0;
            text-align: center;
        }

        #filterEquipment {
            width: 90%;
            margin: 20px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        #searchInput {
            width: 70%;
            padding: 5px;
        }

        select {
            padding: 5px;
        }

        table {
            width: 90%;
            margin: auto;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #333;
            color: white;
        }

        .buttonStatus {
            background-color: #ffcc00;
            border-radius: 10px;
            cursor: pointer;
            padding: 5px 10px;
        }

        .btnTrash {
            background-color: red;
            color: white;
            border: none;
            cursor: pointer;
            padding: 10px;
        }

        .btnUpdate {
            background-color: #ccc;
            border: none;
            cursor: pointer;
            padding: 10px;
        }

        .header {
            background-color: #ffcc00;
            padding: 15px 30px;
            display: flex;
            align-items: center;
        }

        .logo {
            height: 70px;
            margin-right: 20px;
        }

        .header h1 {
            flex: 1;
            text-align: center;
            color: #000;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        <a href="adminMainPage.php"><img src="../0images/ftmkLogo_Yellow.png" class="logo"></a>
        <h1>Equipment Inventory</h1>
    </div>


    <div id="filterEquipment">
        <input type="text" id="searchInput" placeholder="Search by ID or Name">
        <select id="filter">
            <option value="all">All</option>
            <option value="available">Available</option>
            <option value="not available">Not Available</option>
        </select>
    </div>

    <?php if (!empty($success)): ?>
        <div style="color: green; text-align: center; margin-bottom: 15px;">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Equipment ID</th>
                <th>Equipment Name</th>
                <th>Model Number</th>
                <th>Description</th>
                <th>Date</th>
                <th>Quantity</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1;
            while ($row = $result->fetch_assoc()):
                $statusText = $row['AvailabilityStatus'] == 1 ? "Available" : "Not Available";
                $statusColor = $row['AvailabilityStatus'] == 1 ? "#58FF05" : "red"; ?>
                <tr>
                    <td></td>
                    <td><?= htmlspecialchars($row['EquipmentID']) ?></td>
                    <td><?= htmlspecialchars($row['EquipmentName']) ?></td>
                    <td><?= htmlspecialchars($row['ModelNumber']) ?></td>
                    <td><?= htmlspecialchars($row['Description']) ?></td>
                    <td><?= htmlspecialchars($row['Date']) ?></td>
                    <td><?= htmlspecialchars($row['Quantity']) ?></td>
                    <td>
                        <h3 style="color: <?= $statusColor ?>; margin: 5px;"><?= $statusText ?></h3>
                        <button class="buttonStatus">Change Status</button>
                    </td>
                    <td>
                        <button class="btnTrash"><i class="fa fa-trash"></i></button>
                        <a href="adminEquimentUpdate.php?id=<?= urlencode($row['EquipmentID']) ?>">
                            <button class="btnUpdate"><i class="fa fa-refresh"></i></button>
                        </a>

                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <script>
        $(function() {
            function numbering() {
                $("tbody tr:visible").each(function(i) {
                    $(this).find("td:first").text(i + 1);
                });
            }

            function filterAndSearch() {
                const filter = $("#filter").val();
                const keyword = $("#searchInput").val().toLowerCase();

                $("tbody tr").each(function() {
                    const status = $(this).find("td:eq(7) h3").text().toLowerCase();
                    const id = $(this).find("td:eq(1)").text().toLowerCase();
                    const name = $(this).find("td:eq(2)").text().toLowerCase();

                    const matchSearch = id.includes(keyword) || name.includes(keyword);
                    const matchFilter = (filter === "all") ||
                        (filter === "available" && status === "available") ||
                        (filter === "not available" && status === "not available");

                    $(this).toggle(matchSearch && matchFilter);
                });

                numbering();
            }

            $("#searchInput").on("input", filterAndSearch);
            $("#filter").on("change", filterAndSearch);

            $(".buttonStatus").on("click", function() {
                const row = $(this).closest("tr");
                const h3 = row.find("td:eq(7) h3");
                const currentStatus = h3.text();
                const id = row.find("td:eq(1)").text();
                const name = row.find("td:eq(2)").text();
                const model = row.find("td:eq(3)").text();

                const newStatus = currentStatus === "Available" ? 0 : 1;
                const confirmText = currentStatus === "Available" ?
                    `Are you sure you want to set ${name} ${model} to NOT Available?` :
                    `Are you sure you want to set ${name} ${model} to Available?`;

                if (confirm(confirmText)) {
                    $.post("../Equipment/changeStatus.php", {
                        id: id,
                        status: newStatus
                    }, function(res) {
                        if (res === "success") {
                            const newText = newStatus === 1 ? "Available" : "Not Available";
                            const newColor = newStatus === 1 ? "#58FF05" : "red";
                            h3.text(newText).css("color", newColor);
                        } else {
                            alert("Failed to update status.");
                        }
                    });
                }
            });

            $(".btnTrash").on("click", function() {
                const row = $(this).closest("tr");
                const id = row.find("td:eq(1)").text();

                if (confirm("Are you sure you want to DELETE this equipment?")) {
                    $.post("../Equipment/deleteEquipment.php", {
                        id: id
                    }, function(res) {
                        if (res === "success") {
                            row.remove();
                            numbering();
                        } else {
                            alert("Failed to delete equipment.");
                        }
                    });
                }
            });

            numbering();
        });
    </script>

</body>

</html>