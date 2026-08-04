<?php
require '../config.php';
require '../auth.php';
require '../helpers.php';
require_admin();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$error = '';
$uploadDir = __DIR__ . '/../uploads';

$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);

$facility = db_fetch_one('
    SELECT f.*, (SELECT image_url FROM facility_images fi WHERE fi.facility_id = f.id LIMIT 1) AS image_url 
    FROM facilities f WHERE f.id = ?
', [$id]);

if (!$facility) {
    die('Facility not found.');
}

$courtList = db_fetch_all('SELECT id, name FROM courts WHERE facility_id = ? ORDER BY name', [$id]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $location    = trim($_POST['location'] ?? '');
    $capacity    = (int)($_POST['capacity'] ?? 1);
    $description = trim($_POST['description'] ?? '');
    $features    = trim($_POST['features'] ?? '');

    [$newImageUrl, $uploadError]   = handle_facility_image_upload($_FILES['image'] ?? null, $uploadDir, 'facility');
    [$newLayoutUrl, $layoutError]  = handle_facility_image_upload($_FILES['layout'] ?? null, $uploadDir, 'layout');

    if ($name === '' || $location === '' || $capacity < 1) {
        $error = 'Name and location are required and capacity must be at least 1.';
    } elseif ($uploadError) {
        $error = $uploadError;
    } elseif ($layoutError) {
        $error = $layoutError;
    } else {
        if ($newImageUrl) {
            delete_facility_image_file($facility->image_url, $uploadDir);
            
            // Delete old image record and insert new one
            db_delete('facility_images', 'facility_id = ?', [$id]);
            db_insert('facility_images', [
                'facility_id' => $id,
                'image_url'   => $newImageUrl,
                'description' => 'Main View'
            ]);
        }

        db_update('facilities', [
            'name'        => $name,
            'location'    => $location,
            'capacity'    => $capacity,
            'description' => $description !== '' ? $description : null,
            'features'    => $features !== '' ? $features : null,
            'layout_url'  => $newLayoutUrl ?? $facility->layout_url
        ], 'id = ?', [$id]);

        header('Location: facilities.php');
        exit;
    }
}

$pageTitle = 'Edit Event Venue';
require 'partials/header.php';
?>
<div class="form-card">
<h1>Edit Event Venue</h1>
<?php if ($error): ?><p class="alert alert-error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="post" enctype="multipart/form-data">
<input type="hidden" name="id" value="<?= (int)$facility->id ?>">
<label>Name <input type="text" name="name" value="<?= htmlspecialchars($facility->name) ?>" required></label>
<label>Location <input type="text" name="location" value="<?= htmlspecialchars($facility->location) ?>" required></label>
<label>Max Guest Capacity <input type="number" name="capacity" min="1" value="<?= (int)$facility->capacity ?>" required></label>
<label>Current Photo
<img class="table-thumb" style="width:96px;height:96px;" src="<?= htmlspecialchars(facility_image_url((array)$facility)) ?>" alt="<?= htmlspecialchars($facility->name) ?>">
</label>
<label>Replace Photo <input type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp"></label>
<?php if (facility_layout_url((array)$facility)): ?>
<label>Current Layout Diagram
<img class="table-thumb" style="width:96px;height:96px;" src="<?= htmlspecialchars(facility_layout_url((array)$facility)) ?>" alt="Layout">
</label>
<?php endif; ?>
<label>Replace Layout Diagram <input type="file" name="layout" accept="image/jpeg,image/png,image/gif,image/webp"></label>
<label>Description <textarea name="description" rows="3"><?= htmlspecialchars($facility->description ?? '') ?></textarea></label>
<label>Features &amp; Amenities (one per line) <textarea name="features" rows="4"><?= htmlspecialchars($facility->features ?? '') ?></textarea></label>
<button type="submit">Update Venue</button>
</form>

<h2 style="margin-top:24px;">Halls / Spaces within Venue</h2>
<?php if ($flashError): ?><p class="alert alert-error"><?= htmlspecialchars($flashError) ?></p><?php endif; ?>
<table>
<tr><th>Hall / Space Name</th><th>Actions</th></tr>
<?php foreach ($courtList as $c): ?>
<tr>
<td><?= htmlspecialchars($c->name) ?></td>
<td>
<a class="btn btn-secondary btn-small" href="court_edit.php?id=<?= (int)$c->id ?>">Edit</a>
<form action="court_delete.php" method="post" style="display:inline" onsubmit="return confirm('Delete this court? Any bookings for it must be removed first.');">
<input type="hidden" name="id" value="<?= (int)$c->id ?>">
<button type="submit" class="btn-small btn-danger">Delete</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</table>
<div class="card-actions">
<a class="btn btn-small" href="court_create.php?facility_id=<?= (int)$id ?>">+ Add Hall / Space</a>
</div>

<div class="card-actions" style="margin-top:20px;">
<a class="btn btn-secondary btn-small" href="facilities.php">Back to Facilities</a>
</div>
</div>
<?php require 'partials/footer.php'; ?>
