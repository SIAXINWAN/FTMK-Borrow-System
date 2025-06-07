<?php
session_start();
if (isset($_SESSION['UserID'])) {
    $_SESSION = array();
    session_destroy();
    echo "Logout Successful";
    echo "<meta http-equiv=\"refresh\" content=\"3;URl=loginPage.php\">";
}
