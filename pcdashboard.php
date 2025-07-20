<?php
// Load the JSON data
$filename = "expenses.json";
if (!file_exists($filename)) {
    die("Error: expenses.json file not found.");
}

$json_data = file_get_contents($filename);
$expenses = json_decode($json_data, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die("Error: Invalid JSON in expenses.json");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Expense Dashboard</title>
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

    .dashboard-container {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      justify-content: center;
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

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.9em;
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

     .filters {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-bottom: 15px;
    }

    .filters div {
      flex: 1;
      min-width: 200px;
    }

    .filters input, .filters select {
      width: 100%;
      padding: 8px;
      font-size: 0.95em;
    }

    .total {
      text-align: right;
      font-weight: bold;
      margin-top: 10px;
    }
  </style>
</head>
<body>

  <h3>Expense Dashboard</h3> <h6><a href="historyDashboard.php">History Dashboard</a> | <a href="fms.html">Add Expense</a> | <a href="maintenance.php">Maintenance</a></h6>
  
  

  <!-- Filters -->
  <div class="filters" width="80%">
    <div>
      <label for="filterCategory">Filter by Expense Category:</label>
      <select id="filterCategory" onchange="applyFilters()">
        <option value="">All</option>
        <option>Food & Beverages</option>
        <option>Home Maintenance</option>
        <option>Groceries</option>
        <option>Fuel</option>
        <option>Entertainment</option>
        <option>Appliance</option>
        <option>Misc</option>
      </select>
    </div>

    <div>
      <label for="filterCrDr">Filter by Source:</label>
      <select id="filterCrDr" onchange="applyFilters()">
        <option value="">All</option>
        <option value="Expense from Savings">Expense from Savings</option>
        <option value="Expense from Credit">Expense from Credit</option>
        <option value="Other">Other</option>
      </select>
    </div>

    <div>
      <label for="filterMode">Filter by Payment Mode:</label>
      <select id="filterMode" onchange="applyFilters()">
        <option value="">All</option>
        <option value="Credit Card">Credit Card</option>
        <option value="Debit Card">Debit Card</option>
        <option value="UPI">UPI</option>
        <option value="UPI CC">UPI CC</option>
      </select>
    </div>
  </div>

  <div class="total">Total: ₹<span id="totalAmount">0.00</span></div>

  <div class="dashboard-container" style="display: flex; justify-content: space-around; flex-wrap: wrap; align-items: top;"
  width="80%">
    <!-- Expense Table -->
    <div class="table-container" width="100%" style="flex-wrap: wrap; overflow-x: auto;">
      <h2>Expenses</h2>
      <!-- Expense Table -->
      <table id="expensesTable" width="100%">
        <thead>
          <tr>
            <th>Date</th>
            <th>Amount</th>
            <th>Payment Mode</th>
            <th>Description</th>
            <th>Expense Category</th>
            <th>Credit/Debit</th>
          </tr>
        </thead>
        <tbody>
          <!-- Rows will be populated by JS -->
        </tbody>
      </table>
    </div>

    <!-- Category Chart -->
    <div class="chart-container">
      <h2>Expenses by Category</h2>
      <canvas id="categoryChart"></canvas>
    </div>
    <!-- Credit/Debit Chart 
    <div class="chart-container">
      <h2>Expenses by Source (Savings vs Credit)</h2>
      <canvas id="crDrChart"></canvas> --> 
  </div>

  <script>
    const originalExpenses = <?= json_encode($expenses) ?>;

    function applyFilters() {
      const filterCategory = document.getElementById('filterCategory').value;
      const filterCrDr = document.getElementById('filterCrDr').value;
      const filterMode = document.getElementById('filterMode').value;

      const filtered = originalExpenses.filter(expense => {
        const categoryMatch = !filterCategory || expense.category === filterCategory;
        const crDrMatch = !filterCrDr || expense.cr_dr_category === filterCrDr;
        const modeMatch = !filterMode || expense.type === filterMode;
        return categoryMatch && crDrMatch && modeMatch;
      });

      renderTable(filtered);
      updateTotal(filtered);
      updateChart(filtered);
    } 

    function renderTable(expenses) {
        const tbody = document.querySelector("#expensesTable tbody");
        tbody.innerHTML = expenses.map(e => 
        ` <tr>
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

    //Chart baed on expenses category
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

    //chart based on credit/debit category
    function updateCrDrChart(expenses) {
      const totals = {};
      expenses.forEach(e => {
        const cat = e.cr_dr_category || 'Other';
        totals[cat] = (totals[cat] || 0) + e.amount;
      });

      const labels = Object.keys(totals);
      const data = Object.values(totals);

      if (window.crDrChart) {
        window.crDrChart.data.labels = labels;
        window.crDrChart.data.datasets[0].data = data;
        window.crDrChart.update();
        return;
      }

      const ctx = document.getElementById('crDrChart').getContext('2d');
      window.crDrChart = new Chart(ctx, {
        type: 'pie',
        data: {
          labels: labels,
          datasets: [{
            label: 'Expenses by Source',
            data,
            backgroundColor: [
              'rgba(54, 162, 235, 0.6)', // Savings
              'rgba(255, 99, 132, 0.6)', // Credit
              'rgba(200, 200, 200, 0.6)' // Other
            ]
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: 'bottom'
            },
            title: {
              display: true,
              text: 'Expenses by Source (Savings vs Credit)'
            }
          }
        }
      });
    }

    // Initial render
    window.addEventListener('DOMContentLoaded', () => {
      renderTable(originalExpenses);
      updateTotal(originalExpenses);
      updateChart(originalExpenses);
      //updateCrDrChart(originalExpenses);

      document.getElementById('filterCategory').addEventListener('change', applyFilters);
      document.getElementById('filterCrDr').addEventListener('change', applyFilters);
      document.getElementById('filterMode').addEventListener('change', applyFilters);
    });
 </script>

</body>
</html>