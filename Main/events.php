<?php
require 'config.php';
require 'auth.php';
require 'helpers.php';

$search = trim($_GET['q'] ?? '');
$facility_id = (int)($_GET['facility_id'] ?? 0);

$params = [];
$whereClause = "e.status = 'published'";

if ($search !== '') {
    $whereClause .= " AND (e.title LIKE ? OR e.description LIKE ?)";
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

if ($facility_id > 0) {
    $whereClause .= " AND e.facility_id = ?";
    $params[] = $facility_id;
}

$events = db_fetch_all("
    SELECT e.*, f.name AS facility_name, f.location, co.name AS court_name,
           (SELECT MIN(price) FROM ticket_types tt WHERE tt.event_id = e.id) AS min_price
    FROM events e
    JOIN facilities f ON f.id = e.facility_id
    LEFT JOIN courts co ON co.id = e.court_id
    WHERE $whereClause
    ORDER BY e.start_datetime ASC
", $params);

$facilities = db_fetch_all('SELECT id, name FROM facilities ORDER BY name');

$pageTitle = 'Campus Events & Tickets';
require 'partials/header.php';
?>
<div class="page-header">
<h1>Campus Events &amp; Ticketing Portal</h1>
<p>Explore upcoming concerts, seminars, tech talks, and cultural events at TAR UMT venues.</p>
</div>

<form method="get" class="filter-bar">
    <label>Search Event <input type="text" name="q" placeholder="Event title..." value="<?= htmlspecialchars($search) ?>"></label>
    <label>Venue
        <select name="facility_id" onchange="this.form.submit()">
            <option value="0">All Venues</option>
            <?php foreach ($facilities as $fac): ?>
                <option value="<?= (int)$fac->id ?>" <?= $fac->id == $facility_id ? 'selected' : '' ?>><?= htmlspecialchars($fac->name) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <button type="submit">Filter</button>
    <?php if ($search !== '' || $facility_id > 0): ?>
        <a href="events.php" class="btn btn-secondary">Clear</a>
    <?php endif; ?>
</form>

<?php if (empty($events)): ?>
    <div class="empty-state">
        <div class="empty-state-icon">&#127915;</div>
        <p>No events found matching your filter criteria.</p>
    </div>
<?php else: ?>
    <div class="card-grid">
    <?php foreach ($events as $e): ?>
        <div class="card">
            <?php if (!empty($e->image_url)): ?>
                <img class="card-thumb" src="<?= htmlspecialchars($e->image_url) ?>" alt="<?= htmlspecialchars($e->title) ?>" loading="lazy">
            <?php else: ?>
                <div class="card-thumb" style="background:#e1e0d9; display:flex; align-items:center; justify-content:center; font-size:2rem;">🎟️</div>
            <?php endif; ?>
            <div style="display:flex; flex-direction:column; flex:1;">
                <span class="stat-label" style="color:var(--color-primary, #0056b3); font-weight:600;">
                    📅 <?= htmlspecialchars(date('D, d M Y @ g:i A', strtotime($e->start_datetime))) ?>
                </span>
                <h3 style="margin: 8px 0; font-size:1.2rem;"><?= htmlspecialchars($e->title) ?></h3>
                <p style="margin-bottom:6px;">📍 <strong><?= htmlspecialchars($e->facility_name) ?></strong> <?= $e->court_name ? '(' . htmlspecialchars($e->court_name) . ')' : '' ?></p>
                <p class="stat-label" style="margin-bottom:14px;"><?= htmlspecialchars(substr($e->description, 0, 100)) ?>...</p>

                <div style="margin-top:auto; display:flex; align-items:center; justify-content:space-between; border-top:1px solid var(--border-color); padding-top:12px;">
                    <div>
                        <span class="stat-label">From</span><br>
                        <strong style="font-size:1.1rem; color:#28a745;"><?= $e->min_price !== null ? format_myr($e->min_price) : 'Free' ?></strong>
                    </div>
                    <a href="event_details.php?id=<?= (int)$e->id ?>" class="btn btn-small">Get Tickets &rarr;</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require 'partials/footer.php'; ?>
