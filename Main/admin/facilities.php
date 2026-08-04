<?php
require '../config.php';
require '../auth.php';
require '../helpers.php';
require_admin();

$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);

$facilities = db_fetch_all('
    SELECT f.*, (SELECT image_url FROM facility_images fi WHERE fi.facility_id = f.id LIMIT 1) AS image_url 
    FROM facilities f 
    ORDER BY f.name
');

$pageTitle = 'Manage Event Venues';
require 'partials/header.php';
?>
<div class="page-header">
<div class="page-header-top">
<div>
<h1>Event Venues & Halls</h1>
<p>Manage venue spaces and capacity configurations.</p>
</div>
<a class="btn btn-small" href="facility_create.php">+ Add Venue</a>
</div>
</div>
<?php if ($flashError): ?><p class="alert alert-error"><?= htmlspecialchars($flashError) ?></p><?php endif; ?>
<table>
<tr><th>Photo</th><th>Name</th><th>Location</th><th>Capacity</th><th>Actions</th></tr>
<?php foreach ($facilities as $f): ?>
<tr>
<td><img class="table-thumb" src="<?= htmlspecialchars(facility_image_url((array)$f)) ?>" alt="<?= htmlspecialchars($f->name) ?>" loading="lazy"></td>
<td><?= htmlspecialchars($f->name) ?></td>
<td><?= htmlspecialchars($f->location) ?></td>
<td><?= (int)$f->capacity ?></td>
<td>
<a class="btn btn-secondary btn-small" href="facility_edit.php?id=<?= (int)$f->id ?>">Edit &amp; Courts</a>
<form action="facility_delete.php" method="post" style="display:inline" onsubmit="return confirm('Delete this facility?');">
<input type="hidden" name="id" value="<?= (int)$f->id ?>">
<button type="submit" class="btn-small btn-danger">Delete</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</table>
<?php require 'partials/footer.php'; ?>
