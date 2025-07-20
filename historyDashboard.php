<?php
$backupDir = 'backups/';
$backupFiles = [];

if (is_dir($backupDir)) {
    $files = scandir($backupDir);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
            $backupFiles[] = $file;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>History Dashboard</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js "></script>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 15px;
      background: #f9f9f9;
    }

    h1, h2 {
      text-align: center;
    }

    .filters, .table-container, .chart-container {
      background: #fff;
      padding: 15px;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      flex: 1;
      min-width: 300px;
    }

    .filters {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
    }

    .filters div {
      flex: 1;
      min-width: 200px;
    }

    label {
      display: block;
      font-weight: bold;
      margin-top: 5px;
    }

    select, input {
      width: 100%;
      padding: 8px;
      margin-top: 5px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.9em;
      margin-top: 10px;
    }

    th, td {
      padding: 8px;
      border: 1px solid #ccc;
      text-align: left;
    }

    th {
      background-color: #f1f1f1;
    }

    canvas {
      max-width: 100%;
      height: auto;
    }

    .dashboard-container {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      justify-content: center;
    }

    .total {
      text-align: right;
      font-weight: bold;
      margin-top: 10px;
    }
  </style>
</head>
<body>

  <h2>📜 Historical Expense Dashboard</h2>
  <center> <h6><a href="pcdashboard.php">Main Dashboard</a> | <a href="fms.html">Add Expense</a></h6> </center>

  <!-- Select Month -->
  <div class="filters">
    <div>
      <label for="backupSelect">Select Month:</label>
      <select id="backupSelect">
        <option value="">-- Select Month --</option>
        <?php foreach ($backupFiles as $file): ?>
        <option value="<?= $file ?>"><?= $file ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <!-- Total -->
  <div class="total">Total: ₹<span id="totalAmount">0.00</span></div>

  <!-- Table and Charts -->
  <div class="dashboard-container">
    <!-- Expense Table -->
    <div class="table-container">
      <h2>Expenses</h2>
      <table id="expensesTable">
        <thead>
          <tr>
            <th>Date</th>
            <th>Amount</th>
            <th>Mode</th>
            <th>Description</th>
            <th>Expense Category</th>
            <th>cr_dr_category</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>

    <!-- Category Chart -->
    <div class="chart-container">
      <h2>Expenses by Category</h2>
      <canvas id="categoryChart"></canvas>
    </div>
  <script>
    const backupSelect = document.getElementById('backupSelect');

    backupSelect.addEventListener('change', () => {
      const selectedFile = backupSelect.value;
      if (!selectedFile) return;

      fetch(`backups/${selectedFile}`)
        .then(res => {
          if (!res.ok) throw new Error("File not found");
          return res.json();
        })
        .then(expenses => {
          renderTable(expenses);
          updateTotal(expenses);
          updateChart(expenses);
        })
        .catch(err => {
          alert("Error loading backup: " + err.message);
          document.getElementById('expensesTable').querySelector('tbody').innerHTML = '';
          document.getElementById('totalAmount').textContent = '0.00';
          if (window.myChart) window.myChart.data.datasets[0].data = [];
        });
    });

    function renderTable(expenses) {
      const tbody = document.querySelector("#expensesTable tbody");
      tbody.innerHTML = expenses.map(e => `
        <tr>
          <td>${e.date}</td>
          <td>${e.amount.toFixed(2)}</td>
          <td>${e.type}</td>
          <td>${e.description}</td>
          <td>${e.category}</td>
          <td>${e.cr_dr_category}</td>
        </tr>
      `).join('');
    }

    function updateTotal(expenses) {
      const total = expenses.reduce((sum, e) => sum + e.amount, 0);
      document.getElementById('totalAmount').textContent = total.toFixed(2);
    }

    function getCategoryTotals(expenses) {
      const categoryTotals = {};
      expenses.forEach(e => {
        const cat = e.category || 'Misc';
        categoryTotals[cat] = (categoryTotals[cat] || 0) + e.amount;
      });
      return categoryTotals;
    }

    function updateChart(expenses) {
      const categoryTotals = getCategoryTotals(expenses);
      const labels = Object.keys(categoryTotals);
      const data = Object.values(categoryTotals);

      if (window.myChart) {
        window.myChart.data.labels = labels;
        window.myChart.data.datasets[0].data = data;
        window.myChart.update();
        return;
      }

      const ctx = document.getElementById('categoryChart').getContext('2d');
      window.myChart = new Chart(ctx, {
        type: 'pie',
         data: {
          labels: labels,
          datasets: [{
            label: 'Expenses by Category',
             data,
            backgroundColor: [
              'rgba(75, 192, 192, 0.6)',
              'rgba(255, 99, 132, 0.6)',
              'rgba(54, 162, 235, 0.6)',
              'rgba(255, 206, 86, 0.6)',
              'rgba(153, 102, 255, 0.6)',
              'rgba(255, 159, 64, 0.6)',
              'rgba(200, 200, 200, 0.6)'
            ]
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: { position: 'bottom' },
            title: { display: true, text: 'Expenses by Category' }
          }
        }
      });
    }

    function getCrDrTotals(expenses) {
      const categoryTotals = {};
      expenses.forEach(e => {
        const cat = e.cr_dr_category || 'Other';
        categoryTotals[cat] = (categoryTotals[cat] || 0) + e.amount;
      });
      return categoryTotals;
    }

  </script>

</body>
</html>