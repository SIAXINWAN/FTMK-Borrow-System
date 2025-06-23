<?php
session_start();

include("../connect.php");



$equipmentID = $_GET['id'] ?? '';

$stmt = $conn->prepare("SELECT * FROM equipment WHERE EquipmentID = ?");
$stmt->bind_param("s", $equipmentID);
$stmt->execute();
$result = $stmt->get_result();

$row = $result->fetch_assoc();

?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Equipment Update - FTMK Borrow System</title>
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f9f9f9;
    }

    table {
      width: 80%;
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


    a {
      text-decoration: none;
    }

    .form-group {
      display: flex;
      align-items: center;
      margin-bottom: 20px;
    }

    .form-group label {
      width: 180px;
      font-size: 16px;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
      flex: 1;
      padding: 10px;
      font-size: 14px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }

    textarea {
      height: 100px;
      resize: none;
    }

    .button-group {
      display: flex;
      justify-content: center;
      margin-top: 30px;
    }

    .button-group button {
      padding: 10px 30px;
      font-size: 14px;
      font-weight: bold;
      border: none;
      border-radius: 4px;
      margin: 0 10px;
      cursor: pointer;
    }

    .add-button {
      background-color: #ffcc00;
      color: black;
    }

    .clear-button {
      background-color: #ccc;
      color: black;
      padding: 10px 30px;
      font-size: 14px;
      font-weight: bold;
      border-radius: 4px;
      text-decoration: none;
      display: inline-block;
      text-align: center;
    }


    .container {
      background-color: white;
      width: 60%;
      margin: 40px auto;
      padding: 30px 50px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
  </style>

</head>

<body>
  <header>

    <a href="adminEquipmentInventory.php"><img src="../0images/ftmkLogo_Yellow.png" class="logo" /></a>

    <h1 style="text-align: center">Equipment Update</h1>

  </header>
  <div class="container">
    <form
      id="form-equipment"
      action="../Equipment/updateEquipment.php"
      method="post"
      enctype="multipart/form-data">



      <div class="form-group">
        <label>Equipment Name:</label>
        <input type="text" name="name" value="<?= $row['EquipmentName'] ?>" required />
      </div>
      <div class="form-group">
        <label>Equipment Brand:</label>
        <input type="text" name="brand" value="<?= $row['Brand'] ?>" required />
      </div>
      <div class="form-group">
        <label>Model Number:</label>
        <input type="text" name="model" value="<?= $row['ModelNumber'] ?>" required />
      </div>
      <div class="form-group">
        <label>Type:</label>
        <select name="type" required>
          <option value="Camera" <?= $row['Type'] === 'Camera' ? 'selected' : '' ?>>Camera (EC)</option>
          <option value="Camera Accessory" <?= $row['Type'] === 'Camera Accessory' ? 'selected' : '' ?>>Camera Accessory (ECA)</option>
          <option value="Audio Equipment" <?= $row['Type'] === 'Audio Equipment' ? 'selected' : '' ?>>Audio Equipment (EA)</option>
          <option value="Computing" <?= $row['Type'] === 'Computing' ? 'selected' : '' ?>>Computing (EL)</option>
          <option value="Presentation" <?= $row['Type'] === 'Presentation' ? 'selected' : '' ?>>Presentation (EP)</option>
          <option value="Wireless Equipment" <?= $row['Type'] === 'Wireless Equipment' ? 'selected' : '' ?>>Wireless Equipment (EW)</option>
          <option value="Dongle" <?= $row['Type'] === 'Dongle' ? 'selected' : '' ?>>Dongle (ED)</option>

        </select>
      </div>
      <div class="form-group">
        <label>Equipment ID:</label>
        <input type="text" name="id" value="<?= $row['EquipmentID'] ?? '' ?>" required readonly />
        <?php if (!empty($idError)) echo "<span style='color:red; font-size:14px;'>$idError</span>"; ?>
      </div>
      <div class="form-group">
        <label>Quantity:</label>
        <input type="number" name="quantity" min="0" value="<?= $row['Quantity'] ?? '1' ?>" required />
      </div>
      <div class="form-group">
        <label>Description:</label>
        <textarea name="desc" required><?= $row['Description'] ?? '' ?></textarea>
      </div><?php if (!empty($row['Picture'])): ?>
        <div class="form-group">
          <label>Current Photo:</label>
          <img src="../<?= htmlspecialchars($row['Picture']) ?>" alt="Equipment Photo" style="height: 100px;">
        </div>
      <?php endif; ?>

      <div class="form-group">
        <label>New Photo:</label>
        <input type="file" name="picture" />
      </div>
      <div class="form-group">
        <label for="date">Date:</label>
        <input type="date" name="date" value="<?= $row['Date'] ?>" required />
      </div>

      <div class="button-group">
        <button type="submit" class="add-button">Update</button>
        <a href="adminEquipmentInventory.php" class="clear-button" style="text-align: center;">Cancel</a>

      </div>



    </form>
  </div>
</body>

</html>