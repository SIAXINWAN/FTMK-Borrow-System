<?php
session_start();
include("connect.php");

$userID = $_SESSION['UserID'];
$sql = "SELECT * FROM users WHERE UserID = '$userID'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>User Profile</title>
  <link rel="stylesheet" href="styles.css" />
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
      background-color: #fff;
      color: #000;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    .main-wrapper {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 40px;
    }

    .profile-container {
      display: flex;
      justify-content: center;
      align-items: flex-start;
      padding: 50px;
      gap: 60px;
    }

    .profile-left {
      text-align: center;
      max-width: 300px;
    }

    .profile-image {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      object-fit: cover;
      margin-bottom: 20px;
    }

    .profile-left h2 {
      margin: 10px 0;
    }

    .profile-left p {
      margin: 5px 0;
      font-size: 14px;
    }

    .profile-left .label {
      font-weight: bold;
      margin-top: 10px;
    }

    .profile-right {
      flex: 1;
    }

    .tab-buttons {
      display: flex;
      margin-bottom: 10px;
    }

    .tab-buttons button {
      padding: 10px 20px;
      border: none;
      background-color: #ddd;
      cursor: pointer;
      margin-right: 10px;
      font-size: 16px;
    }

    .tab-buttons .active {
      background-color: #304ffe;
      color: #fff;
    }

    .description-box {
      border: 1px solid #ccc;
      padding: 20px;
      max-width: 600px;
    }

    .description-box h3 {
      margin-top: 0;
    }

    .edit-form input {
      width: 100%;
      padding: 10px;
      margin: 8px 0;
      box-sizing: border-box;
    }

    .edit-form button {
      background-color: #304ffe;
      color: white;
      border: none;
      padding: 10px 20px;
      cursor: pointer;
    }
  </style>
</head>

<body>
  <header>
    <div class="header-top">
      <img src="0images/ftmk-logo.png" alt="FTMK Logo" class="logo" />
    </div>
    <div class="header-bar">
      <div class="profile">
        <div>
          <?php
          if (isset($_SESSION['role'])) {
            switch ($_SESSION['role']) {
              case 'Student':
                $homeLink = "1student/studentMainPage.php";
                break;
              case 'Lecturer':
                $homeLink = "2lecturer/lecturerMainPage.php";
                break;
              case 'Company Repair':
                $homeLink = "3companyRepair/companyRepairMainPage.php";
                break;
              case 'Technician':
                $homeLink = "4technician/technicianMainPage.php";
                break;
              case 'Admin':
                $homeLink = "5admin/adminMainPage.php";
                break;
              case 'Security Office':
                $homeLink = "6securityOffice/securityOfficeMainPage.php";
                break;
            }
          } else {
            $homeLink = "loginPage.php"; // fallback
          }
          ?>
          <a href="<?php echo $homeLink; ?>">
            <img src="0images/home-icon.png" alt="Home" class="input-icon" />
            Home
          </a>
        </div>
      </div>
    </div>
  </header>


  <main class="main-wrapper">
    <div class="profile-container">
      <?php if ($row = $result->fetch_assoc()) { ?>
        <div class="profile-left">
          <img src="https://upload.wikimedia.org/wikipedia/commons/9/99/Sample_User_Icon.png" class="profile-image" alt="Profile" />
          <h2><?php echo $row['Name']; ?></h2>
          <p class="label">Email Address</p>
          <p><?php echo $row['Email']; ?></p>
          <p class="label">No. Phone</p>
          <p><?php echo $row['Phone']; ?></p>
          <p class="label">Role:</p>
          <p><?php echo $row['Role']; ?></p>
        </div>

        <div class="profile-right">
          <div class="tab-buttons">
            <button class="active" onclick="showTab('about')">About me</button>
            <button onclick="showTab('edit')">Edit profile</button>
          </div>

          <div id="about-tab" class="description-box">
            <h3>Description</h3>
            <p>User has not updated their description yet.</p>
          </div>

          <div id="edit-tab" class="description-box" style="display:none;">
            <h3>Edit Profile</h3>
            <form class="edit-form" action="editProfile.php" method="POST">
              <input type="hidden" name="UserID" value="<?php echo $row['UserID']; ?>" />
              <label>Name</label>
              <input type="text" name="Name" value="<?php echo $row['Name']; ?>" required />
              <label>Email</label>
              <input type="email" name="Email" value="<?php echo $row['Email']; ?>" required />
              <label>Phone</label>
              <input type="text" name="Phone" value="<?php echo $row['Phone']; ?>" required />
              <button type="submit">Save Changes</button>
            </form>
          </div>
        </div>
      <?php } ?>
    </div>
  </main>

  <script>
    function showTab(tab) {
      document.getElementById('about-tab').style.display = (tab === 'about') ? 'block' : 'none';
      document.getElementById('edit-tab').style.display = (tab === 'edit') ? 'block' : 'none';
      const buttons = document.querySelectorAll('.tab-buttons button');
      buttons.forEach(btn => btn.classList.remove('active'));
      if (tab === 'about') buttons[0].classList.add('active');
      else buttons[1].classList.add('active');
    }
  </script>
</body>

</html>