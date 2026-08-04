<?php
require '../config.php';
require '../auth.php';
require '../helpers.php';

if (!current_user_is_admin()) {
    header('Location: ../login.php');
    exit;
}

$order_ref = $_GET['order_ref'] ?? '';
if (!$order_ref) {
    header('Location: orders.php');
    exit;
}

$order = db_fetch_one('SELECT * FROM orders WHERE order_ref = ?', [$order_ref]);
if (!$order) {
    header('Location: orders.php');
    exit;
}

$tickets = db_fetch_all(
    "SELECT t.*, tt.name AS ticket_type_name, tt.price, e.title AS event_title, e.start_datetime, e.end_datetime, f.name AS facility_name
     FROM tickets t
     JOIN ticket_types tt ON tt.id = t.ticket_type_id
     JOIN events e ON e.id = tt.event_id
     JOIN facilities f ON f.id = e.facility_id
     WHERE t.order_id = ?",
    [$order->id]
);

$pageTitle = 'Order Details';
require 'partials/header.php';
?>
<h1>Order <?= htmlspecialchars($order->order_ref) ?></h1>
<p><strong>Buyer:</strong> <?= htmlspecialchars($order->buyer_name) ?> (<?= htmlspecialchars($order->buyer_email) ?>)</p>
<p><strong>Total Amount:</strong> <?= format_myr($order->total_amount) ?></p>
<p><strong>Status:</strong> <?= htmlspecialchars(ucfirst($order->payment_status)) ?></p>
<p><strong>Created At:</strong> <?= date('d M Y H:i', strtotime($order->created_at)) ?></p>
<h2>Tickets</h2>
<table class="table" style="width:100%; border-collapse:collapse;">
    <thead>
        <tr>
            <th>Ticket Code</th>
            <th>Event</th>
            <th>Venue</th>
            <th>Date &amp; Time</th>
            <th>Type (Price)</th>
            <th>Checked‑in</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tickets as $t): ?>
        <tr>
            <td><?= htmlspecialchars($t->ticket_code) ?></td>
            <td><?= htmlspecialchars($t->event_title) ?></td>
            <td><?= htmlspecialchars($t->facility_name) ?></td>
            <td><?= date('d M Y @ g:i A', strtotime($t->start_datetime)) ?></td>
            <td><?= htmlspecialchars($t->ticket_type_name) ?> (<?= format_myr($t->price) ?>)</td>
            <td><?= $t->is_checked_in ? 'Yes @ '.date('g:i A', strtotime($t->checked_in_at)) : 'No' ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php require '../partials/footer.php'; ?>
