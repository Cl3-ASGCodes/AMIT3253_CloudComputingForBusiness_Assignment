<?php
require 'config.php';
require 'auth.php';
require 'helpers.php';

$code = $_GET['code'] ?? '';
if (!$code) {
    http_response_code(400);
    echo 'Missing ticket code.';
    exit;
}

// Fetch ticket details
$t = db_fetch_one(
    "SELECT t.*, o.order_ref, o.payment_status, tt.name AS ticket_type_name, tt.price, e.title AS event_title, e.start_datetime, e.end_datetime, e.image_url, f.name AS facility_name, f.location
     FROM tickets t
     JOIN orders o ON o.id = t.order_id
     JOIN ticket_types tt ON tt.id = t.ticket_type_id
     JOIN events e ON e.id = tt.event_id
     JOIN facilities f ON f.id = e.facility_id
     WHERE t.ticket_code = ?",
    [$code]
);

if (!$t) {
    http_response_code(404);
    echo 'Ticket not found.';
    exit;
}

$pageTitle = 'Ticket Pass - ' . $t->event_title;
require 'partials/header.php';
?>
<div class="page-header">
<h1>Ticket Pass</h1>
</div>
<div class="card" style="padding:24px; max-width:600px; margin:auto;">
<?php if ($t->image_url): ?>
<img src="<?= htmlspecialchars($t->image_url) ?>" alt="Event Image" style="width:100%; height:auto; border-radius:8px; margin-bottom:16px;">
<?php endif; ?>
<h2><?= htmlspecialchars($t->event_title) ?></h2>
<p><strong>Venue:</strong> <?= htmlspecialchars($t->facility_name) ?>, <?= htmlspecialchars($t->location) ?></p>
<p><strong>Date & Time:</strong> <?= htmlspecialchars(date('D, d M Y @ g:i A', strtotime($t->start_datetime))) ?></p>
<p><strong>Ticket Type:</strong> <?= htmlspecialchars($t->ticket_type_name) ?> (<?= format_myr($t->price) ?>)</p>
<p><strong>Order Ref:</strong> <?= htmlspecialchars($t->order_ref) ?></p>
<p><strong>Ticket Code:</strong> <code><?= htmlspecialchars($t->ticket_code) ?></code></p>
<?php if ($t->is_checked_in): ?>
<p class="badge badge-good">Checked-in at <?= htmlspecialchars(date('g:i A', strtotime($t->checked_in_at))) ?></p>
<?php else: ?>
<p class="badge badge-neutral">Not Checked-in</p>
<?php endif; ?>
<div style="margin-top:20px; text-align:center;">
<img src="https://chart.googleapis.com/chart?chs=250x250&cht=qr&chl=<?= urlencode($t->ticket_code) ?>" alt="QR Code" style="border:1px solid #ccc; padding:4px; background:#fff;">
<p style="margin-top:8px; font-size:0.9rem; color:#555;">Show this QR code at the venue entrance.</p>
</div>
</div>
<?php require 'partials/footer.php'; ?>
