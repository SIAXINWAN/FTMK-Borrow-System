<?php
session_start();

include("../connect.php");
$equipmentID = $_GET['id'];

$stmt1 = $conn->prepare("SELECT * FROM equipment WHERE EquipmentID = ?");
$stmt1->bind_param("s", $equipmentID);
$stmt1->execute();
$eresult = $stmt1->get_result();
$stmt1->close();

$stmt2 = $conn->prepare("SELECT * FROM users WHERE Role = ?");
$role = 'Company Repair';
$stmt2->bind_param("s", $role);
$stmt2->execute();
$companyResult = $stmt2->get_result();
$stmt2->close();


$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);

?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Service Request - FTMK Borrow System</title>
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

    .form-container {
      background: white;
      max-width: 800px;
      margin: 30px auto;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    h2 {
      margin-top: 0;
      font-size: 20px;
      color: #333;
    }

    .form-section {
      display: flex;
      justify-content: space-between;
      margin-bottom: 20px;
    }

    .form-group {
      flex: 1;
      margin-right: 20px;
    }

    .form-group:last-child {
      margin-right: 0;
    }

    label {
      display: block;
      margin-bottom: 6px;
      font-weight: bold;
    }

    input,
    textarea,
    select {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }

    .equipment {
      text-align: center;
      margin-top: 10px;
    }

    .equipment img {
      width: 100px;
      height: 100px;
      object-fit: cover;
      background: #eee;
      display: block;
      margin: 0 auto 10px;
    }

    .equipment-box {
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .equipment-box input[type="number"] {
      width: 80px;
      padding: 6px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 4px;
      text-align: center;
    }

    .buttons {
      text-align: center;
    }

    .buttons button {
      padding: 10px 20px;
      font-weight: bold;
      margin: 0 10px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }

    .submit-btn {
      background: #ffcc00;
    }

    .clear-btn {
      background: #ccc;
    }
  </style>
</head>

<body>

  <header>
    <a href="technicianShowEquipment.php">
      <img src="../0images/ftmkLogo_Yellow.png" alt="FTMK Logo" class="logo" />
    </a>
    <h1>Service Request</h1>
  </header>


  <?php if ($error): ?>
    <div style="color: red; text-align: center; font-weight: bold; padding-top:16px"><?php echo $error; ?></div>
  <?php endif; ?>

  <div class="form-container">
    <form action="submitServiceRequest.php?id=<?php echo htmlspecialchars($equipmentID); ?>" method="post" onsubmit="return validateForm()">
      <h2>Applicant's Information</h2>
      <div class="form-section">
        <div class="form-group">

          <label for="company">Company Repair Name:</label>
          <select id="company" name="company" required>
            <option value="" disabled selected>Select a company</option>
            <?php
            while ($row = $companyResult->fetch_assoc()) {
              $userId = htmlspecialchars($row['UserID']);
              $name = htmlspecialchars($row['Name']);
              $phone = htmlspecialchars($row['Phone']);
              echo "<option value='$userId' data-userid='$userId' data-phone='$phone'>$name</option>";
            }
            ?>
          </select>


          <label for="staffNo">User ID:</label>
          <input type="text" id="staffNo" name="staffNo" placeholder="User ID" readonly required>

          <label for="mobile">Phone Number:</label>
          <input type="text" id="mobile" name="mobile" placeholder="Phone Number" readonly required>

        </div>




        <div class="form-group equipment">
          <label style="font-weight: bold;">Equipment to be service</label>
          <div class="equipment-box">
            <?php
            $equipmentRow = null;
            if ($eresult && $eresult->num_rows == 1) {
              $equipmentRow = $eresult->fetch_assoc();
            }
            ?>

            <?php
            if ($equipmentRow) {
              $pic = htmlspecialchars($equipmentRow['Picture']);
              $name = htmlspecialchars($equipmentRow['EquipmentName']);
              $brand = htmlspecialchars($equipmentRow['Brand']);
              echo "<img src='../$pic' alt='Equipment Image' width='100'>";
              echo "<div style='margin-top: 10px; font-weight: bold;'>$name</div>";
              echo "<h4>$brand</h4>";

              $maxQty = htmlspecialchars($equipmentRow['Quantity']);
              echo "<label for='quantity' style='margin-top: 10px;'>Quantity:</label>";
              echo "<input type='number' id='quantity' name='quantity' min='1' value='1'  max='$maxQty' required>";
            } else {
              echo "Equipment not found.";
            }
            ?>
          </div>
        </div>
      </div>

      <h2>Purpose of Service</h2>

      <label for="reason">Service Reason:</label>
      <textarea id="reason" rows="3" name="reason" placeholder="Enter your reason" required></textarea>

      <label for="date">Date and time of Service:</label>
      <input type="datetime-local" id="date" name="activityTime" required>

      <div class="buttons">
        <button class="submit-btn" type="submit">Submit</button>
        <button class="clear-btn" onclick="handleClear()">Clear</button>
      </div>

      <script>
        function handleClear() {

          document.getElementById('quantity').value = '1';
          document.getElementById('reason').value = '';
          document.getElementById('date').value = '';

          function validateForm() {
            const quantity = document.getElementById("quantity").value;
            if (quantity == 0) {
              alert("Quantity cannot be 0 for a service request.");
              return false;
            }
            return true;
          }


        }


        document.getElementById('company').addEventListener('change', function() {
          const selectedOption = this.options[this.selectedIndex];
          const userId = selectedOption.getAttribute('data-userid');
          const phone = selectedOption.getAttribute('data-phone');

          document.getElementById('staffNo').value = userId || '';
          document.getElementById('mobile').value = phone || '';
        });
      </script>
    </form>
  </div>
</body>

</html>