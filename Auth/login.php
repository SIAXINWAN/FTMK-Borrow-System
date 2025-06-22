<?php
session_start();
include('../connect.php');

// 确保只有 POST 方式访问
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit();
}

// 存储 UserID 进 session
if (!isset($_SESSION['UserID']) && isset($_POST['UserID'])) {
    $_SESSION['UserID'] = $_POST['UserID'];
}

if (isset($_SESSION['UserID'])) {
    $userID = $_SESSION['UserID'];
    $input_password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM users WHERE UserID=?");
    $stmt->bind_param('s', $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (password_verify($input_password, $user['Password'])) {
            $_SESSION['role'] = $user['Role'];

            switch ($user['Role']) {
                case 'Student':
                    header("Location: ../1student/studentMainPage.php");
                    break;
                case 'Lecturer':
                    header("Location: ../2lecturer/lecturerMainPage.php");
                    break;
                case 'Company Repair':
                    header("Location: ../3companyRepair/companyRepairMainPage.php");
                    break;
                case 'Technician':
                    header("Location: ../4technician/technicianMainPage.php");
                    break;
                case 'Admin':
                    header("Location: ../5admin/adminMainPage.php");
                    break;
                case 'Security Office':
                    header("Location: ../6securityOffice/securityOfficeMainPage.php");
                    break;
            }
            exit();
        } else {
            session_unset();
            echo "Login Fail: Password Salah";
            echo "<meta http-equiv='refresh' content='2;URL=../index.php'>";
        }
    } else {
        session_unset();
        echo "Login Fail: Username tidak wujud";
        echo "<meta http-equiv='refresh' content='2;URL=../index.php'>";
    }

    $stmt->close();
    $conn->close();
}
