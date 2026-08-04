<?php
require '../config.php';
require '../auth.php';
require '../helpers.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];

    $facility = db_fetch_one('
        SELECT f.*, (SELECT image_url FROM facility_images fi WHERE fi.facility_id = f.id LIMIT 1) AS image_url 
        FROM facilities f WHERE f.id = ?
    ', [$id]);

    try {
        db_delete('facilities', 'id = ?', [$id]);
        if ($facility && !empty($facility->image_url)) {
            delete_facility_image_file($facility->image_url, __DIR__ . '/../uploads');
        }
    } catch (PDOException $e) {
        $_SESSION['flash_error'] = 'Cannot delete this facility: it still has active bookings, events, or closures referencing it.';
    }
}

header('Location: facilities.php');
exit;
