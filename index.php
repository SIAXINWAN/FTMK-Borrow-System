<?php

session_start();
if (isset($_SESSION['UserID'])) {
  $_SESSION = array();
  session_destroy();
} else {

?>

  <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>FTMK Borrow System - Login</title>
    <link rel="stylesheet" href="styles.css" />
    <style>
      #notes,
      center {
        padding-bottom: 20px;
      }
    </style>
  </head>

  <body>
    <img src="0images/utem-logo.png" alt="UTeM Logo" class="utem-logo" />
    <div class="container">
      <div class="header">
        <img src="0images/ftmk-logo.png" alt="FTMK Logo" class="ftmk-logo" />
        <h2>Fakulti Teknologi Maklumat dan Komunikasi</h2>
      </div>

      <div>
        <input type="radio" name="role" value="Student">Student
        <input type="radio" name="role" value="Lecturer">Lecturer
        <input type="radio" name="role" value="Admin">Admin
        <input type="radio" name="role" value="Technician">Technician
        <input type="radio" name="role" value="Company">Company Repair
        <input type="radio" name="role" value="Security">Security Office
      </div>

      <div class="login-form">
        <form id="login-form" action="Auth/login.php" method="POST">
          <div class="form-group">
            <label for="userid">
              <img
                src="0images/user-icon.png"
                alt="User Icon"
                class="input-icon" />
              User ID
            </label>
            <input type="text" id="userid" name="UserID" required />
          </div>

          <div class="form-group">
            <label for="password">
              <img
                src="0images/password-icon.png"
                alt="Password Icon"
                class="input-icon" />
              Password
            </label>
            <div style="position: relative">
              <input
                type="password"
                id="password"
                name="password"
                required />
            </div>
          </div>

          <button type="submit" class="login-btn">Login</button>
        </form>

        <p id="link">
          Don't have an account? <a href="Auth/registerPage.php">Register</a>
        </p>
      </div>
    </div>

    <script>
      // Map roles to default credentials
      const credentials = {
        Student: {
          userid: "D032310149",
          password: "1234"
        },
        Lecturer: {
          userid: "L10041",
          password: "1234"
        },
        Admin: {
          userid: "a001",
          password: "1234"
        },
        Technician: {
          userid: "t001",
          password: "1234"
        },
        Company: {
          userid: "c001",
          password: "1234"
        },
        Security: {
          userid: "s001",
          password: "1234"
        }
      };

      // Get all radio buttons
      const radios = document.querySelectorAll('input[type="radio"]');
      const userIdInput = document.getElementById('userid');
      const passwordInput = document.getElementById('password');

      radios.forEach(radio => {
        radio.addEventListener('click', function() {
          const role = radio.value;
          if (credentials[role]) {
            userIdInput.value = credentials[role].userid;
            passwordInput.value = credentials[role].password;
          } else {
            userIdInput.value = '';
            passwordInput.value = '';
          }
        });
      });
    </script>
  </body>

  </html>

<?php
}
?>