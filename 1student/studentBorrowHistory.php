<?php
include("../connect.php");
session_start();

$userId = $_SESSION['UserID'];

$stmt1 = $conn->prepare("SELECT bh.*, e.EquipmentName, e.ModelNumber,s.* ,ba.*
        FROM borrow_history bh
        JOIN borrow_applications ba ON bh.ApplicationID = ba.ApplicationID
        JOIN equipment e ON ba.EquipmentID = e.EquipmentID
        JOIN users s ON ba.UserID = s.UserID
        WHERE s.UserID = ?
        ORDER BY bh.BorrowID DESC");
$stmt1->bind_param('s', $userId);
$stmt1->execute();
$result = $stmt1->get_result();

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

    .pickup-alert {
      color: green;
      font-weight: bold;
      background-color: #e6ffe6;
      padding: 5px 10px;
      border-radius: 8px;
      display: inline-block;
    }

    .no-data-row td {
      font-style: italic;
      color: #555;
      background-color: #f0f0f0;
    }
  </style>
</head>

<body>
  <header>
    <a href="studentMainPage.php"> <img src="../0images/ftmkLogo_Yellow.png" alt="FTMK Logo" class="logo" /></a>
    <h1>Borrow History</h1>
  </header>

  <div id="filter">
    <table id="filterTable" class="filterTable">
      <td><label for="filter">Filter </label>
        <select id="color" size="1">
          <option value="all">All</option>
          <option value="current">Current</option>
          <option value="past">Past</option>
        </select>
      </td>
    </table>
  </div>

  <table id="borrowTable" class="borrowTable">
    <thead>
      <tr>
        <th>No</th>
        <th>Equipment ID</th>
        <th>Equipment Name</th>
        <th>Model Number</th>
        <th>Borrow Date</th>
        <th>Due Date</th>
        <th>Return Date</th>
      </tr>
    </thead>
    <?php
    $rows = [];
    while ($row = $result->fetch_assoc()) {
      $rows[] = $row;
    }
    ?>

    <tbody>
      <?php if (empty($rows)) { ?>
        <tr class="no-data-row">
          <td colspan="7" style="text-align: center; font-style: italic; color: #555; background-color: #f0f0f0;">
            No borrow records found.
          </td>
        </tr>
      <?php } else { ?>
        <?php foreach ($rows as $row) { ?>
          <tr>
            <td><?php echo $no++; ?></td>
            <td><?php echo htmlspecialchars($row['EquipmentID']); ?></td>
            <td><?php echo htmlspecialchars($row['EquipmentName']); ?></td>
            <td><?php echo htmlspecialchars($row['ModelNumber']); ?></td>
            <td>
              <?php
              if ($row['BorrowDate']) {
                echo htmlspecialchars($row['BorrowDate']);
              } else {
                echo "<span class='pickup-alert'>Ready for pickup</span>";
              }
              ?>
            </td>
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
                if (!empty($dueDate) && $returnDate > $dueDate) {
                  echo "<span style='color: red; font-weight: bold;'>" . htmlspecialchars($returnDate) . "</span>";
                } else {
                  echo htmlspecialchars($returnDate);
                }
              }
              ?>
            </td>
          </tr>
        <?php } ?>
      <?php } ?>
    </tbody>

  </table>

  <script>
    const filterSelect = document.getElementById("color");
    const table = document.getElementById("borrowTable");
    const tbody = table.querySelector("tbody");

    filterSelect.addEventListener("change", function() {
      const filterValue = this.value;
      let visibleCount = 0;

      // Remove old no-data row if exists
      const oldNoData = document.querySelector(".no-data-row");
      if (oldNoData) oldNoData.remove();

      // Filter rows
      for (let i = 0; i < tbody.rows.length; i++) {
        const row = tbody.rows[i];
        const returnDate = row.cells[6].textContent.trim();

        let show = false;
        if (filterValue === "all") {
          show = true;
        } else if (filterValue === "current") {
          show = (returnDate === "-" || returnDate === "" || returnDate === 'Late');
        } else if (filterValue === "past") {
          show = (returnDate !== "-" && returnDate !== "" && returnDate !== 'Late');
        }

        row.style.display = show ? "" : "none";
        if (show) visibleCount++;
      }

      // If no visible rows, show message
      if (visibleCount === 0) {
        const noDataRow = document.createElement("tr");
        noDataRow.classList.add("no-data-row");

        const td = document.createElement("td");
        td.colSpan = 7;
        td.textContent = "No records found for selected filter.";
        td.style.textAlign = "center";

        noDataRow.appendChild(td);
        tbody.appendChild(noDataRow);
      }
    });
  </script>
</body>

</html>