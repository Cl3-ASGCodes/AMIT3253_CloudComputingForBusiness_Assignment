<?php
require '../config.php';
require '../auth.php';
require '../helpers.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    db_delete('bookings', 'id = ?', [$id]);
}

header('Location: bookings.php');
exit;
