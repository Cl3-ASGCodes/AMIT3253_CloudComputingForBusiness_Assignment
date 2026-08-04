<?php
require 'config.php';
require 'auth.php';
require 'helpers.php';
require_login();

header('Content-Type: application/json');

$court_id  = (int)($_GET['court_id'] ?? 0);
$date      = $_GET['booking_date'] ?? '';
$excludeId = (int)($_GET['exclude_booking_id'] ?? 0);

if ($court_id < 1 || $date === '') {
    echo json_encode(['unavailable_slot_ids' => []]);
    exit;
}

$bookedRows = db_fetch_all(
    'SELECT time_slot_id FROM bookings WHERE court_id = ? AND booking_date = ? AND id != ?',
    [$court_id, $date, $excludeId]
);
$booked = array_map(fn($r) => (int)$r->time_slot_id, $bookedRows);

$closures = db_fetch_all(
    'SELECT time_slot_id FROM closures WHERE court_id = ? AND closure_date = ?',
    [$court_id, $date]
);

$closedAllDay = false;
$closedSlotIds = [];
foreach ($closures as $c) {
    if ($c->time_slot_id === null) {
        $closedAllDay = true;
    } else {
        $closedSlotIds[] = (int)$c->time_slot_id;
    }
}

if ($closedAllDay) {
    $allSlots = db_fetch_all('SELECT id FROM time_slots');
    $unavailable = array_map(fn($s) => (int)$s->id, $allSlots);
} else {
    $unavailable = array_values(array_unique(array_merge($booked, $closedSlotIds)));
}

echo json_encode(['unavailable_slot_ids' => $unavailable]);
