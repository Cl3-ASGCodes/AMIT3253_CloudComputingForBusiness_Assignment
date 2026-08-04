<?php
require '../config.php';
require '../auth.php';
require '../helpers.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    db_delete('contact_messages', 'id = ?', [$id]);
}

header('Location: messages.php');
exit;
