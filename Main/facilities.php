<?php
require 'config.php';
require 'auth.php';
require 'helpers.php';

// Search handling
$search = trim($_GET['q'] ?? '');
if ($search !== '') {
    $facilities = db_fetch_all(
        "SELECT f.*, (SELECT image_url FROM facility_images fi WHERE fi.facility_id = f.id LIMIT 1) AS image_url
         FROM facilities f
         WHERE f.name LIKE ?
         ORDER BY f.name",
        ['%' . $search . '%']
    );
} else {
    $facilities = db_fetch_all(
        "SELECT f.*, (SELECT image_url FROM facility_images fi WHERE fi.facility_id = f.id LIMIT 1) AS image_url
         FROM facilities f
         ORDER BY f.name"
    );
}

// My Bookings
$myBookings = [];
if ($uid = current_user_id()) {
    $myBookings = db_fetch_all(
        "SELECT b.id, f.name AS facility_name, co.name AS court_name, b.full_day, b.start_datetime, b.end_datetime, b.booking_date, b.reason
         FROM bookings b
         JOIN courts co ON co.id = b.court_id
         JOIN facilities f ON f.id = co.facility_id
         WHERE b.user_id = ?
         ORDER BY b.booking_date DESC",
        [$uid]
    );
}

$totalFacilities = count($facilities);
$totalHalls      = db_count('courts');
$totalCapacity   = array_sum(array_column($facilities, 'capacity'));

$pageTitle = 'Our Event Venues';
$pageDescription = 'A detailed look at each campus event venue - capacity, layouts, features, and venue guidelines.';
require 'partials/header.php';
?>



<div class="page-header">
<h1>Our Event Venues</h1>
<p>A closer look at our premier event spaces — capacity, layout configurations, features, and the house rules that ensure seamless hosting.</p>
</div>

<section>
<div class="card-grid">
<div class="stat-tile"><div class="stat-value"><?= (int)$totalFacilities ?></div><div class="stat-label">Event Venues</div></div>
<div class="stat-tile"><div class="stat-value"><?= (int)$totalHalls ?></div><div class="stat-label">Total Halls/Spaces</div></div>
<div class="stat-tile"><div class="stat-value"><?= (int)$totalCapacity ?></div><div class="stat-label">Combined Capacity</div></div>
</div>
</section>

<form method="get" class="filter-bar" id="facility-filter-form">
    <label>Search <input type="text" name="q" id="facility-search" placeholder="Facility name..." value="<?= htmlspecialchars($search) ?>" autocomplete="off"></label>
    <button type="submit">Search</button>
    <?php if ($search !== ''): ?>
        <a class="btn btn-secondary" href="facilities.php#facilities">Clear</a>
    <?php endif; ?>
</form>
<script>
(function(){
    var input=document.getElementById('facility-search');
    var form=document.getElementById('facility-filter-form');
    if(!input||!form) return;
    var timer;
    input.addEventListener('input',function(){
        clearTimeout(timer);
        timer=setTimeout(function(){form.submit();},500);
    });
})();
</script>

    <section class="card-grid">
        <?php foreach ($facilities as $f): ?>
            <?php
                $halls = db_fetch_all('SELECT name FROM courts WHERE facility_id = ? ORDER BY name', [$f->id]);
                $hallNames = array_map(fn($h)=>$h->name, $halls);
                $images = db_fetch_all('SELECT image_url, description FROM facility_images WHERE facility_id = ? ORDER BY id', [$f->id]);
            ?>
            <section class="facility-profile">
                <div class="facility-profile-header">
                    <div>
                        <h3><?= htmlspecialchars($f->name) ?></h3>
                        <p>&#128205; <?= htmlspecialchars($f->location) ?> &middot; Capacity: Up to <?= (int)$f->capacity ?> guests</p>
                        <p><?= count($hallNames) ?> hall/space<?= count($hallNames)===1?'' : 's' ?> available: <?= htmlspecialchars(implode(', ', $hallNames)) ?></p>
                    </div>
                </div>
                <?php if (!empty($images)): ?>
                    <div class="slideshow-container" data-slideshow-id="<?= (int)$f->id ?>">

                        <?php foreach ($images as $idx=>$img): ?>
                            <div class="slide-frame <?= $idx===0 ? 'active' : '' ?>">
                                <img src="<?= htmlspecialchars($img->image_url) ?>" alt="<?= htmlspecialchars($img->description ?? $f->name) ?>" loading="lazy">
                                    <?php if (!empty($img->description)): ?>
                                        <div class="slide-caption"><?= htmlspecialchars($img->description) ?></div>
                                    <?php endif; ?>
                                    <div class="slide-progress"></div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (count($images) > 1): ?>
                            <button type="button" class="slide-prev" onclick="moveSlide(<?= (int)$f->id ?>, -1)">&#10094;</button>
                            <button type="button" class="slide-next" onclick="moveSlide(<?= (int)$f->id ?>, 1)">&#10095;</button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($f->description)): ?>
                    <p><?= htmlspecialchars($f->description) ?></p>
                <?php endif; ?>
                <?php if (!empty($f->features)): ?>
                    <h4>Key Venue Features &amp; Equipment</h4>
                    <ul class="policy-list">
                        <?php foreach (explode("\n", $f->features) as $feature): ?>
                            <?php $feature = trim($feature); ?>
                            <?php if ($feature !== ''): ?>
                                <li><?= htmlspecialchars($feature) ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <div class="card-actions" style="margin-top:1rem;">
                    <a class="btn btn-secondary btn-small" href="schedule.php?facility_id=<?= (int)$f->id ?>">View Schedule</a>
                    <?php if (current_user_id()): ?>
                        <a class="btn btn-small" href="create.php?facility_id=<?= (int)$f->id ?>">Book Now</a>
                    <?php else: ?>
                        <a class="btn btn-small" href="login.php">Login to Book</a>
                    <?php endif; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </section>

        <script>
