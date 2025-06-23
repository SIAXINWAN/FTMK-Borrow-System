<?php
session_start();
include("../connect.php");

$stmt = $conn->prepare("SELECT 
            sa.*, sl.Description,
            tech.Name AS TechnicianName, 
            comp.Name AS CompanyName, 
            e.EquipmentName 
        FROM service_approval sa
        JOIN servicelog sl ON sa.ServiceID = sl.ServiceID
        JOIN equipment e ON sl.EquipmentID = e.EquipmentID
        JOIN users comp ON sl.CompanyID = comp.UserID
        JOIN users tech ON sl.RequesterID = tech.UserID
        WHERE sa.Decision = 'Pending'");

$stmt->execute();
$result = $stmt->get_result();

$no = 1;


?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Service Request Approval - FTMK Borrow System</title>
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <style>
    table {
      width: 80%;
    }

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

    section table {
      margin-left: auto;
      margin-right: auto;
      margin-top: 100px;
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
      padding: 10px;
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

    .buttonBox {
      display: flex;
    }

    /* Loading Overlay Styling */
    #loadingOverlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 9999;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .spinner-container {
      text-align: center;
      color: white;
      font-size: 20px;
    }

    .spinner {
      border: 6px solid #f3f3f3;
      border-top: 6px solid #ffcc00;
      border-radius: 50%;
      width: 50px;
      height: 50px;
      margin: 0 auto 15px;
      animation: spin 1s linear infinite;
    }

    .spinner-text {
      font-weight: bold;
      letter-spacing: 1px;
    }

    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
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

    <a href="adminMainPage.php"><img src="../0images/ftmkLogo_Yellow.png" width="" height="80px" /></a>

    <h1 style="text-align: center">Service Request Approval</h1>

  </header>
  <section>
    <table cellspacing="0">
      <thead>
        <tr>
          <th>No</th>
          <th>Equipment Name</th>
          <th>Technician Name</th>
          <th>Company's Name</th>
          <th>Service Reason</th>
          <th>Approval</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if ($result && $result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $no++ . "</td>";
            echo "<td>" . htmlspecialchars($row['EquipmentName']) . "</td>";
            echo "<td>" . htmlspecialchars($row['TechnicianName']) . "</td>";
            echo "<td>" . htmlspecialchars($row['CompanyName']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Description']) . "</td>";
            echo "<td>
  <div class='buttonBox'>
    <button class='buttonStyle approveBtn' data-approval-id='{$row['ApprovalID']}'>
      <i class='fa fa-check iconStyle tick'></i>
    </button>
    <button class='buttonStyle rejectBtn' data-approval-id='{$row['ApprovalID']}'>
      <i class='fa fa-times iconStyle cross'></i>
    </button>
  </div>
</td>";


            echo "</tr>";
          }
        } else {
          echo "<tr class='no-data-row'><td colspan='7'>No pending applications.</td></tr>";
        }

        ?>

      </tbody>
    </table>
  </section>

  <script>
    $(document).ready(function() {
      const approverId = "<?php echo $_SESSION['UserID']; ?>";

      function showLoading() {
        $("#loadingOverlay").fadeIn(200);
      }

      function hideLoading() {
        $("#loadingOverlay").fadeOut(200);
      }

      $(".approveBtn").click(function() {
        const approvalId = $(this).data("approval-id");

        if (confirm("Are you sure you want to APPROVE this service request?")) {
          showLoading();
          $.post("processApproval.php", {
            approvalId: approvalId,
            decision: "Approved",
            approverId: approverId
          }, function(response) {
            hideLoading();
            if (response === "success") {
              alert("Approved successfully.");
              location.reload();
            } else {
              alert("Failed to approve.");
            }
          });
        }
      });

      $(".rejectBtn").click(function() {
        const approvalId = $(this).data("approval-id");

        const remarks = prompt("Please provide a reason for REJECTION:");
        if (remarks !== null && remarks.trim() !== "") {
          showLoading();
          $.post("processApproval.php", {
            approvalId: approvalId,
            decision: "Rejected",
            approverId: approverId,
            remarks: remarks
          }, function(response) {
            hideLoading();
            if (response === "success") {
              alert("Rejected successfully.");
              location.reload();
            } else {
              alert("Failed to reject.");
            }
          });
        } else {
          alert("Rejection cancelled. Remarks are required.");
        }
      });
    });
  </script>
  <div id="loadingOverlay" style="display: none;">
    <div class="spinner-container">
      <div class="spinner"></div>
      <div class="spinner-text">Processing...</div>
    </div>
  </div>


</body>

</html>