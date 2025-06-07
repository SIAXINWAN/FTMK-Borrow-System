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

      <div class="login-form">
        <form id="login-form" action="login.php" method="POST">
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
                required
                placeholder="1234" />
            </div>
          </div>

          <button type="submit" class="login-btn">Login</button>
        </form>

        <p id="link">
          Don't have an account? <a href="registerPage.html">Register</a>
        </p>
      </div>
    </div>


  </body>

  </html>

<?php
}
?>