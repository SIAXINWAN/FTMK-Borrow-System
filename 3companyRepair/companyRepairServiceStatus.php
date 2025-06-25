<?php
include("../connect.php");

if (isset($_GET['serviceID'])) {
  $serviceID = $_GET['serviceID'];

  $stmt1 = $conn->prepare("SELECT sl.*, e.EquipmentName 
                         FROM servicelog sl 
                         JOIN equipment e ON sl.EquipmentID = e.EquipmentID 
                         WHERE sl.ServiceID = ?");
  $stmt1->bind_param("s", $serviceID);
  $stmt1->execute();
  $result = $stmt1->get_result();
  $data = $result->fetch_assoc();
  $stmt1->close();

  $stmt2 = $conn->prepare("SELECT * FROM service_history WHERE ServiceID = ?");
  $stmt2->bind_param("s", $serviceID);
  $stmt2->execute();
  $historyResult = $stmt2->get_result();
  $history = $historyResult->fetch_assoc();
  $stmt2->close();


  $acceptDate = $history['AcceptDate'] ?? null;
  $actionTaken = $history['ActionTaken'] ?? null;
  $status = $history['Status'] ?? null;
  $note = $history['Note'] ?? null;
  $returnDate = $history['ReturnDate'] ?? null;
  $receivedReturn = $history['ReceivedReturn'] ?? null;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Service Status - FTMK Borrow System</title>
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

    .status-item {
      display: flex;
      align-items: center;
      padding-bottom: 20px;
    }

    .status-label {
      width: 320px;
    }

    .note-box {
      border: 1px solid #000;
      padding: 15px;
      margin-top: 10px;
      width: 100%;
    }

    .status-button {
      padding: 5px 15px;
      color: black;
      border-radius: 4px;
      font-weight: bold;
      min-width: 100px;
      text-align: center;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
      border: none;
    }

    .status-approved {
      background-color: limegreen;
    }

    .status-pending {
      background-color: gray;
    }

    .status-failed {
      background-color: orangered;
    }

    .text {
      font-size: 18px;
      margin: 20px 0 10px;
    }

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

    .green-text {
      color: green;
      font-weight: bold;
    }

    .red-text {
      color: red;
      font-weight: bold;
    }
  </style>
</head>

<body>
  <header>
    <a href="companyRepairServiceRequestList.php">
      <img src="../0images/ftmkLogo_Yellow.png" alt="FTMK Logo" class="logo" />
    </a>
    <h1>Service Status</h1>
  </header>

  <div class="container">
    <div class="text">Equipment Details:</div>
    <table class="equipment-table">
      <tr>
        <th>Name</th>
        <th>Description</th>
      </tr>
      <tr>
        <td><?php echo $data['EquipmentName']; ?></td>
        <td><?php echo $data['Description']; ?></td>
      </tr>
    </table>

    <div class="status-item">
      <div class="status-label">Service Request Acceptance</div>
      <?php if (!$acceptDate): ?>
        <form method="POST" action="updateAcceptDate.php">
          <input type="datetime-local" id="date" name="acceptDate" required style="margin-right: 10px;">

          <script>
            const dateInput = document.getElementById('date');
            const now = new Date();

            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');

            const minDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
            dateInput.min = minDateTime;
          </script>
          <input type="hidden" name="serviceID" value="<?php echo $serviceID; ?>">
          <button type="submit" class="status-button status-approved">Confirm</button>


        </form>
      <?php else: ?>
        <label class="green-text">Confirmed</label>




      <?php endif; ?>
    </div>

    <div class="status-item">
      <div class="status-label">Pickup Equipment</div>
      <?php if ($acceptDate): ?>
        <h4 style="padding-right: 8px;"><?php echo date("Y-m-d H:i", strtotime($acceptDate)); ?></h4>
        <?php if ($actionTaken !== 'Done'): ?>
          <form method="POST" action="updatePickup.php">
            <input type="hidden" name="serviceID" value="<?php echo $serviceID; ?>">
            <button type="submit" onclick="return confirm('Confirm pickup done?');" class="status-button status-approved">Done</button>
          </form>
        <?php else: ?>
          <label class="green-text">Done</label>

        <?php endif; ?>
      <?php else: ?>
        <button class="status-button status-pending" disabled>Pending Accept</button>
      <?php endif; ?>
    </div>

    <div class="status-item" style="flex-direction: column; align-items: flex-start;">

      <div class="status-item" style="flex-direction: column; align-items: flex-start;">
        <?php if ($status === 'Pending'): ?>

          <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; margin-bottom: 10px;">
            <div class="status-label">Equipment Service & Repair Status</div>
            <div style="display: flex; gap: 10px;">
              <form method="POST" action="updateRepairStatus.php" onsubmit="return validateStatusSelection(this);">
                <input type="hidden" name="serviceID" value="<?php echo $serviceID; ?>">
                <input type="hidden" name="status" id="statusInputCompleted" value="">
                <input type="hidden" name="note" id="noteInputCompleted">
                <?php
                $completedBtnClass = ($actionTaken !== 'Done') ? 'status-button status-pending' : 'status-button status-approved';
                ?>
                <button type="button"
                  onclick="chooseStatus('Completed', this)"
                  class="<?php echo $completedBtnClass; ?>"
                  <?php echo ($actionTaken !== 'Done') ? 'disabled' : ''; ?>>
                  Complete
                </button>

              </form>

              <form method="POST" action="updateRepairStatus.php" onsubmit="return validateStatusSelection(this);">
                <input type="hidden" name="serviceID" value="<?php echo $serviceID; ?>">
                <input type="hidden" name="status" id="statusInputIncomplete" value="">
                <input type="hidden" name="note" id="noteInputIncomplete">
                <?php
                $incompleteBtnClass = ($actionTaken !== 'Done') ? 'status-button status-pending' : 'status-button status-failed';
                ?>
                <button type="button"
                  onclick="chooseStatus('Incomplete', this)"
                  class="<?php echo $incompleteBtnClass; ?>"
                  <?php echo ($actionTaken !== 'Done') ? 'disabled' : ''; ?>>
                  Incomplete
                </button>

              </form>
            </div>
          </div>

          <label class="text" for="repairNote">Notes:</label>
          <input type="text"
            name="note"
            id="repairNote"
            placeholder="Enter Note (required)"
            required
            style="width: 100%; padding: 10px; font-size: 16px;"
            <?php echo ($actionTaken !== 'Done') ? 'disabled' : ''; ?>>

          <script>
            function chooseStatus(statusValue, button) {
              const note = document.getElementById("repairNote").value.trim();
              if (!note) {
                alert("Note is required before selecting status.");
                return;
              }

              if (confirm("Confirm status: " + statusValue + "?")) {
                if (statusValue === 'Completed') {
                  document.getElementById("statusInputCompleted").value = statusValue;
                  document.getElementById("noteInputCompleted").value = note;
                } else if (statusValue === 'Incomplete') {
                  document.getElementById("statusInputIncomplete").value = statusValue;
                  document.getElementById("noteInputIncomplete").value = note;
                }

                button.closest("form").submit();
              }
            }

            function validateStatusSelection(form) {
              return !!form.status.value && !!form.note.value.trim();
            }
          </script>

        <?php elseif ($status && $status !== 'Pending'): ?>
          <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 10px;">
            <div class="status-label">Equipment Service & Repair Status</div>
            <?php
            $labelClass = ($status === 'Completed') ? 'green-text' : 'red-text';
            ?>
            <label class="<?php echo $labelClass; ?>" style="font-weight: bold;">
              <?php echo htmlspecialchars($status); ?>
            </label>

          </div>

          <div class="text" style="margin-top: 15px;">Notes:</div>
          <div class="note-box" style="width: 100%; min-height: 50px; padding: 10px; border: 1px solid #ccc; border-radius: 8px; background-color: #f9f9f9; font-size: 16px;">
            <?php echo $note ? nl2br(htmlspecialchars($note)) : '<span style="color:#999;">No note provided.</span>'; ?>
          </div>

        <?php else: ?>
          <button class="status-button status-pending" disabled>Waiting Pickup</button>
        <?php endif; ?>
      </div>
    </div>







    <div class="text">Return Equipment:</div>
    <table class="equipment-table">
      <tr>
        <td>Company Repair</td>
        <td>
          <?php if ($status !== 'Pending' && !$returnDate): ?>
            <form method="POST" action="updateReturnDate.php">
              <input type="hidden" name="serviceID" value="<?php echo $serviceID; ?>">
              <button type="submit" class="status-button status-approved">Done</button>

            </form>
          <?php elseif ($returnDate): ?>
            <label class="green-text">Done</label>

          <?php else: ?>
            <button class="status-button status-pending" disabled>Pending</button>
          <?php endif; ?>
        </td>
      </tr>

      <tr>
        <td>FTMK</td>
        <td>
          <button class="status-button <?php echo ($receivedReturn === 'Done') ? 'status-approved' : 'status-pending'; ?>" disabled>
            <?php echo ($receivedReturn === 'Done') ? 'Received' : 'Pending'; ?>
          </button>
        </td>
      </tr>
    </table>
  </div>

  <div id="loadingOverlay" style="display: none;">
    <div class="spinner-container">
      <div class="spinner"></div>
      <div class="spinner-text">Processing...</div>
    </div>
  </div>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const targetForms = ["updateAcceptDate.php", "updateReturnDate.php"];

      document.querySelectorAll("form").forEach(form => {
        form.addEventListener("submit", function(e) {
          const action = form.getAttribute("action");
          if (targetForms.includes(action)) {
            const confirmed = confirm("Are you sure to proceed?");
            if (!confirmed) {
              e.preventDefault();
              return;
            }
            showLoading();
          }
        });
      });
    });

    function showLoading() {
      document.getElementById("loadingOverlay").style.display = "flex";
    }
  </script>


</body>

</html>