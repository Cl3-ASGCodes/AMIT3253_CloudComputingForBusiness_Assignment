<?php
require '../config.php';
require '../auth.php';
require '../helpers.php';

// Only admin can access
if (!current_user_is_admin()) {
    header('Location: ../login.php');
    exit;
}

$event_id = (int)($_GET['id'] ?? 0);
if ($event_id) {
    // Delete event (cascade will delete related ticket types, orders, tickets)
    db_delete('DELETE FROM events WHERE id = ?', [$event_id]);
    // Optionally set a flash message
    $_SESSION['flash'] = 'Event deleted successfully.';
}
header('Location: events.php');
exit;
?>
