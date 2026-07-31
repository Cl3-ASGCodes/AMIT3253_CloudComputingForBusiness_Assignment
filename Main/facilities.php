<?php
require 'config.php';
require 'auth.php';
require 'helpers.php';

// Fetch all event venues
$facilities = db_fetch_all('SELECT * FROM facilities ORDER BY capacity DESC');

$totalFacilities = count($facilities);
$totalHalls      = db_count('courts');
$totalCapacity   = array_sum(array_column($facilities, 'capacity'));

$pageTitle = 'Our Event Venues';
$pageDescription = 'A detailed look at each campus event venue - capacity, layouts, features, and venue guidelines.';
require 'partials/header.php';
?>

<style>
.slideshow-container {
    position: relative;
    max-width: 100%;
    border-radius: 8px;
    overflow: hidden;
    background-color: #1a1a1a;
    margin-bottom: 1rem;
}
.slide-frame {
    display: none;
    position: relative;
    width: 100%;
    height: 320px;
}
.slide-frame.active {
    display: block;
}
.slide-frame img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.slide-caption {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0, 0, 0, 0.7);
    color: #fff;
    padding: 8px 16px;
    font-size: 0.88rem;
}
.slide-prev, .slide-next {
    cursor: pointer;
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    padding: 10px 14px;
    color: white;
    font-weight: bold;
    font-size: 16px;
    transition: 0.2s ease;
    border-radius: 0 3px 3px 0;
    user-select: none;
    background-color: rgba(0,0,0,0.4);
    border: none;
}
.slide-next {
    right: 0;
    border-radius: 3px 0 0 3px;
}
.slide-prev:hover, .slide-next:hover {
    background-color: rgba(0,0,0,0.8);
}
</style>

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

<?php foreach ($facilities as $f): ?>
<?php
$halls = db_fetch_all('SELECT name FROM courts WHERE facility_id = ? ORDER BY name', [$f->id]);
$hallNames = array_map(fn($h) => $h->name, $halls);

$images = db_fetch_all('SELECT image_url, description FROM facility_images WHERE facility_id = ? ORDER BY id', [$f->id]);
?>
<section class="facility-profile">
<div class="facility-profile-header">
<div>
<h3><?= htmlspecialchars($f->name) ?></h3>
<p>&#128205; <?= htmlspecialchars($f->location) ?> &middot; Capacity: Up to <?= (int)$f->capacity ?> guests</p>
<p><?= count($hallNames) ?> hall/space<?= count($hallNames) === 1 ? '' : 's' ?> available: <?= htmlspecialchars(implode(', ', $hallNames)) ?></p>
</div>
</div>

<?php if (!empty($images)): ?>
<div class="slideshow-container" data-slideshow-id="<?= (int)$f->id ?>">
    <?php foreach ($images as $index => $img): ?>
    <div class="slide-frame <?= $index === 0 ? 'active' : '' ?>">
        <img src="<?= htmlspecialchars($img->image_url) ?>" alt="<?= htmlspecialchars($img->description ?? $f->name) ?>" loading="lazy">
        <?php if (!empty($img->description)): ?>
        <div class="slide-caption"><?= htmlspecialchars($img->description) ?></div>
        <?php endif; ?>
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
</section>
<?php endforeach; ?>

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

<script>
function moveSlide(containerId, direction) {
    var container = document.querySelector('.slideshow-container[data-slideshow-id="' + containerId + '"]');
    if (!container) return;
    var slides = container.querySelectorAll('.slide-frame');
    if (slides.length <= 1) return;

    var currentIndex = -1;
    for (var i = 0; i < slides.length; i++) {
        if (slides[i].classList.contains('active')) {
            currentIndex = i;
            break;
        }
    }

    if (currentIndex !== -1) {
        slides[currentIndex].classList.remove('active');
        var newIndex = (currentIndex + direction + slides.length) % slides.length;
        slides[newIndex].classList.add('active');
    }
}
</script>

<?php require 'partials/footer.php'; ?>