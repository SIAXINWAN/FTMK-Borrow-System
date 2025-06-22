<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Borrow Equipment - FTMK Borrow System</title>
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      font-family: Arial, sans-serif;
      margin: 0;
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
      font-size: 28px;
    }

    .logo {
      height: 80px;
    }

    .tabs {
      font-size: 22px;
      display: flex;
      justify-content: center;
      background-color: #f1f1f1;
      border-bottom: 1px solid #ccc;
    }

    .tab-button {
      padding: 15px 30px;
      cursor: pointer;
      background-color: #f1f1f1;
      border: none;
      border-bottom: 4px solid transparent;
      font-weight: bold;
      font-size: 18px;
    }

    .tab-button.active {
      border-bottom: 4px solid #ffcc00;
      background-color: white;
    }

    .tab-content {
      display: none;
      padding: 20px;
    }

    .tab-content.active {
      display: block;
    }

    .filter-row {
      margin: 10px 0;
    }

    select {
      padding: 5px;
      margin-left: 5px;
    }

    canvas {
      background-color: #f9f9f9;
      padding: 10px;
      max-width: 600px;
      max-height: 300px;
      display: block;
      margin: 0 auto;
      border-radius: 10px;
      box-shadow: 0 0 8px rgba(0, 0, 0, 0.1);
    }

    .most-borrowed {
      margin-top: 20px;
      font-weight: bold;
      text-align: center;
      font-size: 18px;
    }

    .most-borrowed span {
      font-weight: normal;
      color: darkblue;
    }

    #table,
    #usageTable {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }

    th,
    td {
      border: 1px solid #000;
      padding: 8px;
      text-align: center;
    }

    #table th,
    #usageTable th {
      background-color: #333;
      color: white;
    }

    tbody tr {
      height: 40px;
    }

    .toggle-btn {
      padding: 8px 16px;
      margin-left: 5px;
      background-color: #e0e0e0;
      border: none;
      font-weight: bold;
      cursor: pointer;
      border-radius: 5px;
    }

    .toggle-btn.active {
      background-color: #ffcc00;
      color: black;
    }

    .pdf-buttons {
      position: fixed;
      bottom: 20px;
      right: 30px;
      display: flex;
      flex-direction: column;
      gap: 10px;
      z-index: 999;
    }

    .pdf-buttons button {
      background-color: #ffcc00;
      color: black;
      border: none;
      padding: 10px 16px;
      font-weight: bold;
      font-size: 14px;
      border-radius: 8px;
      cursor: pointer;
      box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
      transition: background-color 0.3s ease;
    }

    .pdf-buttons button:hover {
      background-color: #e6b800;
    }

    /* 防止按钮被拍进 PDF */
    .no-print {
      display: block;
    }
  </style>
</head>

