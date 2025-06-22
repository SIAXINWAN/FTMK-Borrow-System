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
          Profile </a>

        <div>
          <a href="../Auth/logout.php">
            <img src="../0images/logout-icon.png" alt="Logout" class="input-icon">
            Log Out</a>
        </div>
      </div>
    </div>
  </header>

  <main>
    <h1>FTMK Borrow System</h1>
    <div class="buttons">
      <a href="technicianEquipmentIssuance&Return.php" class="button">
        <i class="fas fa-exchange-alt"></i>
        <span>Equipment Issuance & Return</span>
      </a>
      <a href="technicianEquipmentAvailabilityManagement.php" class="button">
        <i class="fas fa-clipboard-list"></i>
        <span>Equipment Management</span>
      </a>
      <a href="../Equipment/EquipmentAddition.php" class="button">
        <i class="fas fa-plus-circle"></i>
        <span>Equipment Addition</span>
      </a>
      <a href="technicianShowEquipment.php" class="button">
        <i class="fas fa-tools"></i>
        <span>Service Request</span>
      </a>
      <a href="../Service/showServiceList.php" class="button">
        <i class="fas fa-spinner"></i>
        <span>Checking Repair & Service Status</span>
      </a>
      <a href="../Service/serviceHistory.php" class="button">
        <i class="fas fa-history"></i>
        <span>Service History</span>
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