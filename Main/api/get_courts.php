<?php
// API endpoint to fetch courts for a given facility (JSON response)
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../helpers.php';

$facility_id = (int)($_GET['facility_id'] ?? 0);
if ($facility_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit;
}

$courts = db_fetch_all('SELECT id, name FROM courts WHERE facility_id = ? ORDER BY name', [$facility_id]);
header('Content-Type: application/json');
echo json_encode($courts);
?>
