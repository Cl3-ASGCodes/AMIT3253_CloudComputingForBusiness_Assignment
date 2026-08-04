<?php
require '../config.php';
require '../auth.php';
require '../helpers.php';

// Only admin can access
if (!current_user_is_admin()) {
    header('Location: ../login.php');
    exit;
}

// Fetch facilities for dropdown
$facilities = db_fetch_all('SELECT id, name FROM facilities ORDER BY name');
$courts = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $facility_id = (int)($_POST['facility_id'] ?? 0);
    $court_id = (int)($_POST['court_id'] ?? 0);
    $start = trim($_POST['start_datetime'] ?? '');
    $end = trim($_POST['end_datetime'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');
    $status = $_POST['status'] ?? 'draft';
    $organizer_id = current_user_id();

    // Simple validation (could be expanded)
    if ($title && $facility_id && $start && $end) {
        $sql = "INSERT INTO events (title, description, facility_id, court_id, start_datetime, end_datetime, image_url, organizer_id, status) VALUES (?,?,?,?,?,?,?,?,?)";
        db_insert($sql, [$title, $description, $facility_id, $court_id ?: null, $start, $end, $image_url, $organizer_id, $status]);
        // Redirect after creation
        header('Location: events.php');
        exit;
    } else {
        $error = 'Please fill in all required fields.';
    }
}

$pageTitle = 'Create New Event';
require 'partials/header.php';
?>
<h1>Create New Event</h1>
<?php if (!empty($error)): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<form method="post" class="form" style="max-width:800px;">
    <label>Title*:<br><input type="text" name="title" required style="width:100%;"></label><br><br>
    <label>Description:<br><textarea name="description" rows="4" style="width:100%;"></textarea></label><br><br>
    <label>Facility*:<br>
        <select name="facility_id" required onchange="fetchCourts(this.value)">
            <option value="">Select Facility</option>
            <?php foreach ($facilities as $f): ?>
                <option value="<?= $f->id ?>"><?= htmlspecialchars($f->name) ?></option>
            <?php endforeach; ?>
        </select>
    </label><br><br>
    <label>Hall/Court (optional):<br>
        <select name="court_id" id="court-select">
            <option value="">No specific court</option>
        </select>
    </label><br><br>
    <label>Start Date &amp; Time*:<br><input type="datetime-local" name="start_datetime" required></label><br><br>
    <label>End Date &amp; Time*:<br><input type="datetime-local" name="end_datetime" required></label><br><br>
    <label>Image URL:<br><input type="url" name="image_url" style="width:100%;"></label><br><br>
    <label>Status:
        <select name="status">
            <option value="draft">Draft</option>
            <option value="published" selected>Published</option>
            <option value="cancelled">Cancelled</option>
        </select>
    </label><br><br>
    <button type="submit" class="btn btn-primary">Create Event</button>
    <a href="events.php" class="btn btn-secondary">Cancel</a>
</form>
<script>
function fetchCourts(facilityId) {
    // Simple AJAX fetch to get courts for a facility (requires a small endpoint)
    const courtSelect = document.getElementById('court-select');
    courtSelect.innerHTML = '<option value="">Loading...</option>';
    fetch('../api/get_courts.php?facility_id=' + facilityId)
        .then(r => r.json())
        .then(data => {
            let html = '<option value="">No specific court</option>';
            data.forEach(c => { html += `<option value="${c.id}">${c.name}</option>`; });
            courtSelect.innerHTML = html;
        })
        .catch(() => { courtSelect.innerHTML = '<option value="">Error loading courts</option>'; });
}
</script>
<?php require '../partials/footer.php'; ?>
