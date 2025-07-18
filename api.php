<?php
header("Content-Type: application/json");
$DATA_FILE = "expenses.json";

// Helper to load expenses
function load_expenses() {
    global $DATA_FILE;
    if (!file_exists($DATA_FILE)) {
        file_put_contents($DATA_FILE, "[]");
        return [];
    }
    $json = file_get_contents($DATA_FILE);
    return json_decode($json, true) ?: [];
}

// Helper to save expenses
function save_expenses($data) {
    global $DATA_FILE;
    file_put_contents($DATA_FILE, json_encode($data, JSON_PRETTY_PRINT));
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        echo json_encode(["status" => "error", "message" => "Invalid JSON"]);
        exit;
    }

    // Assign category
    $type = $input['type'] ?? '';
    if (in_array($type, ['UPI', 'Debit Card'])) {
        $input['category'] = 'Savings Account Expenses';
    } elseif (in_array($type, ['UPI CC', 'Credit Card'])) {
        $input['category'] = 'Credit Expenses';
    } else {
        $input['category'] = 'Other';
    }

    $expenses = load_expenses();
    $input['id'] = count($expenses) + 1;
    $expenses[] = $input;
    save_expenses($expenses);

    echo json_encode(["status" => "success"]);
    exit;
}

if ($method === 'GET') {
    echo json_encode(load_expenses());
    exit;
}

echo json_encode(["status" => "error", "message" => "Unsupported method"]);
?>