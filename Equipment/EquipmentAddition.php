<?php
session_start();
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
$idError = $_SESSION['idError'] ?? '';
$old = $_SESSION['old'] ?? [];

unset($_SESSION['success'], $_SESSION['error'], $_SESSION['idError'], $_SESSION['old']);
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Equipment Addition - FTMK Borrow System</title>
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      font-family: Arial, sans-serif;
      margin: 0;
      background-color: #f5f5f5;
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

    .container {
      background-color: white;
      width: 60%;
      margin: 40px auto;
      padding: 30px 50px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
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
    }
  </style>
</head>

<body>

  <div class="header">
    <?php
    if (isset($_SESSION['role'])) {
      switch ($_SESSION['role']) {
        case 'Technician':
          $homeLink = "../4technician/technicianMainPage.php";
          break;
        case 'Admin':
          $homeLink = "../5admin/adminMainPage.php";
          break;
      }
    } else {
      $homeLink = "../index.php";
    }
    ?>
    <a href="<?php echo $homeLink; ?>">

      <img src="../0images/ftmkLogo_Yellow.png" alt="FTMK Logo" class="logo" />
    </a>
    <h1>Equipment Addition</h1>
  </div>

  <div class="container">
    <?php if (!empty($success)): ?>
      <div style="margin-top: 20px; text-align: center; color: green; padding-bottom: 16px;">
        <?= $success ?>
      </div>
    <?php elseif (!empty($error)): ?>
      <div style="margin-top: 20px; text-align: center; color: red;">
        <?= $error ?>
      </div>
    <?php endif; ?>

    <form
      id="form-equipment"
      action="addEquipment.php"
      method="post"
      enctype="multipart/form-data">
      <div class="form-group">
        <label>Equipment Name:</label>
        <input type="text" name="name" value="<?= $old['nm'] ?? '' ?>" required />
      </div>
      <div class="form-group">
        <label>Equipment Brand:</label>
        <input type="text" name="brand" value="<?= $old['brand'] ?? '' ?>" required />
      </div>
      <div class="form-group">
        <label>Model Number:</label>
        <input type="text" name="model" value="<?= $old['model'] ?? '' ?>" required />
      </div>
      <div class="form-group">
        <label>Type:</label>
        <select name="type" required>
          <option value="" disabled <?= empty($old['type']) ? 'selected' : '' ?>>Select a type</option>
          <option value="Camera" <?= ($old['type'] ?? '') === 'Camera' ? 'selected' : '' ?>>Camera (EC)</option>
          <option value="Camera Accessory" <?= ($old['type'] ?? '') === 'Camera Accessory' ? 'selected' : '' ?>>Camera Accessory (ECA)</option>
          <option value="Audio Equipment" <?= ($old['type'] ?? '') === 'Audio Equipment' ? 'selected' : '' ?>>Audio Equipment (EA)</option>
          <option value="Computing" <?= ($old['type'] ?? '') === 'Computing' ? 'selected' : '' ?>>Computing (EL)</option>
          <option value="Presentation" <?= ($old['type'] ?? '') === 'Presentation' ? 'selected' : '' ?>>Presentation (EP)</option>
          <option value="Wireless Equipment" <?= ($old['type'] ?? '') === 'Wireless Equipment' ? 'selected' : '' ?>>Wireless Equipment (EW)</option>
          <option value="Dongle" <?= ($old['type'] ?? '') === 'Dongle' ? 'selected' : '' ?>>Dongle (ED)</option>
        </select>
      </div>
      <div class="form-group">
        <label>Equipment ID:</label>
        <input type="text" name="id" value="<?= $old['id'] ?? '' ?>" required />
        <?php if (!empty($idError)) echo "<span style='color:red; font-size:14px;'>$idError</span>"; ?>
      </div>
      <div class="form-group">
        <label>Quantity:</label>
        <input type="number" name="quantity" min="1" value="<?= $old['quantity'] ?? '1' ?>" required />
      </div>
      <div class="form-group">
        <label>Description:</label>
        <textarea name="desc" required><?= $old['desc'] ?? '' ?></textarea>
      </div>
      <div class="form-group">
        <label>Photo:</label>
        <input type="file" name="picture" required />
      </div>
      <div class="form-group">
        <label for="date">Date:</label>
        <input type="date" name="date" value="<?= $old['date'] ?? '' ?>" required />
      </div>

      <div class="button-group">
        <button type="submit" class="add-button">Add</button>
        <button type="reset" class="clear-button">Clear</button>
      </div>



    </form>
  </div>
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const typeSelect = document.querySelector("select[name='type']");
      const idInput = document.querySelector("input[name='id']");

      typeSelect.addEventListener("change", function() {
        const selectedType = this.value;

        if (selectedType) {
          fetch(`getNextEquipmentId.php?type=${encodeURIComponent(selectedType)}`)
            .then(res => res.json())
            .then(data => {
              idInput.value = data.nextId;
              idInput.readOnly = true;
            })
            .catch(err => {
              console.error("Error fetching next ID:", err);
              idInput.value = "";
              idInput.readOnly = false;
            });
        } else {
          idInput.value = "";
          idInput.readOnly = false;
        }
      });
    });
  </script>

</body>

</html>