<body>
  <header>
    <a href="adminMainPage.php">
      <img src="../0images/ftmkLogo_Yellow.png" alt="FTMK Logo" class="logo" />
    </a>
    <h1>Analysis & Report</h1>
  </header>

  <div class="tabs">
    <button class="tab-button active" onclick="showTab('usage')">Equipment Usage</button>
    <button class="tab-button" onclick="showTab('overdue')">Overdue List</button>
  </div>



  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
  <script>
    function exportSectionAsPDF(sectionId, filename) {
      const element = document.getElementById(sectionId);
      const noPrintElems = document.querySelectorAll('.no-print');

      // 隐藏按钮
      noPrintElems.forEach(el => el.style.display = 'none');

      const opt = {
        margin: 0.5,
        filename: filename,
        image: {
          type: 'jpeg',
          quality: 0.98
        },
        html2canvas: {
          scale: 2
        },
        jsPDF: {
          unit: 'in',
          format: 'a4',
          orientation: 'portrait'
        }
      };

      const allTabs = document.querySelectorAll('.tab-content');
      allTabs.forEach(tab => tab.style.display = 'none');
      const target = document.getElementById(sectionId);
      target.style.display = 'block';

      html2pdf().set(opt).from(target).save().then(() => {
        // 恢复原本显示
        allTabs.forEach(tab => {
          if (tab.classList.contains('active')) {
            tab.style.display = 'block';
          } else {
            tab.style.display = 'none';
          }
        });

        // 显示按钮回来
        noPrintElems.forEach(el => el.style.display = 'flex');
      });
    }
  </script>
  <div id="usage" class="tab-content active">
    <h3>Detailed Equipment Usage</h3>
    <div class="filter-row" style="text-align: right;">
      <button onclick="showChart()" id="chartBtn" class="toggle-btn active ">Chart</button>
      <button onclick="showTable()" id="tableBtn" class="toggle-btn">Table</button>
    </div>

    <div class="filter-row">
      Role:
      <select id="roleFilter" onchange="loadUsage()">
        <option value="All">All</option>
        <option value="Student">Student</option>
        <option value="Lecturer">Lecturer</option>
      </select>
      Year:
      <select id="yearFilter" onchange="loadUsage()">
        <option value="All">All</option>
        <option value="2025">2025</option>
        <option value="2024">2024</option>
      </select>
      Month:
      <select id="monthFilter" onchange="loadUsage()">
        <option value="All">All</option>
        <option value="1">January</option>
        <option value="2">February</option>
        <option value="3">March</option>
        <option value="4">April</option>
        <option value="5">May</option>
        <option value="6">June</option>
        <option value="7">July</option>
        <option value="8">August</option>
        <option value="9">September</option>
        <option value="10">October</option>
        <option value="11">November</option>
        <option value="12">December</option>
      </select>
    </div>


    <canvas id="usageChart" width="400" height="200"></canvas>
    <div class="most-borrowed">Most Borrowed Equipment: <span id="mostItem">-</span></div>

    <table id="usageTable">
      <thead>
        <tr>
          <th>No</th>
          <th>Equipment Name</th>
          <th>Borrow Count</th>
        </tr>
      </thead>
      <tbody id="usageBody"></tbody>
    </table>
  </div>

  <div id="overdue" class="tab-content">
    <h2>Overdue List</h2>
    <div class="filter-row">
      Filter:
      <select id="filterSelect" onchange="loadOverdue()">
        <option value="All">All</option>
        <option value="Student">Student</option>
        <option value="Lecturer">Lecturer</option>
      </select>
    </div>
    <table id="table">
      <thead>
        <tr>
          <th>No</th>
          <th>User Name</th>
          <th>Phone</th>
          <th>Equipment Name</th>
          <th>Due Date</th>
        </tr>
      </thead>
      <tbody id="overdueBody">
        <?php include("getOverdueList.php"); ?>
      </tbody>
    </table>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script>
    let usageChart;

    function showTab(tabId) {
      document.querySelectorAll(".tab-button").forEach(btn => btn.classList.remove("active"));
      document.querySelectorAll(".tab-content").forEach(tab => tab.classList.remove("active"));
      document.getElementById(tabId).classList.add("active");
      event.target.classList.add("active");
    }

    function loadOverdue() {
      const filter = document.getElementById("filterSelect").value;
      fetch(`getOverdueList.php?filter=${filter}`)
        .then(res => res.text())
        .then(html => {
          document.getElementById("overdueBody").innerHTML = html;
        });
    }

    function loadUsage() {
      const role = document.getElementById("roleFilter").value;
      const year = document.getElementById("yearFilter").value;
      const month = document.getElementById("monthFilter").value;

      const isChartVisible = document.getElementById("usageChart").style.display !== "none";

      // Chart
      fetch(`getUsageChart.php?role=${role}&year=${year}&month=${month}`)
        .then(res => res.json())
        .then(chartData => {
          const ctx = document.getElementById("usageChart").getContext("2d");
          if (usageChart) usageChart.destroy();
          usageChart = new Chart(ctx, {
            type: "bar",
            data: {
              labels: chartData.labels,
              datasets: [{
                label: "Usage Count",
                data: chartData.data,
                backgroundColor: "lightblue"
              }]
            },
            options: {
              responsive: true,
              scales: {
                y: {
                  beginAtZero: true
                }
              }
            }
          });
          if (chartData.labels && chartData.data && chartData.data.length > 0) {
            const maxCount = Math.max(...chartData.data);
            const mostItems = chartData.labels.filter((label, index) => chartData.data[index] === maxCount);
            document.getElementById("mostItem").innerText = mostItems.length > 0 ? mostItems.join(", ") : "-";
          } else {
            document.getElementById("mostItem").innerText = "-";
          }



          // 控制显示：如果 chart 本来是隐藏的，就继续隐藏
          if (!isChartVisible) {
            document.getElementById("usageChart").style.display = "none";
            document.querySelector(".most-borrowed").style.display = "none";
          }
        });

      // Table
      // Table
      fetch(`getUsageTable.php?role=${role}&year=${year}&month=${month}`)
        .then(res => res.text())
        .then(html => {
          $('#usageTable').DataTable().destroy();
          document.getElementById("usageBody").innerHTML = html;

          $('#usageTable').DataTable({
            pageLength: 5,
            initComplete: function() {
              // 只有在 Chart 是显示状态时，才隐藏表格 wrapper
              if (document.getElementById("usageChart").style.display !== "none") {
                document.getElementById("usageTable_wrapper").style.display = "none";
              }
            }
          });
        });

    }


    function showChart() {
      document.getElementById("usageChart").style.display = "block";
      document.getElementById("usageTable_wrapper").style.display = "none";
      document.querySelector(".most-borrowed").style.display = "block";
      document.getElementById("chartBtn").classList.add("active");
      document.getElementById("tableBtn").classList.remove("active");
    }

    function showTable() {
      document.getElementById("usageChart").style.display = "none";
      document.getElementById("usageTable_wrapper").style.display = "block";
      document.querySelector(".most-borrowed").style.display = "none";
      document.getElementById("tableBtn").classList.add("active");
      document.getElementById("chartBtn").classList.remove("active");
    }

    window.onload = function() {
      $('#usageTable').DataTable({
        pageLength: 5,
        initComplete: function() {
          document.getElementById("usageTable_wrapper").style.display = "none";
        }
      });
      document.querySelector(".most-borrowed").style.display = "block";
      document.getElementById("chartBtn").classList.add("active");
      document.getElementById("tableBtn").classList.remove("active");
      loadOverdue();
      loadUsage();
    }
  </script>
  <div class="pdf-buttons no-print">
    <button onclick="exportSectionAsPDF('usage', 'equipment_usage_report.pdf')">Download Usage as PDF</button>
    <button onclick="exportSectionAsPDF('overdue', 'overdue_list_report.pdf')">Download Overdue as PDF</button>
  </div>

</body>

</html>