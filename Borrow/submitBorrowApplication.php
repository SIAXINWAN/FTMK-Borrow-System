<?php
session_start();
include("../connect.php");
include("../Notification/sendEmail.php");


$userId = $_SESSION['UserID'];
$equipmentId = $_GET['id'];
$lecturerId = $_POST['lecturerId'] ?? null;
$quantity = $_POST['quantity'];
$purpose = $_POST['purpose'];
$activityTime = $_POST['activityTime'];
$applyDate = date('Y-m-d H:i:s');


$roleStmt = $conn->prepare("SELECT Role FROM users WHERE UserID = ?");
$roleStmt->bind_param("s", $userId);
$roleStmt->execute();
$roleResult = $roleStmt->get_result();
$roleRow = $roleResult->fetch_assoc();
$role = $roleRow['Role'] ?? 'Student';
$roleStmt->close();

$success = true;

if ($role === 'Student') {
    if (!$lecturerId) {
        die("Lecturer ID is required for student applications.");
    }

    $insertAppStmt = $conn->prepare("INSERT INTO borrow_applications
        (UserID, EquipmentID, LecturerID, Quantity, Purpose, ApplyDate, ActivityDateTime, ApplicationStatus)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')");
    $insertAppStmt->bind_param("sssssss", $userId, $equipmentId, $lecturerId, $quantity, $purpose, $applyDate, $activityTime);
} else {
    $insertAppStmt = $conn->prepare("INSERT INTO borrow_applications
        (UserID, EquipmentID, Quantity, Purpose, ApplyDate, ActivityDateTime, ApplicationStatus)
        VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
    $insertAppStmt->bind_param("ssisss", $userId, $equipmentId, $quantity, $purpose, $applyDate, $activityTime);
}

if ($insertAppStmt->execute()) {

    $applicationId = $conn->insert_id;
    $insertAppStmt->close();

    $updateQtyStmt = $conn->prepare("UPDATE equipment SET Quantity = Quantity - ? WHERE EquipmentID = ?");
    $updateQtyStmt->bind_param("is", $quantity, $equipmentId);

    if (!$updateQtyStmt->execute()) {
        echo "Application saved, but failed to update equipment quantity: " . $conn->error;
        exit;
    }
    $updateQtyStmt->close();


    $approvalSuccess = true;

    if ($role === 'Student') {
        $stmt1 = $conn->prepare("INSERT INTO approval (ApplicationID, ApproverRole, ApproverID, Status, Remarks, ApprovalDate) 
                                 VALUES (?, 'Lecturer', ?, 'Pending', NULL, NULL)");
        $stmt1->bind_param("is", $applicationId, $lecturerId);

        $stmt2 = $conn->prepare("INSERT INTO approval (ApplicationID, ApproverRole, ApproverID, Status, Remarks, ApprovalDate) 
                                 VALUES (?, 'Admin', NULL, 'Pending', NULL, NULL)");
        $stmt2->bind_param("i", $applicationId);

        $stmt3 = $conn->prepare("INSERT INTO approval (ApplicationID, ApproverRole, ApproverID, Status, Remarks, ApprovalDate) 
                                 VALUES (?, 'Security Office', NULL, 'Pending', NULL, NULL)");
        $stmt3->bind_param("i", $applicationId);

        $approvalSuccess = $stmt1->execute() && $stmt2->execute() && $stmt3->execute();

        $stmt1->close();
        $stmt2->close();
        $stmt3->close();
    } else {
        $stmt1 = $conn->prepare("INSERT INTO approval (ApplicationID, ApproverRole, ApproverID, Status, Remarks, ApprovalDate) 
                                 VALUES (?, 'Admin', NULL, 'Pending', NULL, NULL)");
        $stmt1->bind_param("i", $applicationId);

        $stmt2 = $conn->prepare("INSERT INTO approval (ApplicationID, ApproverRole, ApproverID, Status, Remarks, ApprovalDate) 
                                 VALUES (?, 'Security Office', NULL, 'Pending', NULL, NULL)");
        $stmt2->bind_param("i", $applicationId);

        $approvalSuccess = $stmt1->execute() && $stmt2->execute();

        $stmt1->close();
        $stmt2->close();
    }

    if ($approvalSuccess) {



        if ($role === 'Student') {
            $dummyStmt = $conn->prepare("SELECT Name, Email FROM dummy WHERE UserID = ?");
            $dummyStmt->bind_param("s", $lecturerId);
            $dummyStmt->execute();
            $dummyResult = $dummyStmt->get_result();
            $dummyRow = $dummyResult->fetch_assoc();
            $lecturerName = $dummyRow['Name'] ?? '';
            $lecturerEmail = $dummyRow['Email'] ?? '';
            $dummyStmt->close();

            $userCheckStmt = $conn->prepare("SELECT * FROM users WHERE UserID = ?");
            $userCheckStmt->bind_param("s", $lecturerId);
            $userCheckStmt->execute();
            $userCheckResult = $userCheckStmt->get_result();
            $hasAccount = $userCheckResult->num_rows > 0;
            $userCheckStmt->close();

            if ($lecturerEmail) {
                if ($hasAccount) {
                    $subject = "New Borrow Application From Your Student";
                    $body = "
                        Dear $lecturerName,<br><br>
                        You have received a new equipment borrow application from your student.<br>
                        Please log in to the <b><a href='https://webapp.utem.edu.my/student/dit/jcats/FTMK-Borrow-System/' target='_blank'>FTMK Borrow System</a></b> as soon as possible and check your student's application.<br><br>
                        Thank you.
                    ";
                } else {
                    $subject = "Action Required: Register to Approve Borrow Application";
                    $body = "
                        Dear $lecturerName,<br><br>
                        A student has submitted a borrow application and selected you as the approving lecturer.<br><br>
                        <b>However, our system shows that you have not yet registered for an account.</b><br><br>
                        Please register at the <b><a href='https://webapp.utem.edu.my/student/dit/jcats/FTMK-Borrow-System/' target='_blank'>FTMK Borrow System</a></b> as soon as possible and check your student's application.<br><br>
                        Thank you.<br><br>
                        Best regards,<br>
                        FTMK Borrow System<br>
                        University Teknikal Malaysia Melaka (UTeM)<br>
                    ";
                }

                sendNotification($lecturerEmail, $subject, $body);
            }
        } else {
            $adminQuery = "SELECT Email, Name FROM users WHERE Role = 'Admin'";
            $adminResult = $conn->query($adminQuery);
            while ($adminRow = $adminResult->fetch_assoc()) {
                $adminEmail = $adminRow['Email'];
                $adminName = $adminRow['Name'];

                $subject = "New Borrow Application Pending Approval";
                $body = "
                    Dear $adminName,<br><br>
                    A new borrow application has been submitted and is awaiting your approval.<br>
                    Please log in to <b><a href='https://webapp.utem.edu.my/student/dit/jcats/FTMK-Borrow-System/' target='_blank'>FTMK Borrow System</a></b> to review and take action.<br><br>
                    Thank you.<br><br>
                    Best regards,<br>
                    FTMK Borrow System<br>
                    University Teknikal Malaysia Melaka (UTeM)<br>
                ";
                sendNotification($adminEmail, $subject, $body);
            }
        }

        $_SESSION['success'] = "Borrow application submitted!";
        $redirect = ($role === 'Student') ? "../1student/studentApplicationStatus.php" : "../2lecturer/lecturerApplicationStatus.php";
        header("Location: $redirect");
        exit;
    } else {
        echo "Application saved, but approval record failed: " . $conn->error;
    }
} else {
    echo "Error in saving application: " . $conn->error;
}
