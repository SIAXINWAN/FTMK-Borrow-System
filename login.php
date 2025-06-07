<?php
session_start();

include('connect.php');

// Set username and password from POST if not already in session
if (!isset($_SESSION['UserID']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $_SESSION['UserID'] = $_POST['UserID'];
    $_SESSION['password'] = $_POST['password'];
}

if (isset($_SESSION['UserID'], $_SESSION['password'])) {
    $userID = $_SESSION['UserID'];
    $input_password = $_SESSION['password'];

    $sql = "SELECT * FROM users WHERE UserID='$userID'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (password_verify($input_password, $user['Password'])) {
            $_SESSION['role'] = $user['Role'];

            switch ($user['Role']) {
                case 'Student':
                    header("Location: 1student\studentMainPage.php");
                    break;
                case 'Lecturer':
                    header("Location: 2lecturer\lecturerMainPage.php");
                    break;
                case 'Company Repair':
                    header("Location: 3companyRepair\companyRepairMainPage.php");
                    break;
                case 'Technician':
                    header("Location: 4technician/technicianMainPage.php");
                    break;
                case 'Admin':
                    header("Location: 5admin\adminMainPage.php");
                    break;
                case 'Security Office':
                    header("Location: 6securityOffice\securityOfficeMainPage.php");
                    break;
            }
            exit();
        } else {
            echo "Login Fail: Password Salah";
            session_unset();
            echo "<meta http-equiv='refresh' content='2;URL=loginPage.php'>";
        }
    } else {
        echo "Login Fail: Username tidak wujud";
        session_unset();
        echo "<meta http-equiv='refresh' content='2;URL=loginPage.php'>";
    }
}
