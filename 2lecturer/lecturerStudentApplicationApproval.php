<?php

include("../connect.php");
session_start(); ?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Application Approval - FTMK Borrow System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
        }


        header {
            background-color: #ffcc00;
            padding: 15px 20px;
            display: flex;
            align-items: center;
        }

        header h1 {
            margin: 0 auto;
            color: #000;
            font-weight: bold;
        }

        .logo {
            height: 80px;
        }

        section table {
            width: 80%;
            margin-left: auto;
            margin-right: auto;
            margin-top: 50px;
        }

        section table th,
        section table td {
            text-align: center;
        }

        section th {
            background-color: rgb(53, 52, 52);
            color: white;
        }

        section table,
        section th,
        section td {
            border: 1.5px solid black;
            border-collapse: collapse;
        }

        section tr {
            height: 50px;
        }

        section td {
            text-align: center;
            vertical-align: middle;
        }


        .buttonBox {
            display: flex;
        }

        .iconStyle {
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            width: 30px;
            height: 30px;
        }

        .tick {
            background-color: greenyellow;
        }

        .cross {
            background-color: red;
        }

        .buttonStyle {
            height: 50px;
            width: 50px;
            background-color: white;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            margin: 0 auto;
        }

        /* Loading Overlay Styling */
        #loadingOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .spinner-container {
            text-align: center;
            color: white;
            font-size: 20px;
        }

        .spinner {
            border: 6px solid #f3f3f3;
            border-top: 6px solid #ffcc00;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            margin: 0 auto 15px;
            animation: spin 1s linear infinite;
        }

        .spinner-text {
            font-weight: bold;
            letter-spacing: 1px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <header>
        <a href="lecturerMainPage.php"><img src="../0images/ftmkLogo_Yellow.png" class="logo"></a>
        <h1>Student Application Approval</h1>

    </header>
    <section>
        <table cellspacing="0">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Student Name</th>
                    <th>Equipment Name</th>
                    <th>Quantity</th>
                    <th>Reason</th>
                    <th>Approval</th>
                </tr>
            </thead>
            <tbody>
                <?php

                $lecturerId = $_SESSION['UserID'];

                $stmt = $conn->prepare("SELECT a.ApplicationID, u.Name AS StudentName, e.EquipmentName, a.Purpose, a.Quantity
                        FROM borrow_applications a
                        JOIN users u ON a.UserID = u.UserID
                        JOIN equipment e ON a.EquipmentID = e.EquipmentID
                        JOIN approval ap ON a.ApplicationID = ap.ApplicationID
                        WHERE ap.ApproverRole = 'Lecturer' AND ap.ApproverID = ? AND ap.Status = 'Pending'");
                $stmt->bind_param("s", $lecturerId);
                $stmt->execute();
                $result = $stmt->get_result();
                $stmt->close();

                $counter = 1;
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr data-app-id='{$row['ApplicationID']}'>";
                        echo "<td>" . $counter++ . "</td>";
                        echo "<td>" . htmlspecialchars($row['StudentName']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['EquipmentName']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Quantity']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Purpose']) . "</td>";
                        echo "<td>
                <div class='buttonBox'>
                    <button class='buttonStyle approve'><i class='fa fa-check iconStyle tick'></i></button>
                    <button class='buttonStyle reject'><i class='fa fa-times iconStyle cross'></i></button>
                </div>
              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No pending applications.</td></tr>";
                }
                ?>

            </tbody>
        </table>
    </section>

    <script>
        $(document).ready(function() {

            function showLoading() {
                $("#loadingOverlay").fadeIn(200);
            }

            function hideLoading() {
                $("#loadingOverlay").fadeOut(200);
            }

            $(".approve").click(function() {
                let tr = $(this).closest("tr");
                let appId = tr.data("app-id");
                let studentName = tr.find("td:nth-child(2)").text();

                if (confirm("Are you sure you want to APPROVE " + studentName + "'s application?")) {
                    showLoading();

                    $.post("lecturerApproval.php", {
                        action: "approve",
                        appId: appId
                    }, function(response) {
                        hideLoading();

                        if (response === "success") {
                            alert("Successfully approved and email notification sent.");
                            tr.remove();
                            numbering();
                        } else {
                            alert("Approval failed: " + response);
                        }
                    });
                }
            });

            $(".reject").click(function() {
                let tr = $(this).closest("tr");
                let appId = tr.data("app-id");
                let studentName = tr.find("td:nth-child(2)").text();

                let reason = prompt("Please enter a reason for rejecting " + studentName + "'s application:");

                if (reason === null || reason.trim() === "") {
                    alert("Rejection reason is required.");
                    return;
                }

                showLoading();

                $.post("lecturerApproval.php", {
                    action: "reject",
                    appId: appId,
                    remarks: reason
                }, function(response) {
                    hideLoading(); // ✅ 隐藏 loading
                    console.log("Server response:", response);

                    if (response === "success") {
                        alert("Application rejected and student has been notified.");
                        tr.remove();
                        numbering();
                    } else {
                        alert("Rejection failed. Server said: " + response);
                    }
                });
            });


        });


        function numbering() {
            var tbody = document.querySelector("section table tbody");
            var rows = Array.from(tbody.querySelectorAll("tr"));

            var counter = 1;
            rows.forEach(element => {
                element.cells[0].textContent = counter;
                counter += 1;
            });
        }
    </script>
    <!-- Loading Spinner Overlay -->
    <div id="loadingOverlay" style="display:none;">
        <div class="spinner-container">
            <div class="spinner"></div>
            <div class="spinner-text">Processing...</div>
        </div>
    </div>


</body>

</html>