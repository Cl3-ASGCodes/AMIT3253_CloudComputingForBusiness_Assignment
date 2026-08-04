<?php
require 'config.php';
require 'auth.php';
require 'helpers.php';

// ---------- Search ----------
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

// ---------- Notifications & My Bookings ----------
$notifications = [];
$myBookings = [];
if ($uid = current_user_id()) {
    $notifications = db_fetch_all(
        'SELECT id, message, created_at FROM notifications WHERE user_id = ? AND read_at IS NULL ORDER BY created_at DESC',
        [$uid]
    );

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

$pageTitle = 'Campus Event Venue Booking';
require 'partials/header.php';
?>
<?php foreach ($notifications as $n): ?>
    <div class="alert alert-warning">
        <span>&#128276; <?= htmlspecialchars($n->message) ?></span>
        <form action="notification_dismiss.php" method="post">
            <input type="hidden" name="id" value="<?= (int)$n->id ?>">
            <button type="submit" class="btn btn-small btn-secondary">Dismiss</button>
        </form>
    </div>
<?php endforeach; ?>


<div class="page-header">
    <h1>Campus Event Venue Booking</h1>
    <p>Easy access portal for venue booking for Guests and TARCIANs.</p>
</div>

<section>
    <h2 id="facilities">Available Facilities</h2>
    <form method="get" class="filter-bar" id="facility-filter-form">
        <label>Search <input type="text" name="q" id="facility-search" placeholder="Facility name..." value="<?= htmlspecialchars($search) ?>" autocomplete="off"></label>
        <button type="submit">Search</button>
        <?php if ($search !== ''): ?>
            <a class="btn btn-secondary" href="index.php#facilities">Clear</a>
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
    <?php if (empty($facilities)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">&#128269;</div>
            <p>No facilities match your search.</p>
            <a class="btn btn-small btn-secondary" href="index.php#facilities">Clear filters</a>
        </div>
    <?php else: ?>
        <div class="card-grid">
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
        </div>
    <?php endif; ?>
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

<section>
    <h2>My Bookings</h2>
    <?php if (!current_user_id()): ?>
        <div class="empty-state">
            <div class="empty-state-icon">&#128100;</div>
            <p><a href="login.php">Login</a> or <a href="register.php">register</a> to view and manage your bookings.</p>
        </div>
    <?php elseif (empty($myBookings)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">&#128197;</div>
            <p>You have no bookings yet. Book a facility above to get started.</p>
        </div>
    <?php else: ?>
        <table>
            <tr>
                <th>Facility</th>
                <th>Court</th>
                <th>Date</th>
                <th>Time</th>
                <th>Reason</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($myBookings as $b): ?>
                <tr>
                    <td><?= htmlspecialchars($b->facility_name) ?></td>
                    <td><?= htmlspecialchars($b->court_name) ?></td>
                    <td><?= htmlspecialchars($b->booking_date) ?></td>
                    <td>
                        <?php if ($b->full_day): ?>
                            Full Day
                        <?php else: ?>
                            <?= date('H:i', strtotime($b->start_datetime)) ?> - <?= date('H:i', strtotime($b->end_datetime)) ?>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($b->reason ?? '') ?></td>
                    <td>
                        <a class="btn btn-secondary btn-small" href="edit.php?id=<?= (int)$b->id ?>">Edit</a>
                        <form action="delete.php" method="post" style="display:inline" onsubmit="return confirm('Delete this booking?');">
                            <input type="hidden" name="id" value="<?= (int)$b->id ?>">
                            <button type="submit" class="btn-small btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</section>

<?php require 'partials/footer.php'; ?>