function moveSlide(containerId, direction) {
    var container = document.querySelector('.slideshow-container[data-slideshow-id="' + containerId + '"]');
    if (!container) return;
    var slides = container.querySelectorAll('.slide-frame');
    if (slides.length <= 1) return;
    var currentIndex = -1;
    slides.forEach(function(s,i){ if (s.classList.contains('active')) currentIndex = i; });
    if (currentIndex !== -1) {
        slides[currentIndex].classList.remove('active');
        var newIndex = (currentIndex + direction + slides.length) % slides.length;
        slides[newIndex].classList.add('active');
    }
}
// Auto‑play all slideshows on load
(function(){
    var containers = document.querySelectorAll('.slideshow-container');
    containers.forEach(function(container){
        var slides = container.querySelectorAll('.slide-frame');
        if (slides.length <= 1) return;
        var index = 0;
        setInterval(function(){
            slides[index].classList.remove('active');
            index = (index + 1) % slides.length;
            slides[index].classList.add('active');
        }, 4000); // change slide every 4 seconds
    });
})();
</script>

<?php if (current_user_id()): ?>
    <?php if (empty($myBookings)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">🔔</div>
            <p>You have no bookings yet. Book a facility above to get started.</p>
        </div>
    <?php else: ?>
        <section>
            <h2>My Bookings</h2>
            <table>
                <tr><th>Facility</th><th>Court</th><th>Date</th><th>Time</th><th>Reason</th><th>Actions</th></tr>
                <?php foreach ($myBookings as $b): ?>
                <tr>
                    <td><?= htmlspecialchars($b->facility_name) ?></td>
                    <td><?= htmlspecialchars($b->court_name) ?></td>
                    <td><?= htmlspecialchars($b->booking_date) ?></td>
                    <td><?php if ($b->full_day): ?>Full Day<?php else: ?><?= date('H:i', strtotime($b->start_datetime)) ?> - <?= date('H:i', strtotime($b->end_datetime)) ?><?php endif; ?></td>
                    <td><?= htmlspecialchars($b->reason ?? '') ?></td>
                    <td>
                        <a class="btn btn-secondary btn-small" href="edit.php?id=<?= (int)$b->id ?>">Edit</a>
                        <form action="delete.php" method="post" style="display:inline" onsubmit="return confirm('Delete this booking?');">
                            <input type="hidden" name="id" value="<?= (int)$b->id ?>">
                            <button type="submit" class="btn btn-small btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </section>
    <?php endif; ?>
<?php else: ?>
    <div class="empty-state">
        <div class="empty-state-icon">🔔</div>
        <p><a href="login.php">Login</a> or <a href="register.php">Register</a> to view and manage your bookings.</p>
    </div>
<?php endif; ?>

<section>
<h2>General Event Booking Guidelines</h2>
<ul class="policy-list">
<li>One reservation per account per hall space and time slot — allocations operate on a first-come, first-served basis.</li>
<li>Need to modify arrangements? Cancel or edit anytime via My Bookings on the homepage to release the slot.</li>
<li>Venues may be temporarily unavailable for administrative maintenance — active closures are always detailed on the Schedule page.</li>
<li>Ensure setup and tear-down adhere to your reserved window to accommodate proceeding venue allocations.</li>
</ul>
</section>

<section>
<h2>Frequently Asked Questions</h2>
<details class="faq-item">
<summary>Are there costs associated with reserving an event venue?</summary>
<p>No. Event venue bookings are available free of charge for university operations, students, and staff — log in to check slot availability.</p>
</details>
<details class="faq-item">
<summary>How can I cancel or adjust an existing venue reservation?</summary>
<p>Navigate to My Bookings on the homepage to update the date/time slot or remove the booking entirely.</p>
</details>
<details class="faq-item">
<summary>How are venue maintenance closures handled?</summary>
<p>Unavailable dates and time slots display as "Closed" alongside the reason on the Schedule page, blocking incoming reservations.</p>
</details>
<details class="faq-item">
<summary>Is there a cap on the number of event spaces I can reserve?</summary>
<p>There is no rigid cap — reserve as many distinct spaces or time slots as required, provided slots do not overlap.</p>
</details>
</section>

<?php require 'partials/footer.php'; ?>