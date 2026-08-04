<?php
require '../config.php';
require '../auth.php';
require '../helpers.php';
require_admin();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$error = '';

$court = db_fetch_one('
    SELECT c.id, c.name, c.facility_id, f.name AS facility_name
    FROM courts c
    JOIN facilities f ON f.id = c.facility_id
    WHERE c.id = ?
', [$id]);

if (!$court) {
    die('Court not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');

    if ($name === '') {
        $error = 'Court / Hall name is required.';
        $court->name = $name;
    } else {
        db_update('courts', ['name' => $name], 'id = ?', [$id]);
        header('Location: facility_edit.php?id=' . $court->facility_id);
        exit;
    }
}

$pageTitle = 'Edit Hall / Court';
require 'partials/header.php';
?>
<div class="form-card">
<h1>Edit Hall / Court</h1>
<p class="stat-label">Venue: <?= htmlspecialchars($court->facility_name) ?></p>
<?php if ($error): ?><p class="alert alert-error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="post">
<input type="hidden" name="id" value="<?= (int)$court->id ?>">
<label>Hall / Space Name <input type="text" name="name" value="<?= htmlspecialchars($court->name) ?>" required></label>
<button type="submit">Update Hall / Space</button>
</form>
<div class="card-actions">
<a class="btn btn-secondary btn-small" href="facility_edit.php?id=<?= (int)$court->facility_id ?>">Back to Venue</a>
</div>
</div>
<?php require 'partials/footer.php'; ?>
