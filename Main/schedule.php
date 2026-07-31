<?php
require 'config.php';
require 'auth.php';
require 'helpers.php';

// Retrieve all available event venues
$facilities = db_fetch_all('SELECT * FROM facilities ORDER BY name');

$selectedFacilityId = (int)($_GET['facility_id'] ?? ($facilities[0]->id ?? 0));
$selectedDate = $_GET['date'] ?? date('Y-m-d');

// Retrieve halls/spaces associated with the selected venue
$courts = db_fetch_all('SELECT id, name FROM courts WHERE facility_id = ? ORDER BY name', [$selectedFacilityId]);

// Retrieve time/hourly slots
$timeSlots = db_fetch_all('SELECT * FROM time_slots ORDER BY sort_order');

$uid = current_user_id();

$courtIds = array_map(fn($c) => $c->id, $courts);
$bookedSlots = []; // [court_id][time_slot_id] = user_id
$closedSlots = []; // [court_id][time_slot_id] = reason
$wholeDayClosed = []; // [court_id] = reason

if (!empty($courtIds)) {
    $placeholders = implode(',', array_fill(0, count($courtIds), '?'));

    // Fetch existing bookings for the specified date
    $bookingParams = array_merge($courtIds, [$selectedDate]);
    $bookings = db_fetch_all("SELECT court_id, time_slot_id, user_id FROM bookings WHERE court_id IN ($placeholders) AND booking_date = ?", $bookingParams);
    foreach ($bookings as $b) {
        $bookedSlots[$b->court_id][$b->time_slot_id] = $b->user_id;
    }

    // Fetch venue/hall closures for the specified date
    $closureParams = array_merge($courtIds, [$selectedDate]);
    $closures = db_fetch_all("SELECT court_id, time_slot_id, reason FROM closures WHERE court_id IN ($placeholders) AND closure_date = ?", $closureParams);
    foreach ($closures as $cl) {
        if ($cl->time_slot_id === null) {
            $wholeDayClosed[$cl->court_id] = $cl->reason;
        } else {
            $closedSlots[$cl->court_id][$cl->time_slot_id] = $cl->reason;
        }
    }
}

$pageTitle = 'Venue Hourly Schedule';
require 'partials/header.php';
?>
<div class="page-header">
<h1>Venue Hourly Schedule</h1>
<p>Check available, booked, and closed event spaces by hour before submitting a reservation.</p>
</div>

<form method="get" class="filter-bar">
<label>Event Venue
<select name="facility_id" onchange="this.form.submit()">
<?php foreach ($facilities as $f): ?>
<option value="<?= (int)$f->id ?>" <?= $f->id == $selectedFacilityId ? 'selected' : '' ?>><?= htmlspecialchars($f->name) ?></option>
<?php endforeach; ?>
</select>
</label>
<label>Date <input type="date" name="date" value="<?= htmlspecialchars($selectedDate) ?>" min="<?= date('Y-m-d') ?>" onchange="this.form.submit()"></label>
<noscript><button type="submit">View</button></noscript>
</form>

<?php if (empty($courts)): ?>
<div class="empty-state">
<div class="empty-state-icon">&#128269;</div>
<p>This event venue has no halls or spaces configured yet.</p>
</div>
<?php else: ?>
<div style="overflow-x:auto;">
<table class="schedule-table">
<tr>
<th>Hours / Time Window</th>
<?php foreach ($courts as $court): ?>
<th class="schedule-court-th"><?= htmlspecialchars($court->name) ?></th>
<?php endforeach; ?>
</tr>
<?php foreach ($timeSlots as $t): ?>
<tr>
<td><?= htmlspecialchars($t->label) ?></td>
<?php foreach ($courts as $court): ?>
<?php
$courtId = $court->id;
$slotId = $t->id;

if (isset($wholeDayClosed[$courtId]) || isset($closedSlots[$courtId][$slotId])) {
    $status = 'Closed';
    $reason = $wholeDayClosed[$courtId] ?? $closedSlots[$courtId][$slotId];
    $badgeClass = 'badge-critical';
    $icon = '&#10005;';
} elseif (isset($bookedSlots[$courtId][$slotId])) {
    $isMine = $uid && $bookedSlots[$courtId][$slotId] == $uid;
    $status = $isMine ? 'Booked by you' : 'Booked';
    $reason = null;
    $badgeClass = $isMine ? 'badge-accent' : 'badge-neutral';
    $icon = $isMine ? '&#10003;' : '&#9679;';
} else {
    $status = 'Available';
    $reason = null;
    $badgeClass = 'badge-good';
    $icon = '&#10003;';
}
?>
<td class="schedule-court-td">
<div class="schedule-cell">
<span class="badge <?= $badgeClass ?>"><?= $icon ?> <?= htmlspecialchars($status) ?></span>
<?php if ($reason): ?><span class="stat-label"><?= htmlspecialchars($reason) ?></span><?php endif; ?>
<?php if ($status === 'Available' && $selectedDate >= date('Y-m-d')): ?>
<?php if ($uid): ?>
<a class="btn btn-small" href="create.php?court_id=<?= (int)$courtId ?>&booking_date=<?= htmlspecialchars($selectedDate) ?>&time_slot_id=<?= (int)$slotId ?>">Book Slot</a>
<?php else: ?>
<a class="btn btn-small" href="login.php">Login to Book</a>
<?php endif; ?>
<?php endif; ?>
</div>
</td>
<?php endforeach; ?>
</tr>
<?php endforeach; ?>
</table>
</div>
<?php endif; ?>

<div class="card-actions" style="margin-top:20px;">
<a class="btn btn-secondary btn-small" href="index.php">Back to Home</a>
</div>
<?php require 'partials/footer.php'; ?>