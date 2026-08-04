<?php
require '../config.php';
require '../auth.php';
require '../helpers.php';
require_admin();

$facility_id = (int)($_GET['facility_id'] ?? $_POST['facility_id'] ?? 0);
$error = '';

$facility = db_fetch_one('SELECT * FROM facilities WHERE id = ?', [$facility_id]);

if (!$facility) {
    die('Facility not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');

    if ($name === '') {
        $error = 'Court / Hall name is required.';
    } else {
        db_insert('courts', [
            'facility_id' => $facility_id,
            'name'        => $name,
            'location'    => $facility->location
        ]);
        header('Location: facility_edit.php?id=' . $facility_id);
        exit;
    }
}

$pageTitle = 'Add Hall / Court';
require 'partials/header.php';
?>
<div class="form-card">
<h1>Add Hall / Court</h1>
<p class="stat-label">Venue: <?= htmlspecialchars($facility->name) ?></p>
<?php if ($error): ?><p class="alert alert-error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="post">
<input type="hidden" name="facility_id" value="<?= (int)$facility_id ?>">
<label>Hall / Space Name <input type="text" name="name" placeholder="e.g. TA-101, Main Auditorium" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required></label>
<button type="submit">Add Hall / Space</button>
</form>
<div class="card-actions">
<a class="btn btn-secondary btn-small" href="facility_edit.php?id=<?= (int)$facility_id ?>">Back to Venue</a>
</div>
</div>
<?php require 'partials/footer.php'; ?>
