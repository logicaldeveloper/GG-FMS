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

// Prepare category totals
$categoryTotals = [];
foreach ($expenses as $e) {
    $category = $e['category'] ?? 'Other';
    $amount = $e['amount'] ?? 0;
    $categoryTotals[$category] = ($categoryTotals[$category] ?? 0) + $amount;
}

$labels = json_encode(array_keys($categoryTotals), JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
$data = json_encode(array_values($categoryTotals), JSON_NUMERIC_CHECK);
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

    .table-container, .chart-container {
      flex: 1;
      min-width: 300px;
      background: #fff;
      padding: 15px;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
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

    @media (max-width: 768px) {
      .dashboard-container {
        flex-direction: column;
      }
    }
  </style>
</head>
<body>

  <h3>Expense Dashboard</h3> <h6><a href="fms.html">Add Expense</a></h6>

  <!-- Filters -->
  <div class="filters" width="80%">
    <div width="40%">
      <label for="filterLocation">Filter by Location:</label>
      <input type="text" id="filterLocation" placeholder="e.g., Bangalore" >
    </div>
    <div width="40%">
      <label for="filterMode">Filter by Payment Mode:</label>
      <select id="filterMode" >
        <option value="">All</option>
        <option value="Credit Card">Credit Card</option>
        <option value="Debit Card">Debit Card</option>
        <option value="UPI">UPI</option>
        <option value="UPI CC">UPI CC</option>
      </select>
    </div>
  </div>
  <div class="total">Total: ₹<span id="totalAmount">0.00</span></div>

  <!-- Dashboard Content -->
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
            <th>Location</th>
          </tr>
        </thead>
        <tbody>
          <!-- Rows will be populated by JS -->
        </tbody>
      </table>
    </div>

  <!-- Category Chart -->
<div class="chart-container">
  <canvas id="categoryChart"></canvas>
</div>

<script>
  const originalExpenses = <?= json_encode($expenses) ?>;

  function applyFilters() {
    const filterLocation = document.getElementById('filterLocation').value.trim().toLowerCase();
    const filterMode = document.getElementById('filterMode').value;

    const filtered = originalExpenses.filter(expense => {
      const locationMatch = !filterLocation ||
        (expense.location?.name?.toLowerCase().includes(filterLocation));
      const modeMatch = !filterMode || expense.type === filterMode;
      return locationMatch && modeMatch;
    });

    renderTable(filtered);
    updateTotal(filtered);
    updateChart(filtered);
  }

  function renderTable(expenses) {
    const tbody = document.querySelector("#expensesTable tbody");
    tbody.innerHTML = expenses.map(e => `
      <tr>
        <td>${e.date}</td>
        <td>${e.amount.toFixed(2)}</td>
        <td>${e.type}</td>
        <td>${e.description}</td>
        <td>${e.location?.name || '-'}</td>
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
      const category = e.category || 'Other';
      categoryTotals[category] = (categoryTotals[category] || 0) + e.amount;
    });
    return categoryTotals;
  }

  function updateChart(expenses) {
    const categoryTotals = getCategoryTotals(expenses);
    const labels = Object.keys(categoryTotals);
    const data = Object.values(categoryTotals);

    console.log("Labels:", labels);
    console.log("Data:", data);

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
          data: data,
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
            text: 'Expenses by Category'
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

    document.getElementById('filterLocation').addEventListener('input', applyFilters);
    document.getElementById('filterMode').addEventListener('change', applyFilters);
  });
</script>
 
</body>
</html>