<?php
session_start();
include("../connect.php");

$userId = $_SESSION['UserID'] ?? '';

$stmt1 = $conn->prepare("SELECT ba.ApplicationID, ba.ApplyDate, ba.Purpose, ba.Quantity, ba.ActivityDateTime,
                          u.Name as UserName, u.UserID as MatricNo,
                          e.EquipmentID, e.EquipmentName
                   FROM borrow_applications ba
                   JOIN users u ON ba.UserID = u.UserID
                   JOIN equipment e ON ba.EquipmentID = e.EquipmentID
                   WHERE ba.UserID = ?
                   ORDER BY ba.ApplicationID DESC LIMIT 1");
$stmt1->bind_param('s', $userId);
$stmt1->execute();
$appResult = $stmt1->get_result();

$appRow = $appResult->fetch_assoc();


if (!$appRow) {
  echo "<script>
    alert('You have not submitted any borrow application yet.');
    window.location.href = 'lecturerMainPage.php';
  </script>";
  exit;
}
$applicationId = $appRow['ApplicationID'];

$stmt2 = $conn->prepare("SELECT ApproverRole, Status, Remarks 
                FROM approval 
                WHERE ApplicationID = ?");
$stmt2->bind_param('i', $applicationId);
$stmt2->execute();
$approvalResult = $stmt2->get_result();


$statuses = [
  'Admin' => 'Pending',
  'Security Office' => 'Pending'
];

$remarks = [
  'Admin' => '',
  'Security Office' => ''
];

while ($row = $approvalResult->fetch_assoc()) {
  $statuses[$row['ApproverRole']] = $row['Status'];
  $remarks[$row['ApproverRole']] = $row['Remarks'] ?? '';
}

?>



<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Application Status - FTMK Borrow System</title>
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

    .container {
      background-color: white;
      width: 70%;
      margin: 40px auto;
      padding: 30px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .info label {
      display: block;
      margin: 10px 0 5px;
      font-weight: bold;
    }

    .equipment-table {
      width: 100%;
      border-collapse: collapse;
      margin: 20px 0;
    }

    .equipment-table th,
    .equipment-table td {
      border: 1px solid #000;
      padding: 8px;
      text-align: center;
    }

    .status {
      margin: 30px 0;
    }

    .status-item {
      display: flex;
      align-items: center;
      margin-bottom: 15px;
    }

    .status-label {
      width: 180px;
      font-weight: bold;
    }

    .pending {
      background-color: #ccc;
      color: #000;
    }

    .approved {
      background-color: limegreen;
      color: white;
    }

    .rejected {
      background-color: crimson;
      color: white;
    }

    .status-box {
      padding: 5px 15px;
      border-radius: 4px;
      font-weight: bold;
      min-width: 100px;
      text-align: center;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .attention {
      color: red;
      font-weight: bold;
      font-size: 18px;
      margin-bottom: 10px;
    }

    .pickup-box {
      border: 1px solid #000;
      padding: 15px;
    }
  </style>
</head>

<body>

  <header>
    <a href="lecturerMainPage.php">
      <img src="../0images/ftmkLogo_Yellow.png" alt="FTMK Logo" class="logo" />
    </a>
    <h1>Application Status</h1>
  </header>

  <div class="container">
    <div class="info">
      <label>Name: <?php echo htmlspecialchars($appRow['UserName']); ?></label>
      <label>Matric No: <?php echo htmlspecialchars($appRow['MatricNo']); ?></label>
      <label>Application Date: <?php echo htmlspecialchars($appRow['ApplyDate']); ?></label>
      <label>Apply to borrow equipment:</label>
    </div>

    <table class="equipment-table">
      <tr>
        <th>Name</th>
        <th>Purpose</th>
      </tr>
      <tr>
      <tr>
        <td><?php echo htmlspecialchars($appRow['EquipmentName']); ?></td>
        <td><?php echo htmlspecialchars($appRow['Purpose']); ?></td>
      </tr>

      </tr>
    </table>

    <div class="status">
      <?php
      function getStatusClass($status)
      {
        $status = strtolower($status);
        if ($status === 'approved') return 'approved';
        if ($status === 'rejected') return 'rejected';
        return 'pending';
      }
      ?>

      <div class="status-item">
        <div class="status-label">Admin approval</div>
        <div class="status-box <?php echo getStatusClass($statuses['Admin']); ?>">
          <?php echo htmlspecialchars($statuses['Admin']); ?>
        </div>
      </div>
      <div class="status-item">
        <div class="status-label">Security Office approval</div>
        <div class="status-box <?php echo getStatusClass($statuses['Security Office']); ?>">
          <?php echo htmlspecialchars($statuses['Security Office']); ?>
        </div>
      </div>

    </div>




    <div class="attention">Attention !!!</div>

    <div class="pickup-box">
      <?php
      if (
        $statuses['Admin'] === 'Approved' && $statuses['Security Office'] === 'Approved'
      ) {
        echo "The device is approved to be picked up at the lab counter.";
      } else if (
        $statuses['Admin'] === 'Rejected' || $statuses['Security Office'] === 'Rejected'
      ) {
        echo "Your application has been rejected.<br>";



        if ($statuses['Admin'] === 'Rejected') {
          echo "<strong>Admin's reason:</strong> " . htmlspecialchars($remarks['Admin']) . "<br>";
        }
        if ($statuses['Security Office'] === 'Rejected') {
          echo "<strong>Admin's reason:</strong> " . htmlspecialchars($remarks['Security Office']) . "<br>";
        }
      } else {
        echo "Your application is still pending approval.";
      }
      ?>
    </div>

  </div>
</body>

</html>