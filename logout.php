<?php
session_start();
if (isset($_SESSION['UserID'])) {
    $_SESSION = array();
    session_destroy();
    echo "<div style='text-align: center; padding-top: 100px;'>";
    echo " <img src='0images/bye.jpg' alt='bye' width='200'  />";
    echo "<h2>Log Out Successful<br> See you soon!</h2>";
    echo "  <p>Redirecting to login page...</p>";
    echo "  </div>";
    echo "<meta http-equiv=\"refresh\" content=\"3;URl=loginPage.php\">";
}
