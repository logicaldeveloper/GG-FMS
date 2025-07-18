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

    h1, h2, h3 {
      text-align: center;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 30px;
      font-size: 0.9em;
    }

    th, td {
      padding: 10px;
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

    .chart-container {
      position: relative;
      height: 300px;
      margin: auto;
    }

    pre {
      background: #eee;
      padding: 10px;
      overflow-x: auto;
    }

    @media (min-width: 600px) {
      table {
        font-size: 1em;
      }
    }
  </style>
</head>
<body>

  <h1>Expense Dashboard</h1>
 
  <!-- Expense Grid -->
  <h2>Expenses</h2>
    <div style="margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 10px;">
    <div style="flex: 1; min-width: 200px;">
        <label for="filterLocation">Filter by Location:</label>
        <input type="text" id="filterLocation" placeholder="e.g., Bangalore" oninput="applyFilters()">
    </div>
    <div style="flex: 1; min-width: 200px;">
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

    <h3>Total: ₹<span id="totalAmount">0.00</span></h3>
    <table id="expensesTable" border="1" style="width:100%; border-collapse: collapse;">
    <tr>
      <th>Date</th>
      <th>Amount</th>
      <th>Mode</th>
      <th>Bank</th>
      <th>Description</th>
      <th>Location</th>
      <th>Category</th>
    </tr>
    <?php foreach ($expenses as $e): ?>
    <tr>
      <td><?= htmlspecialchars($e['date']) ?></td>
      <td><?= number_format($e['amount'], 2) ?></td>
      <td><?= htmlspecialchars($e['type'] ?? '') ?></td>
      <td><?= htmlspecialchars($e['bank'] ?? '') ?></td>
      <td><?= htmlspecialchars($e['description'] ?? '') ?></td>
      <td><?= htmlspecialchars($e['location']['name'] ?? '') ?></td>
      <td><?= htmlspecialchars($e['category'] ?? 'Other') ?></td>
    </tr>
    <?php endforeach; ?>
  </table>

  <!-- Category Chart -->
  <h2>Expenses by Category</h2>
  <div class="chart-container">
    <canvas id="categoryChart"></canvas>
  </div>

  <script>
    const labels = <?= $labels ?>;
    const data = <?= $data ?>;

    console.log("Labels:", labels);
    console.log("Data:", data);

    if (data.length > 0 && labels.length > 0) {
      const ctx = document.getElementById('categoryChart').getContext('2d');
      new Chart(ctx, {
        type: 'pie',
        data: {
          labels: labels,
          datasets: [{
            label: 'Expenses by Category',
             data,
            backgroundColor: [
              'rgba(54, 162, 235, 0.6)',
              'rgba(255, 99, 132, 0.6)',
              'rgba(200, 200, 200, 0.6)'
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
    } else {
      document.getElementById('categoryChart').parentElement.innerHTML += "<p>No data to display in chart.</p>";
    }
  </script>
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
  }

  function renderTable(expenses) {
    const table = document.getElementById('expensesTable');
    table.innerHTML = `
      <tr>
        <th>Date</th>
        <th>Amount</th>
        <th>Mode</th>
        <th>Bank</th>
        <th>Description</th>
        <th>Location</th>
        <th>Category</th>
      </tr>
      ${expenses.map(e => `
        <tr>
          <td>${e.date}</td>
          <td>${e.amount.toFixed(2)}</td>
          <td>${e.type}</td>
          <td>${e.bank || '-'}</td>
          <td>${e.description}</td>
          <td>${e.location?.name || '-'}</td>
          <td>${e.category || 'Other'}</td>
        </tr>
      `).join('')}
    `;
  }

  function updateTotal(expenses) {
    const total = expenses.reduce((sum, e) => sum + e.amount, 0);
    document.getElementById('totalAmount').textContent = total.toFixed(2);
  }

  // Initial render
  renderTable(originalExpenses);
  updateTotal(originalExpenses);
</script>
</body>
</html>