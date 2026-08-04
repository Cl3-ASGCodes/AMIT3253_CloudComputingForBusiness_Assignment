<?php
require 'config.php';
require 'auth.php';
require 'helpers.php';
require_login();

$error = '';
$selectedCourt    = (int)($_GET['court_id'] ?? 0);
$selectedFacility = (int)($_GET['facility_id'] ?? 0);
$selectedDate     = $_GET['booking_date'] ?? date('Y-m-d');
$fullDayToggle    = (bool)($_GET['full_day'] ?? false);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $court_id     = (int)$_POST['court_id'];
    $booking_date = $_POST['booking_date'];
    $full_day     = isset($_POST['full_day']) ? 1 : 0;
    $start_time   = $_POST['start_time'] ?? null; // HH:MM
    $end_time     = $_POST['end_time'] ?? null;   // HH:MM
    $reason       = trim($_POST['reason'] ?? '');
    $uid          = current_user_id();

    $selectedCourt = $court_id;
    $selectedDate  = $booking_date;
    $fullDayToggle = (bool)$full_day;

    // Basic validation
    if ($booking_date === '' || $court_id < 1) {
        $error = 'Please choose a court and date.';
    } elseif ($booking_date < date('Y-m-d')) {
        $error = 'You cannot book a date in the past.';
    } else {
        if ($full_day) {
            $start_datetime = "$booking_date 00:00:00";
            $end_datetime   = "$booking_date 23:59:59";
        } else {
            if (!$start_time || !$end_time) {
                $error = 'Please provide start and end times for a partial‑day booking.';
            } else {
                $start_dt = DateTime::createFromFormat('Y-m-d H:i', "$booking_date $start_time");
                $end_dt   = DateTime::createFromFormat('Y-m-d H:i', "$booking_date $end_time");
                if (!$start_dt || !$end_dt) {
                    $error = 'Invalid time format.';
                } elseif ($end_dt <= $start_dt) {
                    $error = 'End time must be after start time.';
                } else {
                    $start_datetime = $start_dt->format('Y-m-d H:i:s');
                    $end_datetime   = $end_dt->format('Y-m-d H:i:s');
                }
            }
        }
    }

    if ($error === '') {
        // Check for closures that block the requested period
        $closed = db_fetch_one(
            "SELECT id FROM closures WHERE court_id = ? AND closure_date = ?",
            [$court_id, $booking_date]
        );
        if ($closed) {
            $error = 'This court is closed for the selected date.';
        } else {
            try {
                $newBookingId = db_insert('bookings', [
                    'user_id'      => $uid,
                    'court_id'     => $court_id,
                    'booking_date' => $booking_date,
                    'full_day'     => $full_day,
                    'start_datetime'=> $start_datetime,
                    'end_datetime' => $end_datetime,
                    'reason'       => $reason
                ]);
                header('Location: confirmation.php?id=' . $newBookingId);
                exit;
            } catch (PDOException $e) {
                $error = 'Could not save the booking. Please try again.';
            }
        }
    }
}

$courts = db_fetch_all(
    "SELECT co.id, co.name AS court_name, f.id AS facility_id, f.name AS facility_name " .
    "FROM courts co JOIN facilities f ON f.id = co.facility_id ORDER BY f.name, co.name"
);

// If a facility was passed but no specific court, default to its first court.
if (!$selectedCourt && $selectedFacility) {
    foreach ($courts as $c) {
        if ($c->facility_id == $selectedFacility) {
            $selectedCourt = $c->id;
            break;
        }
    }
}

$pageTitle = 'New Booking';
require 'partials/header.php';
?>
<div class="form-card">
    <h1>New Facility Booking</h1>
    <?php if ($error): ?>
        <p class="alert alert-error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form method="post" id="booking-form">
        <label>Court
            <select name="court_id" id="court-select" required>
                <?php $currentFacility = null; ?>
                <?php foreach ($courts as $c): ?>
                    <?php if ($c->facility_id !== $currentFacility): ?>
                        <?php if ($currentFacility !== null): ?></optgroup><?php endif; ?>
                        <optgroup label="<?php echo htmlspecialchars($c->facility_name); ?>">
                        <?php $currentFacility = $c->facility_id; ?>
                    <?php endif; ?>
                    <option value="<?php echo (int)$c->id; ?>" <?php echo $c->id == $selectedCourt ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($c->court_name); ?>
                    </option>
                <?php endforeach; ?>
                <?php if ($courts): ?></optgroup><?php endif; ?>
            </select>
        </label>
        <label>Booking Date
            <input type="date" name="booking_date" id="booking-date" value="<?php echo htmlspecialchars($selectedDate); ?>" min="<?php echo date('Y-m-d'); ?>" required>
        </label>
        <label>Full Day
            <input type="checkbox" name="full_day" id="full-day-toggle" <?php echo $fullDayToggle ? 'checked' : ''; ?> >
        </label>
        <div id="partial-time-fields" style="margin-top:0.5rem;<?php echo $fullDayToggle ? 'display:none;' : ''; ?>">
            <label>Start Time
                <input type="time" name="start_time" id="start-time" value="<?php echo $_POST['start_time'] ?? ''; ?>" <?php echo $fullDayToggle ? '' : 'required'; ?> >
            </label>
            <label>End Time
                <input type="time" name="end_time" id="end-time" value="<?php echo $_POST['end_time'] ?? ''; ?>" <?php echo $fullDayToggle ? '' : 'required'; ?> >
            </label>
        </div>
        <label>Reason (optional)
            <textarea name="reason" rows="2" placeholder="Why are you booking this venue?"><?php echo htmlspecialchars($_POST['reason'] ?? ''); ?></textarea>
        </label>
        <p class="form-hint" id="slot-availability-hint"></p>
        <button type="submit" class="btn-primary">Book Now</button>
    </form>
    <script>
    (function(){
        const fullDay = document.getElementById('full-day-toggle');
        const partialDiv = document.getElementById('partial-time-fields');
        const startInput = document.getElementById('start-time');
        const endInput = document.getElementById('end-time');
        function toggleFields(){
            if(fullDay.checked){
                partialDiv.style.display='none';
                startInput.required=false; endInput.required=false;
            } else {
                partialDiv.style.display='block';
                startInput.required=true; endInput.required=true;
            }
        }
        fullDay.addEventListener('change', toggleFields);
        toggleFields();
    })();
    </script>
    <div class="card-actions">
        <a class="btn btn-secondary btn-small" href="schedule.php<?php echo $selectedFacility ? '?facility_id=' . (int)$selectedFacility : '';?>">View Schedule</a>
        <a class="btn btn-secondary btn-small" href="index.php">Back to Home</a>
    </div>
</div>
<?php require 'partials/footer.php'; ?>
