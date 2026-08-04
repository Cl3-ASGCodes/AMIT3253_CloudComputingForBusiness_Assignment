<?php
require '../config.php';
require '../auth.php';
require '../helpers.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];

    $court = db_fetch_one('SELECT facility_id FROM courts WHERE id = ?', [$id]);

    if ($court) {
        try {
            db_delete('courts', 'id = ?', [$id]);
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = 'Cannot delete this court: it still has active bookings, events, or closures referencing it.';
        }

        header('Location: facility_edit.php?id=' . $court->facility_id);
        exit;
    }
}

header('Location: facilities.php');
exit;
