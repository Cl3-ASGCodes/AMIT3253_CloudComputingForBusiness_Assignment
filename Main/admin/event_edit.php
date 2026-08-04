<?php
require '../config.php';
require '../auth.php';
require '../helpers.php';

// Only admin access
if (!current_user_is_admin()) {
    header('Location: ../login.php');
    exit;
}

$event_id = (int)($_GET['id'] ?? 0);
if (!$event_id) {
    header('Location: events.php');
    exit;
}

$event = db_fetch_one('SELECT * FROM events WHERE id = ?', [$event_id]);
if (!$event) {
    header('Location: events.php');
    exit;
}

// Fetch facilities and courts for dropdowns
$facilities = db_fetch_all('SELECT id, name FROM facilities ORDER BY name');
$courts = db_fetch_all('SELECT id, name, facility_id FROM courts ORDER BY name');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $facility_id = (int)($_POST['facility_id'] ?? 0);
    $court_id = $_POST['court_id'] ? (int)$_POST['court_id'] : null;
    $start = trim($_POST['start_datetime'] ?? '');
    $end = trim($_POST['end_datetime'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');
    $status = $_POST['status'] ?? 'draft';

    if ($title && $facility_id && $start && $end) {
        $sql = "UPDATE events SET title = ?, description = ?, facility_id = ?, court_id = ?, start_datetime = ?, end_datetime = ?, image_url = ?, status = ? WHERE id = ?";
        db_update($sql, [$title, $description, $facility_id, $court_id, $start, $end, $image_url, $status, $event_id]);
        header('Location: events.php');
        exit;
    } else {
        $error = 'Please fill in all required fields.';
    }
}

$pageTitle = 'Edit Event';
require 'partials/header.php';
?>
<h1>Edit Event</h1>
<?php if (!empty($error)): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<form method="post" class="form" style="max-width:800px;">
    <label>Title*:<br><input type="text" name="title" required style="width:100%;" value="<?= htmlspecialchars($event->title) ?>"></label><br><br>
    <label>Description:<br><textarea name="description" rows="4" style="width:100%;"><?= htmlspecialchars($event->description) ?></textarea></label><br><br>
    <label>Facility*:<br>
        <select name="facility_id" required onchange="fetchCourts(this.value)">
            <option value="">Select Facility</option>
            <?php foreach ($facilities as $f): ?>
                <option value="<?= $f->id ?>" <?= $f->id == $event->facility_id ? 'selected' : '' ?>><?= htmlspecialchars($f->name) ?></option>
            <?php endforeach; ?>
        </select>
    </label><br><br>
    <label>Hall/Court (optional):<br>
        <select name="court_id" id="court-select">
            <option value="">No specific court</option>
            <?php foreach ($courts as $c): ?>
                <?php if ($c->facility_id == $event->facility_id): ?>
                    <option value="<?= $c->id ?>" <?= $c->id == $event->court_id ? 'selected' : '' ?>><?= htmlspecialchars($c->name) ?></option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>
    </label><br><br>
    <label>Start Date &amp; Time*:<br><input type="datetime-local" name="start_datetime" required value="<?= date('Y-m-d\TH:i', strtotime($event->start_datetime)) ?>"></label><br><br>
    <label>End Date &amp; Time*:<br><input type="datetime-local" name="end_datetime" required value="<?= date('Y-m-d\TH:i', strtotime($event->end_datetime)) ?>"></label><br><br>
    <label>Image URL:<br><input type="url" name="image_url" style="width:100%;" value="<?= htmlspecialchars($event->image_url) ?>"></label><br><br>
    <label>Status:
        <select name="status">
            <option value="draft" <?= $event->status == 'draft' ? 'selected' : '' ?>>Draft</option>
            <option value="published" <?= $event->status == 'published' ? 'selected' : '' ?>>Published</option>
            <option value="cancelled" <?= $event->status == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
        </select>
    </label><br><br>
    <button type="submit" class="btn btn-primary">Save Changes</button>
    <a href="events.php" class="btn btn-secondary">Cancel</a>
</form>
<script>
function fetchCourts(facilityId) {
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
