<?php
include("../connect.php");
session_start();

$userId = $_SESSION['UserID'];

$stmt1 = $conn->prepare( "SELECT bh.*, e.EquipmentName, e.ModelNumber 
        FROM borrow_history bh
        JOIN equipment e ON bh.EquipmentID = e.EquipmentID
        WHERE bh.UserID = ?
        ORDER BY bh.BorrowID DESC");
$stmt1->bind_param('s',$userId);
$stmt1->execute();
$result= $stmt1->get_result();



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
    <tr>
      <th>No</th>
      <th>Equipment ID</th>
      <th>Equipment Name</th>
      <th>Model Number</th>
      <th>Borrow Date</th>
      <th>Due Date</th>
      <th>Return Date</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()) { ?>
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
        <td><?php echo $row['ReturnDate'] ? htmlspecialchars($row['ReturnDate']) : '-'; ?></td>

      </tr>
    <?php } ?>
  </table>
  <script>
    const filterSelect = document.getElementById("color");
    const table = document.getElementById("borrowTable");

    filterSelect.addEventListener("change", function() {
      const filterValue = this.value;

      for (let i = 1; i < table.rows.length; i++) {
        const row = table.rows[i];
        const returnDateCell = row.cells[6];
        const returnDate = returnDateCell.textContent.trim();

        if (filterValue === "all") {
          row.style.display = "";
        } else if (filterValue === "current") {
          row.style.display = (returnDate === "-" || returnDate === "") ? "" : "none";
        } else if (filterValue === "past") {
          row.style.display = (returnDate !== "-" && returnDate !== "") ? "" : "none";
        }
      }
    });
  </script>
</body>

</html>