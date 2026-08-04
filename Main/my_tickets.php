<?php
require 'config.php';
require 'auth.php';
require 'helpers.php';

$uid = current_user_id();
$userEmail = '';
if ($uid) {
    $uObj = db_fetch_one('SELECT email FROM users WHERE id = ?', [$uid]);
    if ($uObj) { $userEmail = $uObj->email; }
}

$searchRef = trim($_GET['order_ref'] ?? '');
$success = !empty($_GET['success']);

$tickets = [];
if ($uid || $searchRef !== '') {
    $sql = "
        SELECT t.*, o.order_ref, o.total_amount, o.payment_status, tt.name AS ticket_type_name, tt.price,
               e.title AS event_title, e.start_datetime, e.end_datetime, e.image_url,
               f.name AS facility_name, f.location
        FROM tickets t
        JOIN orders o ON o.id = t.order_id
        JOIN ticket_types tt ON tt.id = t.ticket_type_id
        JOIN events e ON e.id = tt.event_id
        JOIN facilities f ON f.id = e.facility_id
        WHERE 1=1
    ";
    $params = [];

    if ($searchRef !== '') {
        $sql .= " AND o.order_ref = ?";
        $params[] = $searchRef;
    } elseif ($uid) {
        $sql .= " AND (o.user_id = ? OR o.buyer_email = ?)";
        $params[] = $uid;
        $params[] = $userEmail;
    }

    $sql .= " ORDER BY e.start_datetime DESC, t.id ASC";
    $tickets = db_fetch_all($sql, $params);
}

$pageTitle = 'My Digital Tickets';
require 'partials/header.php';
?>
<div class="page-header">
<h1>My Digital Event Tickets</h1>
<p>View your active event passes, QR check-in codes, and purchase history.</p>
</div>

<?php if ($success): ?>
<div class="alert alert-warning" style="background-color: #d4edda; color: #155724; border-color: #c3e6cb;">
🎉 <strong>Order Successful!</strong> Your tickets have been issued below. Show your QR Code pass at the venue entrance.
</div>
<?php endif; ?>

<form method="get" class="filter-bar" style="margin-bottom: 24px;">
<label>Lookup Order Ref <input type="text" name="order_ref" placeholder="e.g. ORD-2026-X89F2" value="<?= htmlspecialchars($searchRef) ?>"></label>
<button type="submit">Search Tickets</button>
<?php if ($searchRef !== ''): ?><a href="my_tickets.php" class="btn btn-secondary">Clear</a><?php endif; ?>
</form>

<?php if (empty($tickets)): ?>
<div class="empty-state">
<div class="empty-state-icon">&#127915;</div>
<p>No tickets found. <?= !$uid ? 'Please <a href="login.php">login</a> or search by Order Reference above.' : 'Browse events below to purchase tickets.' ?></p>
<a href="events.php" class="btn" style="margin-top: 12px;">Browse Campus Events</a>
</div>
<?php else: ?>
<div style="display: flex; flex-direction: column; gap: 16px;">
<?php foreach ($tickets as $t): ?>
<div class="card" style="padding: 20px; display: flex; flex-wrap: wrap; gap: 20px; align-items: center; justify-content: space-between;">
<div style="display: flex; gap: 16px; align-items: center;">
<?php if ($t->image_url): ?>
<img src="<?= htmlspecialchars($t->image_url) ?>" alt="" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
<?php else: ?>
<div style="width: 80px; height: 80px; background: #e1e0d9; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem;">🎟️</div>
<?php endif; ?>

<div>
<span class="stat-label">Order Ref: <strong><?= htmlspecialchars($t->order_ref) ?></strong> &middot; Code: <code style="background:var(--border-color); padding:2px 6px; border-radius:4px;"><?= htmlspecialchars($t->ticket_code) ?></code></span>
<h3 style="margin: 4px 0 6px 0; font-size: 1.2rem;"><?= htmlspecialchars($t->event_title) ?></h3>
<p class="stat-label" style="margin: 0;">
📍 <?= htmlspecialchars($t->facility_name) ?> &middot; 📅 <?= htmlspecialchars(date('D, d M Y @ g:i A', strtotime($t->start_datetime))) ?>
</p>
<div style="margin-top: 6px;">
<span class="badge badge-accent"><?= htmlspecialchars($t->ticket_type_name) ?> (<?= format_myr($t->price) ?>)</span>
<?php if ($t->is_checked_in): ?>
<span class="badge badge-good">✓ Checked-in at <?= htmlspecialchars(date('g:i A', strtotime($t->checked_in_at))) ?></span>
<?php else: ?>
<span class="badge badge-neutral">Valid Pass</span>
<?php endif; ?>
</div>
</div>
</div>

<div>
<a href="ticket_view.php?code=<?= urlencode($t->ticket_code) ?>" class="btn" style="display: flex; align-items: center; gap: 6px;">
<span>📱 View Digital QR Pass</span> &rarr;
</a>
</div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php require 'partials/footer.php'; ?>
