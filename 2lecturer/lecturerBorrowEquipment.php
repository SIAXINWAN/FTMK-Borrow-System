<?php
session_start();

include("../connect.php");

$userId = $_SESSION['UserID'];
$equipmentID = $_GET['id'];

$stmt1 = $conn->prepare("SELECT * FROM users WHERE UserID = ?");
$stmt1->bind_param("s", $userId);
$stmt1->execute();
$result = $stmt1->get_result();

$stmt2 = $conn->prepare("SELECT * FROM equipment WHERE EquipmentID = ?");
$stmt2->bind_param("s", $equipmentID);
$stmt2->execute();
$eresult = $stmt2->get_result();

$stmt3 = $conn->prepare("SELECT UserID, Name FROM dummy WHERE Role = ?");
$role = "Lecturer";
$stmt3->bind_param("s", $role);
$stmt3->execute();
$lresult = $stmt3->get_result();


?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Borrow Equipment - FTMK Borrow System</title>
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
      /* width: 100px;
      height: 100px; */
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
  </style>
</head>

<body>

  <header>
    <a href="../Equipment/showEquipment.php">
      <img src="../0images/ftmkLogo_Yellow.png" alt="FTMK Logo" class="logo" />
    </a>
    <h1>Intangible Assets Borrow Form</h1>
  </header>

  <div class="form-container">
    <form action="../Borrow/submitBorrowApplication.php?id=<?php echo htmlspecialchars($equipmentID); ?>" method="post">
      <script>
        document.addEventListener("DOMContentLoaded", function() {
          const form = document.querySelector("form");
          if (form) {
            form.addEventListener("submit", function() {
              // Show the spinner when form is submitted
              document.getElementById("loadingOverlay").style.display = "flex";
            });
          }
        });
      </script>

      <h2>Applicant's Information</h2>
      <div class="form-section">
        <div class="form-group">

          <?php
          if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();

            echo '<label for="name">Name:</label>';
            echo '<input type="text" id="name" value="' . htmlspecialchars($row["Name"]) . '" readonly>';

            echo '<label for="studentNo">User ID:</label>';
            echo '<input type="text" id="studentNo" value="' . htmlspecialchars($row["UserID"]) . '" readonly>';

            echo '<label for="mobile">Phone Number:</label>';
            echo '<input type="text" id="mobile" value="' . htmlspecialchars($row["Phone"]) . '" readonly>';
          }

          ?>



        </div>

        <div class="form-group equipment">
          <label style="font-weight: bold;">Equipment to be borrow</label>
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
              echo "<input type='number' id='quantity' name='quantity' min='1' value='1' max='$maxQty' required>";
            } else {
              echo "Equipment not found.";
            }
            ?>


          </div>
        </div>
      </div>

      <h2>Purpose of Application (Course / Workshop / Seminar)</h2>

      <label for="purpose">Purpose of Application:</label>
      <textarea id="purpose" name="purpose" rows="3" placeholder="Enter your purpose" required></textarea>

      <label for="date">Date and time of Activity:</label>
      <input type="datetime-local" id="date" name="activityTime" required>
      <input type="hidden" name="equipmentId" value="<?php echo htmlspecialchars($equipmentID); ?>">


      <div class="buttons">
        <button class="submit-btn" type="submit"">Submit</button>
        <button class=" clear-btn" onclick="handleClear()">Clear</button>
      </div>

      <script>
        function handleClear() {
          document.getElementById('quantity').value = '1';
          document.getElementById('purpose').value = '';
          document.getElementById('date').value = '';
        }
      </script>
    </form>
  </div>
  <div id="loadingOverlay" style="display: none;">
    <div class="spinner-container">
      <div class="spinner"></div>
      <div class="spinner-text">Processing...</div>
    </div>
  </div>

</body>

</html>