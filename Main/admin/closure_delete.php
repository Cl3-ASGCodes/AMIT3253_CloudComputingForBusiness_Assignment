<?php
require '../config.php';
require '../auth.php';
require '../helpers.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    db_delete('closures', 'id = ?', [$id]);
}

header('Location: closures.php');
exit;
