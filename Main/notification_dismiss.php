<?php
require 'config.php';
require 'auth.php';
require 'helpers.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id  = (int)$_POST['id'];
    $uid = current_user_id();

    db_query('UPDATE notifications SET read_at = NOW() WHERE id = ? AND user_id = ?', [$id, $uid]);
}

header('Location: index.php');
exit;
