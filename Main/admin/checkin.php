<?php
require '../config.php';
require '../auth.php';
require '../helpers.php';

// Only admin can access
if (!current_user_is_admin()) {
    header('Location: ../login.php');
    exit;
}

$pageTitle = 'Ticket Check-in';
require '../partials/header.php';
?>
<h1>Ticket Check-in</h1>
<form method="post" action="">
    <label for="ticket_code">Ticket Code:</label>
    <input type="text" id="ticket_code" name="ticket_code" required />
    <button type="submit" class="btn-primary">Check In</button>
</form>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['ticket_code'] ?? '');
    if ($code === '') {
        echo '<p class="error">Please enter a ticket code.</p>';
    } else {
        // Find ticket
        $ticket = db_fetch_one('SELECT t.id, t.is_checked_in, t.checked_in_at, o.order_ref, e.title FROM tickets t JOIN orders o ON t.order_id = o.id JOIN events e ON o.event_id = e.id WHERE t.ticket_code = ?', [$code]);
        if (!$ticket) {
            echo '<p class="error">Ticket not found.</p>';
        } else if ($ticket->is_checked_in) {
            echo '<p class="info">Ticket already checked in at '.htmlspecialchars($ticket->checked_in_at).'.</p>';
        } else {
            // Mark as checked in
            db_execute('UPDATE tickets SET is_checked_in = 1, checked_in_at = NOW() WHERE id = ?', [$ticket->id]);
            echo '<p class="success">Check-in successful for ticket '.htmlspecialchars($code).'.</p>';
            echo '<p>Order: '.htmlspecialchars($ticket->order_ref).', Event: '.htmlspecialchars($ticket->title).'</p>';
        }
    }
}
?>
<?php require '../partials/footer.php'; ?>
