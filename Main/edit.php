<?php
require 'config.php';
require 'auth.php';
require 'helpers.php';
require_login();

$id  = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$uid = current_user_id();
$error = '';

$booking = db_fetch_one('SELECT * FROM bookings WHERE id = ? AND user_id = ?', [$id, $uid]);

if (!$booking) {
    die('Booking not found or you do not have permission to edit it.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $court_id     = (int)$_POST['court_id'];
    $booking_date = $_POST['booking_date'];
    $time_slot_id = (int)$_POST['time_slot_id'];

    $closed = db_fetch_one('
        SELECT id FROM closures
        WHERE court_id = ? AND closure_date = ? AND (time_slot_id = ? OR time_slot_id IS NULL)
    ', [$court_id, $booking_date, $time_slot_id]);

    $slotLookup = db_fetch_one('SELECT label FROM time_slots WHERE id = ?', [$time_slot_id]);

    $booking->court_id     = $court_id;
    $booking->booking_date = $booking_date;
    $booking->time_slot_id = $time_slot_id;

    if ($booking_date < date('Y-m-d')) {
        $error = 'You cannot move a booking to a date in the past.';
    } elseif ($closed) {
        $error = 'This court is closed for the selected date/time slot.';
    } elseif (!$slotLookup) {
        $error = 'Please choose a valid time slot.';
    } elseif (is_slot_in_past($booking_date, $slotLookup->label)) {
        $error = 'That time slot has already passed today. Please choose a later time slot.';
    } else {
        try {
            db_update('bookings', [
                'court_id'     => $court_id,
                'booking_date' => $booking_date,
                'time_slot_id' => $time_slot_id
            ], 'id = ? AND user_id = ?', [$id, $uid]);

            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000 || str_contains($e->getMessage(), '1062')) {
                $error = 'That court has just been booked for this time by someone else. Please choose another.';
            } else {
                $error = 'Could not update the booking. Please try again.';
            }
        }
    }
}

$courts = db_fetch_all('
    SELECT co.id, co.name AS court_name, f.id AS facility_id, f.name AS facility_name
    FROM courts co
    JOIN facilities f ON f.id = co.facility_id
    ORDER BY f.name, co.name
');
$timeSlots = db_fetch_all('SELECT * FROM time_slots ORDER BY sort_order');

$pageTitle = 'Edit Booking';
require 'partials/header.php';
?>
<div class="form-card">
<h1>Edit Booking</h1>
<?php if ($error): ?><p class="alert alert-error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="post">
<input type="hidden" name="id" value="<?= (int)$booking->id ?>">
<label>Court
<select name="court_id" id="court-select" required>
<?php $currentFacility = null; ?>
<?php foreach ($courts as $c): ?>
<?php if ($c->facility_id !== $currentFacility): ?>
<?php if ($currentFacility !== null): ?></optgroup><?php endif; ?>
<optgroup label="<?= htmlspecialchars($c->facility_name) ?>">
<?php $currentFacility = $c->facility_id; ?>
<?php endif; ?>
<option value="<?= (int)$c->id ?>" <?= $c->id == $booking->court_id ? 'selected' : '' ?>><?= htmlspecialchars($c->court_name) ?></option>
<?php endforeach; ?>
<?php if ($courts): ?></optgroup><?php endif; ?>
</select>
</label>
<label>Booking Date <input type="date" name="booking_date" id="booking-date" value="<?= htmlspecialchars($booking->booking_date) ?>" min="<?= date('Y-m-d') ?>" required></label>
<label>Time Slot
<select name="time_slot_id" id="time-slot-select" required>
<?php foreach ($timeSlots as $t): ?>
<option value="<?= (int)$t->id ?>" data-slot-label="<?= htmlspecialchars($t->label) ?>" <?= $t->id == $booking->time_slot_id ? 'selected' : '' ?>><?= htmlspecialchars($t->label) ?></option>
<?php endforeach; ?>
</select>
</label>
<p class="form-hint" id="slot-availability-hint"></p>
<button type="submit">Save Changes</button>
<a class="btn btn-secondary" href="index.php">Cancel</a>
</form>
<script>
(function () {
    var courtSelect = document.getElementById('court-select');
    var dateInput = document.getElementById('booking-date');
    var slotSelect = document.getElementById('time-slot-select');
    var hint = document.getElementById('slot-availability-hint');
    var bookingId = <?= (int)$booking->id ?>;
    var today = '<?= date('Y-m-d') ?>';
    var nowMinutes = <?= (int)date('H') * 60 + (int)date('i') ?>;

    function slotStartMinutes(label) {
        var parts = label.split(' - ')[0].split(':');
        return (parseInt(parts[0], 10) * 60) + parseInt(parts[1], 10);
    }

    function resetOptions() {
        Array.prototype.forEach.call(slotSelect.options, function (opt) {
            if (opt.dataset.originalText) {
                opt.textContent = opt.dataset.originalText;
            }
            opt.disabled = false;
        });
        hint.textContent = '';
    }

    function markDisabled(opt, label) {
        opt.dataset.originalText = opt.dataset.originalText || opt.textContent;
        opt.textContent = opt.dataset.originalText + ' (' + label + ')';
        opt.disabled = true;
    }

    function refreshSlots() {
        var courtId = courtSelect.value;
        var date = dateInput.value;

        resetOptions();

        if (date === today) {
            Array.prototype.forEach.call(slotSelect.options, function (opt) {
                if (opt.dataset.slotLabel && slotStartMinutes(opt.dataset.slotLabel) < nowMinutes) {
                    markDisabled(opt, 'Past');
                }
            });
        }

        if (!courtId || !date) { return; }

        fetch('slot_availability.php?court_id=' + encodeURIComponent(courtId) + '&booking_date=' + encodeURIComponent(date) + '&exclude_booking_id=' + bookingId)
            .then(function (res) { return res.json(); })
            .then(function (data) {
                var unavailable = (data.unavailable_slot_ids || []).map(String);
                Array.prototype.forEach.call(slotSelect.options, function (opt) {
                    if (unavailable.indexOf(opt.value) !== -1 && !opt.disabled) {
                        markDisabled(opt, 'Unavailable');
                    }
                });
                hint.textContent = 'Greyed-out slots are either already booked, closed, or already passed today.';
            })
            .catch(function () { });
    }

    courtSelect.addEventListener('change', refreshSlots);
    dateInput.addEventListener('change', refreshSlots);
    refreshSlots();
})();
</script>
</div>
<?php require 'partials/footer.php'; ?>
