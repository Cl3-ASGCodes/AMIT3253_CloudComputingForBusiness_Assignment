<?php
require '../config.php';
require '../auth.php';
require '../helpers.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    $myId = (int)current_user_id();

    if ($id === $myId) {
        $_SESSION['flash_error'] = 'You cannot change your own admin status.';
    } else {
        $user = db_fetch_one('
            SELECT u.id, u.login_id, l.user_type 
            FROM users u 
            LEFT JOIN login l ON l.id = u.login_id 
            WHERE u.id = ?
        ', [$id]);

        if ($user && $user->login_id) {
            $newType = ($user->user_type === 'admin') ? 'guest' : 'admin';
            db_update('login', ['user_type' => $newType], 'id = ?', [$user->login_id]);
        }
    }
}

header('Location: users.php');
exit;
