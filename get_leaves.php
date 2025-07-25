<?php
header('Content-Type: application/json');

$dataFile = 'leaves.json';
$leaves = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];

// Filter by date range if provided
if (isset($_GET['start']) && isset($_GET['end'])) {
    $start = new DateTime($_GET['start']);
    $end = new DateTime($_GET['end']);
    
    $filtered = [];
    
    foreach ($leaves as $date => $people) {
        $current = new DateTime($date);
        if ($current >= $start && $current <= $end) {
            $filtered[$date] = $people;
        }
    }
    
    $leaves = $filtered;
}

// Return leaves for specific date if requested
if (isset($_GET['date'])) {
    $date = $_GET['date'];
    $response = [
        'leaves' => isset($leaves[$date]) ? $leaves[$date] : []
    ];
} else {
    $response = [
        'leaves' => $leaves
    ];
}

echo json_encode($response);
?>