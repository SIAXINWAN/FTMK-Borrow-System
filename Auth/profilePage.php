<?php
session_start();
include("../connect.php");

$userID = $_SESSION['UserID'];

$stmt = $conn->prepare("SELECT * FROM users WHERE UserID = ?");
$stmt->bind_param("s", $userID);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>User Profile</title>
  <link rel="stylesheet" href="../styles.css" />
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
      background-color: #f8f9fa;
      color: #333;
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
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
      display: flex;
      padding: 40px 60px;
      gap: 60px;
      max-width: 900px;
      width: 100%;
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
      background-color: #ccc;
    }

    .profile-left h2 {
      margin: 10px 0;
      font-size: 24px;
    }

    .profile-left p {
      margin: 5px 0;
      font-size: 14px;
    }

    .profile-left .label {
      font-weight: bold;
      margin-top: 10px;
      color: #555;
    }

    .profile-right {
      flex: 1;
    }

    .description-box {
      border-top: 1px solid #ccc;
      padding-top: 20px;
      margin-top: 20px;
    }

    .edit-form input {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      box-sizing: border-box;
      border: 1px solid #ccc;
      border-radius: 6px;
    }

    .edit-form button,
    .toggle-password-btn {
      background-color: #304ffe;
      color: white;
      border: none;
      padding: 10px 20px;
      cursor: pointer;
      border-radius: 6px;
      font-size: 16px;
    }

    .toggle-password-btn {
      margin-top: 20px;
    }

    .toggle-password-btn:hover,
    .edit-form button:hover {
      background-color: #1e40ff;
    }

    .header-bar a {
      text-decoration: none;
      color: #000;
      font-weight: bold;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .header-bar {
      padding: 10px 20px;
      background-color: #ffd740;
      display: flex;
      justify-content: flex-end;
    }

    .logo {
      height: 60px;
      margin: 20px;
    }

    .input-icon {
      width: 20px;
      height: 20px;
    }

    .error-text {
      font-size: small;
      color: red;
      font-weight: bold;
      padding-bottom: 8px;
    }

    .success-text {
      color: green;
      font-weight: bold;
      padding-top: 80px;
    }
  </style>
</head>

<body>
  <header>
    <div class="header-top">
      <img src="../0images/ftmk-logo.png" alt="FTMK Logo" class="logo" />
    </div>
    <div class="header-bar">
      <div class="profile">
        <div>
          <?php
          if (isset($_SESSION['role'])) {
            switch ($_SESSION['role']) {
              case 'Student':
                $homeLink = "../1student/studentMainPage.php";
                break;
              case 'Lecturer':
                $homeLink = "../2lecturer/lecturerMainPage.php";
                break;
              case 'Company Repair':
                $homeLink = "../3companyRepair/companyRepairMainPage.php";
                break;
              case 'Technician':
                $homeLink = "../4technician/technicianMainPage.php";
                break;
              case 'Admin':
                $homeLink = "../5admin/adminMainPage.php";
                break;
              case 'Security Office':
                $homeLink = "../6securityOffice/securityOfficeMainPage.php";
                break;
              default:
                $homeLink = "../index.php";
                break;
            }
          } else {
            $homeLink = "../index.php";
          }
          ?>
          <a href="<?php echo $homeLink; ?>">
            <img src="../0images/home-icon.png" alt="Home" class="input-icon" />
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

          <h2><?php echo $row['Name']; ?></h2>
          <p class="label">Email Address</p>
          <p><?php echo $row['Email']; ?></p>
          <p class="label">No. Phone</p>
          <p><?php echo $row['Phone']; ?></p>
          <p class="label">Role</p>
          <p><?php echo $row['Role']; ?></p>
        </div>

        <div class="profile-right">



          <button class="toggle-password-btn" onclick="togglePasswordForm()">Change Password</button>
          <?php


          if (isset($_GET['success']) && $_GET['success'] === 'password_updated') {
            echo '<div class="success-text" style="color: green; margin-bottom: 10px;">Password updated successfully.</div>';
          }
          ?>

          <div id="password-form" class="description-box" style="display:none;">
            <h3>Change Password</h3>
            <form class="edit-form" action="changePassword.php" method="POST">
              <input type="hidden" name="UserID" value="<?php echo $row['UserID']; ?>" />

              <label>Current Password</label>
              <input type="password" name="currentPassword" required />
              <?php if (isset($_GET['error']) && $_GET['error'] == 'wrong_password') {
                echo '<div class="error-text">Current password is incorrect.</div>';
              } ?>

              <label>New Password</label>
              <input type="password" name="newPassword" required />
              <?php if (isset($_GET['error']) && $_GET['error'] == 'same_as_old') {
                echo '<div class="error-text">New password cannot be the same as current password.</div>';
              } ?>

              <label>Confirm New Password</label>
              <input type="password" name="confirmPassword" required />
              <?php if (isset($_GET['error']) && $_GET['error'] == 'password_mismatch') {
                echo '<div class="error-text">Passwords do not match.</div>';
              } ?>

              <?php if (isset($_GET['error']) && $_GET['error'] == 'update_failed') {
                echo '<div class="error-text">Update failed. Please try again.</div>';
              } ?>

              <button type="submit">Update Password</button>
            </form>

          </div>
        </div>
      <?php } ?>
    </div>
  </main>

  <script>
    window.onload = function() {
      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.get('error')) {
        document.getElementById("password-form").style.display = "block";
      }
    };


    function togglePasswordForm() {
      const form = document.getElementById("password-form");
      form.style.display = form.style.display === "none" ? "block" : "none";

      const successMessage = document.querySelector('.success-text');
      if (successMessage) {
        successMessage.remove();
      }
    }
  </script>
</body>

</html>