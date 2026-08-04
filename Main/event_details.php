<?php
require 'config.php';
require 'auth.php';
require 'helpers.php';

$id = (int)($_GET['id'] ?? 0);

$event = db_fetch_one("
    SELECT e.*, f.name AS facility_name, f.location, f.layout_url, co.name AS court_name
    FROM events e
    JOIN facilities f ON f.id = e.facility_id
    LEFT JOIN courts co ON co.id = e.court_id
    WHERE e.id = ? AND e.status = 'published'
", [$id]);

if (!$event) {
    die('Event not found or is no longer available.');
}

$ticketTypes = db_fetch_all('SELECT * FROM ticket_types WHERE event_id = ? ORDER BY price DESC', [$id]);

$pageTitle = $event->title . ' - Event Details';
require 'partials/header.php';
?>
<div style="max-width: 900px; margin: 0 auto;">
<a href="events.php" class="btn btn-secondary btn-small" style="margin-bottom: 16px;">&larr; Back to Events</a>

<div class="card" style="padding: 0; overflow: hidden; margin-bottom: 24px;">
<?php if (!empty($event->image_url)): ?>
<img src="<?= htmlspecialchars($event->image_url) ?>" alt="<?= htmlspecialchars($event->title) ?>" style="width: 100%; max-height: 380px; object-fit: cover;">
<?php endif; ?>

<div style="padding: 24px;">
<div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
<div>
<span class="badge badge-accent" style="margin-bottom: 8px;">Upcoming Event</span>
<h1 style="margin: 4px 0; font-size: 1.8rem;"><?= htmlspecialchars($event->title) ?></h1>
<p style="font-size: 1.1rem; color: var(--text-secondary);">
📍 <strong><?= htmlspecialchars($event->facility_name) ?></strong> <?= $event->court_name ? '(' . htmlspecialchars($event->court_name) . ')' : '' ?> &middot; <?= htmlspecialchars($event->location) ?>
</p>
</div>
<div style="text-align: right;">
<span class="stat-label">Event Date &amp; Time</span>
<div style="font-weight: 600; font-size: 1.1rem; color: var(--color-primary, #0056b3);">
📅 <?= htmlspecialchars(date('D, d M Y', strtotime($event->start_datetime))) ?><br>
⏰ <?= htmlspecialchars(date('g:i A', strtotime($event->start_datetime))) ?> - <?= htmlspecialchars(date('g:i A', strtotime($event->end_datetime))) ?>
</div>
</div>
</div>
</div>
</div>

<div style="display: grid; grid-template-columns: 1fr 340px; gap: 24px; align-items: start;">
<div>
<div class="card" style="padding: 24px; margin-bottom: 24px;">
<h2>About this Event</h2>
<p style="line-height: 1.6; white-space: pre-line;"><?= htmlspecialchars($event->description) ?></p>

<?php if ($event->layout_url): ?>
<h3 style="margin-top: 20px;">Venue Layout</h3>
<img class="table-thumb" style="width: 100%; max-width: 400px; height: auto; border-radius: 6px;" src="<?= htmlspecialchars($event->layout_url) ?>" alt="Venue Layout">
<?php endif; ?>
</div>
</div>

<div>
<div class="card" style="padding: 24px; position: sticky; top: 80px;">
<h2 style="margin-top: 0;">Select Tickets</h2>
<form action="checkout.php" method="post">
<input type="hidden" name="event_id" value="<?= (int)$event->id ?>">

<?php foreach ($ticketTypes as $tt): ?>
<div style="border: 1px solid var(--border-color); border-radius: 8px; padding: 14px; margin-bottom: 12px;">
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
<strong style="font-size: 1rem;"><?= htmlspecialchars($tt->name) ?></strong>
<span style="font-weight: 700; color: #28a745; font-size: 1.1rem;"><?= format_myr($tt->price) ?></span>
</div>
<?php if ($tt->description): ?>
<p class="stat-label" style="margin-bottom: 8px;"><?= htmlspecialchars($tt->description) ?></p>
<?php endif; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px;">
<?php if ($tt->remaining_quantity > 0): ?>
<span class="stat-label" style="color: #28a745;"><?= (int)$tt->remaining_quantity ?> tickets left</span>
<select name="tickets[<?= (int)$tt->id ?>]" style="width: 70px;">
<option value="0">0</option>
<option value="1">1</option>
<option value="2">2</option>
<option value="3">3</option>
<option value="4">4</option>
<option value="5">5</option>
</select>
<?php else: ?>
<span class="badge badge-critical">Sold Out</span>
<?php endif; ?>
</div>
</div>
<?php endforeach; ?>

<button type="submit" class="btn" style="width: 100%; padding: 12px; font-size: 1.05rem; margin-top: 8px;">Proceed to Checkout &rarr;</button>
</form>
</div>
</div>
</div>
</div>
<?php require 'partials/footer.php'; ?>
