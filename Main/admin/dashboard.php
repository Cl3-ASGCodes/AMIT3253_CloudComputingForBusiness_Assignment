<?php
require '../config.php';
require '../auth.php';
require '../helpers.php';
require_admin();

$totalRevenue = (float)db_fetch_value("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'paid'");
$totalTicketsSold = db_count('tickets');
$totalBookings = db_count('bookings');
$activeEventsCount = db_count('events', "status = 'published'");

$recentOrders = db_fetch_all("
    SELECT o.*, (SELECT COUNT(*) FROM tickets t WHERE t.order_id = o.id) AS ticket_count
    FROM orders o 
    ORDER BY o.created_at DESC 
    LIMIT 5
");

$upcomingEvents = db_fetch_all("
    SELECT e.*, f.name AS facility_name,
           (SELECT COUNT(*) FROM tickets t JOIN ticket_types tt ON tt.id = t.ticket_type_id WHERE tt.event_id = e.id) AS sold_count
    FROM events e
    JOIN facilities f ON f.id = e.facility_id
    WHERE e.status = 'published'
    ORDER BY e.start_datetime ASC
    LIMIT 5
");

$pageTitle = 'Admin Dashboard';
require 'partials/header.php';
?>
<div class="page-header">
<h1>Management Dashboard</h1>
<p>Overview of campus event ticketing sales, venue hourly bookings, and operational metrics.</p>
</div>

<div class="card-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 28px;">
<div class="card" style="padding: 20px;">
<span class="stat-label">Total Ticket Sales Revenue</span>
<h2 style="font-size: 1.8rem; margin: 8px 0; color: var(--color-primary, #0056b3);"><?= format_myr($totalRevenue) ?></h2>
<span class="stat-label">Completed Orders</span>
</div>

<div class="card" style="padding: 20px;">
<span class="stat-label">Tickets Issued / Sold</span>
<h2 style="font-size: 1.8rem; margin: 8px 0; color: #28a745;"><?= number_format($totalTicketsSold) ?></h2>
<span class="stat-label">Event Attendees</span>
</div>

<div class="card" style="padding: 20px;">
<span class="stat-label">Venue Reservations</span>
<h2 style="font-size: 1.8rem; margin: 8px 0; color: #17a2b8;"><?= number_format($totalBookings) ?></h2>
<span class="stat-label">Hourly Space Bookings</span>
</div>

<div class="card" style="padding: 20px;">
<span class="stat-label">Active Published Events</span>
<h2 style="font-size: 1.8rem; margin: 8px 0; color: #6f42c1;"><?= number_format($activeEventsCount) ?></h2>
<span class="stat-label">Campus Events</span>
</div>
</div>

<div style="margin-bottom: 32px;">
<h2>Quick Actions</h2>
<div class="card-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px;">
<a href="event_create.php" class="btn" style="text-align:center;">+ Create Event</a>
<a href="checkin.php" class="btn btn-secondary" style="text-align:center;">📷 QR Scanner Check-in</a>
<a href="orders.php" class="btn btn-secondary" style="text-align:center;">📋 View All Orders</a>
<a href="facility_create.php" class="btn btn-secondary" style="text-align:center;">🏛️ Add Venue</a>
</div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 24px; margin-bottom: 24px;">
<div>
<h2>Recent Ticket Orders</h2>
<?php if (empty($recentOrders)): ?>
<div class="empty-state">
<p>No ticket orders recorded yet.</p>
</div>
<?php else: ?>
<table>
<tr><th>Order Ref</th><th>Buyer</th><th>Tickets</th><th>Total</th><th>Date</th></tr>
<?php foreach ($recentOrders as $o): ?>
<tr>
<td><a href="orders.php#<?= htmlspecialchars($o->order_ref) ?>"><strong><?= htmlspecialchars($o->order_ref) ?></strong></a></td>
<td><?= htmlspecialchars($o->buyer_name) ?></td>
<td><?= (int)$o->ticket_count ?></td>
<td><?= format_myr($o->total_amount) ?></td>
<td><?= htmlspecialchars(date('d M, g:i a', strtotime($o->created_at))) ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
</div>

<div>
<h2>Upcoming Events</h2>
<?php if (empty($upcomingEvents)): ?>
<div class="empty-state">
<p>No upcoming events configured.</p>
</div>
<?php else: ?>
<table>
<tr><th>Event Title</th><th>Venue</th><th>Date</th><th>Sold</th></tr>
<?php foreach ($upcomingEvents as $e): ?>
<tr>
<td><a href="event_edit.php?id=<?= (int)$e->id ?>"><strong><?= htmlspecialchars($e->title) ?></strong></a></td>
<td><?= htmlspecialchars($e->facility_name) ?></td>
<td><?= htmlspecialchars(date('d M Y, g:i a', strtotime($e->start_datetime))) ?></td>
<td><span class="badge badge-good"><?= (int)$e->sold_count ?> sold</span></td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
</div>
</div>

<?php require 'partials/footer.php'; ?>
