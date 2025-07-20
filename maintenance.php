<?php
$expensesFile = 'expenses.json';
$backupDir = 'backups/';

// Create backup folder if not exists
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// Generate current month-year
$selectedMonth = $_POST['backup_month'] ?? date('F-Y');
$selectedMonthForBackup = $_POST['backup_month'] ?? date('F-Y');
$message = '';

// Handle Backup
if (isset($_POST['backup_expense'])) {
    $backupMonth = $_POST['backup_month'];
    $parts = explode('-', $backupMonth);
    $month = trim($parts[0]);
    $year = trim($parts[1]);
    $backupFilename = "$month-$year.json";

    $backupPath = $backupDir . $backupFilename;

    if (file_exists($backupPath)) {
        $message = "Backup for $backupMonth already exists.";
    } else {
        if (copy($expensesFile, $backupPath)) {
            $message = "✅ Backup created: $backupFilename";
        } else {
            $message = "❌ Backup failed.";
        }
    }
}

// Handle Purge
if (isset($_POST['purge_expense'])) {
    $backupMonth = date('F-Y'); // Current month
    $backupFilename = $backupDir . str_replace('-', '-', $backupMonth) . '.json';

    // Warn if no backup found
    if (!file_exists($backupFilename)) {
        $message = "⚠️ Cannot purge. No backup found for " . htmlspecialchars($backupMonth);
    } else {
        file_put_contents($expensesFile, '[]');
        $message = "🗑️ Expense data has been cleared.";
    }
}

// Handle Restore
if (isset($_POST['restore_backup'])) {
    $restoreFile = $_POST['restore_month'];
    $source = $backupDir . $restoreFile;

    if (file_exists($source)) {
        if (copy($source, $expensesFile)) {
            $message = "🔄 Restored from: $restoreFile";
        } else {
            $message = "❌ Restore failed.";
        }
    } else {
        $message = "⚠️ Backup file not found.";
    }
}

// Get list of last 3 months for restore
function getLast3Months()
{
    $months = [];
    for ($i = 1; $i <= 3; $i++) {
        $months[] = date('F-Y', strtotime("-$i months"));
    }
    return array_reverse($months); // Most recent last
}

$last3Months = getLast3Months();
$backupOptions = [];

foreach ($last3Months as $m) {
    $backupFile = "$m.json";
    $backupOptions[$backupFile] = $backupFile;
}

// For dropdown
$backupFilesOnServer = glob($backupDir . '*-*.json');
foreach ($backupFilesOnServer as $file) {
    $filename = basename($file);
    if (in_array($filename, $backupOptions)) {
        $backupOptions[$filename] = $filename;
    }
}
asort($backupOptions);
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Maintenance - Backup & Restore</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 30px;
      background: #f9f9f9;
    }
    h1, h2 {
      text-align: center;
    }
    .box {
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      max-width: 500px;
      margin: auto;
      margin-bottom: 30px;
    }
    button {
      padding: 10px 20px;
      margin-top: 10px;
      background-color: #4CAF50;
      color: white;
      border: none;
      cursor: pointer;
      width: 100%;
      font-size: 1em;
    }
    button:hover {
      background-color: #45a049;
    }
    select, input[type="text"] {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      font-size: 1em;
    }
    .message {
      text-align: center;
      margin-top: 20px;
      font-weight: bold;
      color: #ff0000;
    }
    .backup-list {
      margin-top: 10px;
    }
    .backup-list li {
      padding: 8px;
      background: #f1f1f1;
      margin-bottom: 5px;
      border-radius: 4px;
    }
  </style>
</head>
<body>

  <h2>🔧 Maintenance - Backup & Restore</h2>
  <center> <h6><a href="pcdashboard.php">Back to Dashboard</a> | <a href="fms.html">Add Expense</a></h6> </center>

  <?php if (!empty($message)): ?>
    <div class="message"><?= $message ?></div>
  <?php endif; ?>
<p/>
  <!-- 1. Backup Expense -->

<div style="display: flex; justify-content: space-around; flex-wrap: wrap; align-items: center;"
  width="80%">
  <div class="box" width="25%">
    <h3>1. Backup Expense</h3>
    <p>Select the month to back up your current `expenses.json` file.</p>
    <form method="post">
      <label for="backup_month">Select Month:</label>
      <select name="backup_month" id="backup_month" required>
        <?php
        $currentMonth = date('F-Y');
        $allMonths = [];
        for ($i = 0; $i < 12; $i++) {
            $month = date('F-Y', strtotime("-$i months"));
            $selected = ($month === $selectedMonth) ? 'selected' : '';
            echo "<option value='$month' $selected>$month</option>";
        }
        ?>
      </select>
      <button type="submit" name="backup_expense">Backup Expense</button>
    </form>
  </div>

  <!-- 2. Restore Backup -->
  <div class="box" width="25%">
    <h3>2. Restore Backup</h3>
    <p>Select a backup file from the last 3 months to restore your expense data.</p>
    <form method="post">
      <label for="restore_month">Select Backup:</label>
      <select name="restore_month" id="restore_month" required>
        <option value="">-- Select Month --</option>
        <?php foreach ($backupOptions as $filename): ?>
        <option value="<?= $filename ?>"><?= $filename ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" name="restore_backup">Restore Selected Backup</button>
    </form>
  </div>

  <!-- 3. Purge Expense -->
  <div class="box" width="25%">
    <h3>3. Start Fresh (Purge Current Data)</h3>
    <p>This will clear the current `expenses.json`. Only allowed if a backup for this month exists.</p>
    <form method="post" onsubmit="return confirm('Are you sure you want to clear all expenses?');">
      <button type="submit" name="purge_expense" onclick="return confirm('Are you sure? Backup should be taken first.');">
        Purge Expense Data
      </button>
    </form>
  </div>

</div>

</body>
</html>