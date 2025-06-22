<?php
session_start();
include("../connect.php");

$search = $_GET['search'] ?? '';
$conditions = [];
$params = [];
$types = '';

if (!empty($search)) {
    $conditions[] = "EquipmentName LIKE ?";
    $params[] = '%' . $search . '%';
    $types .= 's';
}

$whereClause = count($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
$sql = "SELECT EquipmentID, EquipmentName, Picture, Type, Quantity FROM equipment $whereClause";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

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
            padding: 10px 20px;
            align-items: center;
            border-bottom: 1px solid #ccc;
        }

        nav a {
            margin-right: 30px;
            text-decoration: none;
            color: #000;
        }

        nav a.active {
            font-weight: bold;
        }

        .search-filter {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }

        .search-filter input[type="text"] {
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

        @media (max-width: 768px) {
            .equipment-card {
                flex: 1 1 100%;
                max-width: 100%;
            }
        }
    </style>
</head>

<body>
    <header>
        <a href="technicianMainPage.php">
            <img src="../0images/ftmkLogo_Yellow.png" alt="FTMK Logo" class="logo" />
        </a>
        <h1>List Of Equipment</h1>
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
        </div>
    </nav>

    <div class="equipment-container">
        <?php while ($row = $result->fetch_assoc()) :
            $id = htmlspecialchars($row['EquipmentID']);
            $name = htmlspecialchars($row['EquipmentName']);
            $pic = htmlspecialchars($row['Picture']);
            $type = htmlspecialchars($row['Type']);

        ?>
            <button class='equipment-card' data-name="<?= $name ?>" data-type="<?= $type ?>" data-quantity="<?= htmlspecialchars($row['Quantity']) ?>" onclick="handleClick('<?= $id ?>', <?= htmlspecialchars($row['Quantity']) ?>)">
                <img src="../<?= $pic ?>" alt="Equipment Photo">
                <h3><?= $name ?></h3>
            </button>
        <?php endwhile; ?>
    </div>

    <script>
        function handleClick(equipmentId, quantity) {
            if (quantity == 0) {
                alert("This equipment is no available and cannot be serviced.");
                return;
            }
            location.href = 'technicianServiceRequest.php?id=' + equipmentId;
        }
        $(function() {
            let selectedType = "all";

            function filterCards() {
                const keyword = $("#searchBox").val().toLowerCase();

                $(".equipment-card").each(function() {
                    const name = $(this).data("name").toLowerCase();
                    const type = $(this).data("type");

                    const matchSearch = name.includes(keyword);
                    const matchType = selectedType === "all" || type === selectedType;

                    $(this).toggle(matchSearch && matchType);
                });
            }

            $("#searchBox").on("input", filterCards);

            $(".nav-type").on("click", function(e) {
                e.preventDefault();
                $(".nav-type").removeClass("active");
                $(this).addClass("active");
                selectedType = $(this).data("type");
                filterCards();
            });
        });
    </script>
</body>

</html>