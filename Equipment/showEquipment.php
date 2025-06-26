<?php
session_start();
include("../connect.php");

if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'Student':
            $detailsPage = "../1student/studentBorrowEquipment.php";
            break;
        case 'Lecturer':
            $detailsPage = "../2lecturer/lecturerBorrowEquipment.php";
            break;
        default:
            $detailsPage = "../index.php";
    }
} else {
    $detailsPage = "index.php";
}

$statusFilter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$conditions = [];
$params = [];
$types = '';

if ($statusFilter === 'Available') {
    $conditions[] = "AvailabilityStatus = 1";
} elseif ($statusFilter === 'Not Available') {
    $conditions[] = "AvailabilityStatus = 0";
}

if (!empty($search)) {
    $conditions[] = "EquipmentName LIKE ?";
    $params[] = '%' . $search . '%';
    $types .= 's';
}

$whereClause = count($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
$sql = "SELECT EquipmentID, EquipmentName, Picture, AvailabilityStatus, Type FROM equipment $whereClause";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Pending application count
$userId = $_SESSION['UserID'];
$hasPending = false;

$pendingSql = "SELECT COUNT(*) AS count FROM borrow_applications WHERE UserID = ? AND ApplicationStatus = 'Pending'";
$stmt2 = $conn->prepare($pendingSql);
$stmt2->bind_param("s", $userId);
$stmt2->execute();
$pendingResult = $stmt2->get_result();

if ($pendingResult && $row = $pendingResult->fetch_assoc()) {
    $hasPending = $row['count'] > 0;
}

$stmt->close();
$stmt2->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Equipment - FTMK Borrow System</title>
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

        nav {
            display: flex;
            flex-wrap: wrap;
            border-bottom: 1px solid #ccc;
            padding: 10px 20px;
            align-items: center;
        }

        nav a {
            margin-right: 40px;
            text-decoration: none;
            color: #000;

        }


        nav a.active {
            font-weight: bold;
            text-decoration: none;

        }


        .search-filter {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }

        .search-filter input[type="text"],
        .search-filter select {
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        .equipment-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            padding: 20px;
            gap: 20px;
        }

        .equipment-card {
            flex: 1 1 calc(33.33% - 40px);
            max-width: calc(33.33% - 40px);
            min-width: 200px;
            background: none;
            border: none;
            text-align: center;
            cursor: pointer;
            padding: 10px;
            border-radius: 10px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .equipment-card:hover {
            transform: scale(1.03);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            background-color: #fff;
        }

        .equipment-card img {
            width: 100px;
            height: 100px;
            border-radius: 10px;
            object-fit: contain;
            margin-bottom: 10px;
        }

        .equipment-card h4 {
            margin: 5px 0;
            color: #333;
        }

        .available {
            color: limegreen;
        }

        .not-available {
            color: red;
        }

        @media (max-width: 768px) {
            .equipment-card {
                flex: 1 1 100%;
                max-width: 100%;
            }
        }

        .disabled-card {
            cursor: not-allowed;
            opacity: 0.6;
            pointer-events: none;
        }

        .disabled-card:hover {
            transform: none;
            box-shadow: none;
            background-color: inherit;
        }
    </style>
</head>

<body>
    <header>
        <?php
        if (isset($_SESSION['role'])) {
            switch ($_SESSION['role']) {
                case 'Student':
                    $homeLink = "../1student/studentMainPage.php";
                    break;
                case 'Lecturer':
                    $homeLink = "../2lecturer/lecturerMainPage.php";
                    break;
            }
        } else {
            $homeLink = "../index.php";
        }
        ?>
        <a href="<?php echo $homeLink; ?>">
            <img src="../0images/ftmkLogo_Yellow.png" alt="FTMK Logo" class="logo" /></a>
        <h1>List Of Equipment Available to Borrow</h1>
    </header>

    <nav>
        <a href="#" class="nav-type active" data-type="all">All</a>
        <a href="#" class="nav-type" data-type="Camera">Camera</a>
        <a href="#" class="nav-type" data-type="Camera Accessory">Camera Accessory</a>
        <a href="#" class="nav-type" data-type="Audio Equipment">Audio Equipment</a>
        <a href="#" class="nav-type" data-type="Computing">Computing</a>
        <a href="#" class="nav-type" data-type="Presentation">Presentation</a>
        <a href="#" class="nav-type" data-type="Wireless Equipment">Wireless Equipment</a>
        <a href="#" class="nav-type" data-type="Dongle">Dongle</a>
        <div class="search-filter">
            <input type="text" id="searchBox" placeholder="Search by name" value="<?= htmlspecialchars($search) ?>" />
            <label for="status">Filter</label>
            <select id="status">
                <option value="all">All</option>
                <option value="available">Available</option>
                <option value="not available">Not Available</option>
            </select>
        </div>

    </nav>


    <div class="equipment-container">
        <?php
        while ($row = $result->fetch_assoc()) {
            $id = htmlspecialchars($row['EquipmentID']);
            $name = htmlspecialchars($row['EquipmentName']);
            $pic = htmlspecialchars($row['Picture']);
            $status = $row['AvailabilityStatus'];
            $statusText = $status == 1 ? "Available" : "Not Available";
            $statusClass = $status == 1 ? "available" : "not-available";

            $clickable = $status == 1 ? "onclick=\"location.href='$detailsPage?id=$id'\"" : "";
            $disabledClass = $status == 1 ? "" : " disabled-card";

            $type = htmlspecialchars($row['Type']);


            echo "<button class='equipment-card$disabledClass' data-id='$id' data-name='$name' data-status='$statusText' data-type='$type' $clickable>";
            echo "<img src='../$pic' alt='Equipment Photo'>";
            echo "<h3>$name</h3>";
            echo "<div class='$statusClass'>$statusText</div>";
            echo "</button>";
        }
        ?>
    </div>
    <script>
        $(function() {
            let selectedType = "all";

            function filterCards() {
                const keyword = $("#searchBox").val().toLowerCase();
                const status = $("#status").val();

                $(".equipment-card").each(function() {
                    const name = $(this).data("name").toString().toLowerCase();
                    const equipStatus = $(this).data("status").toString().toLowerCase();
                    const equipType = $(this).data("type").toString();

                    const matchSearch = name.includes(keyword);
                    const matchStatus = (status === "all") ||
                        (status === "available" && equipStatus === "available") ||
                        (status === "not available" && equipStatus === "not available");
                    const matchType = (selectedType === "all") || (equipType === selectedType);

                    $(this).toggle(matchSearch && matchStatus && matchType);
                });
            }

            $("#searchBox").on("input", filterCards);
            $("#status").on("change", filterCards);

            $(".nav-type").on("click", function(e) {
                e.preventDefault();
                $(".nav-type").removeClass("active");
                $(this).addClass("active");
                selectedType = $(this).data("type");
                filterCards();
            });

            <?php if ($hasPending): ?>
                alert("You have a pending borrow application. Please wait for approval before proceeding.");
                window.location.href = "<?= $homeLink ?>";
            <?php endif; ?>
        });
    </script>



</body>

</html>