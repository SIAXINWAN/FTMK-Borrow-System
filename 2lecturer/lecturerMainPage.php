<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>FTMK Borrow System</title>
  <link rel="stylesheet" href="../styles.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link
    href="https://fonts.googleapis.com/css2?family=Roboto&display=swap"
    rel="stylesheet" />
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
</head>

<body>
  <header>
    <div class="header-top">
      <img src="../0images/ftmk-logo.png" alt="FTMK Logo" class="logo" />
    </div>

    <div class="header-bar">
      <div class="profile">
        <a href="../Auth/profilePage.php">
          <img
            src="../0images/user-icon.png"
            alt="User Icon"
            class="input-icon" />
          Profile
        </a>

        <div>
          <a href="../Auth/logout.php">
            <img
              src="../0images/logout-icon.png"
              alt="Logout"
              class="input-icon" />
            Log Out</a>
        </div>
      </div>
    </div>
  </header>

  <main>
    <h1>FTMK Borrow System</h1>
    <div class="buttons">
      <a href="../Equipment/showEquipment.php" class="button">
        <i class="fas fa-file-alt"></i>
        <span>Borrow Equipment</span>
      </a>

      <a href="lecturerApplicationStatus.php" class="button">
        <i class="fas fa-sync-alt"></i>
        <span>Application Status</span>
      </a>
      <a href="lecturerBorrowHistory.php" class="button">
        <i class="fas fa-history"></i>
        <span>Borrow History</span>
      </a>
      <a href="lecturerStudentApplicationApproval.php" class="button">
        <i class="fas fa-check-circle"></i>
        <span>Student Application Approval</span>
      </a>
    </div>
  </main>

  <footer>
    <div class="footer-content">
      <div class="contact">
        <h3>CONTACT US</h3>
        <div class="address-container">
          <i class="fas fa-home"></i>
          <div class="address-text">
            <p>
              Address: Faculty of Information & Communication Technology
              Universiti Teknikal Malaysia Melaka Hang Tuah Jaya, 76100 Durian
              Tunggal Melaka, Malaysia
            </p>
          </div>
        </div>
        <p><i class="fas fa-envelope"></i> Email: ftmk@utem.edu.my</p>
        <p><i class="fas fa-phone"></i> No. Phone: +606 2702411</p>
      </div>
      <div class="social">
        <h3>FIND US AT</h3>
        <div class="icons">
          <a
            href="https://web.facebook.com/myftmk/?_rdc=1&_rdr#"
            target="_blank"><i class="fab fa-facebook"></i></a>
          <a href="https://www.instagram.com/myftmk/?hl=en" target="_blank"><i class="fab fa-instagram"></i></a>
          <a href="http://www.youtube.com/@FTMKTV" target="_blank"><i class="fab fa-youtube"></i></a>
          <a href="https://x.com/myftmk?s=09" target="_blank"><i class="fab fa-twitter"></i></a>
        </div>
        <p><i class="fas fa-map-marker-alt"></i></p>
      </div>
    </div>
  </footer>
</body>

</html>