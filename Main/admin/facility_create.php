<?php
require '../config.php';
require '../auth.php';
require '../helpers.php';
require_admin();

$error = '';
$uploadDir = __DIR__ . '/../uploads';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name          = trim($_POST['name'] ?? '');
    $location      = trim($_POST['location'] ?? '');
    $capacity      = (int)($_POST['capacity'] ?? 1);
    $description   = trim($_POST['description'] ?? '');
    $features      = trim($_POST['features'] ?? '');
    $first_court   = trim($_POST['first_court'] ?? '');

    [$image_url, $uploadError] = handle_facility_image_upload($_FILES['image'] ?? null, $uploadDir, 'facility');

    if ($name === '' || $location === '' || $capacity < 1 || $first_court === '') {
        $error = 'Name, location, capacity and the first court name are all required.';
    } elseif ($uploadError) {
        $error = $uploadError;
    } else {
        db_transaction(function() use ($name, $location, $capacity, $description, $features, $image_url, $first_court) {
            $facility_id = db_insert('facilities', [
                'name'        => $name,
                'location'    => $location,
                'capacity'    => $capacity,
                'description' => $description !== '' ? $description : null,
                'features'    => $features !== '' ? $features : null
            ]);

            if ($image_url) {
                db_insert('facility_images', [
                    'facility_id' => $facility_id,
                    'image_url'   => $image_url,
                    'description' => 'Main View'
                ]);
            }

            db_insert('courts', [
                'facility_id' => $facility_id,
                'name'        => $first_court,
                'location'    => $location
            ]);
        });

        header('Location: facilities.php');
        exit;
    }
}

$pageTitle = 'Add Event Venue';
require 'partials/header.php';
?>
<div class="form-card">
<h1>Add Event Venue</h1>
<p class="stat-label">A facility represents an event venue (e.g., "Dewan Tunku Abdul Rahman"). Add its first court/hall below, and manage additional halls on the venue edit page.</p>
<?php if ($error): ?><p class="alert alert-error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="post" enctype="multipart/form-data">
<label>Venue Name <input type="text" name="name" placeholder="e.g. Multi-Purpose Hall" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required></label>
<label>Location <input type="text" name="location" placeholder="e.g. Arena Level 2" value="<?= htmlspecialchars($_POST['location'] ?? '') ?>" required></label>
<label>Max Guest Capacity <input type="number" name="capacity" min="1" value="<?= htmlspecialchars($_POST['capacity'] ?? '100') ?>" required></label>
<label>First Hall / Space Name <input type="text" name="first_court" placeholder="e.g. Main Hall" value="<?= htmlspecialchars($_POST['first_court'] ?? '') ?>" required></label>
<label>Photo <input type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp"></label>
<label>Description <textarea name="description" rows="3" placeholder="Shown on the public venue showcase page"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea></label>
<label>Features &amp; Amenities (one per line) <textarea name="features" rows="4" placeholder="LED Screen, PA System, Holding Room..."><?= htmlspecialchars($_POST['features'] ?? '') ?></textarea></label>
<button type="submit">Add Venue</button>
</form>
<div class="card-actions">
<a class="btn btn-secondary btn-small" href="facilities.php">Back to Facilities</a>
</div>
</div>
<?php require 'partials/footer.php'; ?>
