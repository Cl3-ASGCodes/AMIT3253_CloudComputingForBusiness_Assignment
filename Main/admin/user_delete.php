<?php
require '../config.php';
require '../auth.php';
require '../helpers.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    $myId = (int)current_user_id();

    if ($id === $myId) {
        $_SESSION['flash_error'] = 'You cannot delete your own account.';
    } else {
        $user = db_fetch_one('SELECT login_id FROM users WHERE id = ?', [$id]);

        db_transaction(function() use ($id, $user) {
            db_delete('bookings', 'user_id = ?', [$id]);
            db_delete('notifications', 'user_id = ?', [$id]);
            db_delete('users', 'id = ?', [$id]);
            if ($user && $user->login_id) {
                db_delete('login', 'id = ?', [$user->login_id]);
            }
        });
    }
}

header('Location: users.php');
exit;
