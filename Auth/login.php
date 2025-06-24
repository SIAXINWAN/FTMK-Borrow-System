<?php
session_start();
include('../connect.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit();
}

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

        if (empty($user['Password'])) {
            session_unset();
            echo "<div style='text-align: center; padding-top: 100px;'>";
            echo "<img src='../0images/sad.png' alt='sad' width='150' />";
            echo "<h2>Login Failed</h2>";
            echo "<h3>You haven't registered yet. Please register before logging in.</h3>";
            echo "<p>Redirecting to login page...</p>";
            echo "</div>";
            echo "<meta http-equiv='refresh' content='3;URL=../index.php'>";
            exit();
        }

        if (password_verify($input_password, $user['Password'])) {
            $_SESSION['role'] = $user['Role'];
            $_SESSION['Email'] = $user['Email'];

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
            echo "<div style='text-align: center; padding-top: 100px;'>";
            echo "<img src='../0images/sad.png' alt='sad' width='150' />";
            echo "<h2>Login Failed</h2>";
            echo "<h3>Incorrect password. Please try again.</h3>";
            echo "<p>Redirecting to login page...</p>";
            echo "</div>";
            echo "<meta http-equiv='refresh' content='3;URL=../index.php'>";
        }
    } else {
        session_unset();
        echo "<div style='text-align: center; padding-top: 100px;'>";
        echo "<img src='../0images/sad.png' alt='sad' width='150' />";
        echo "<h2>Login Failed</h2>";
        echo "<h3>User ID not found. Please check and try again.</h3>";
        echo "<p>Redirecting to login page...</p>";
        echo "</div>";
        echo "<meta http-equiv='refresh' content='3;URL=../index.php'>";
    }

    $stmt->close();
    $conn->close();
}
