<?php
require 'config.php';
require 'auth.php';
require 'helpers.php';

// $facilities = $conn->query('SELECT * FROM facilities ORDER BY name')->fetch_all(MYSQLI_ASSOC);

// $totalFacilities = count($facilities);
// $totalCourts     = $conn->query('SELECT COUNT(*) AS c FROM courts')->fetch_assoc()['c'];
// $totalCapacity   = array_sum(array_column($facilities, 'capacity'));

$pageTitle = 'Our Facilities';
$pageDescription = 'A detailed look at each campus sports facility - construction, materials, capacity and house rules.';
require 'partials/header.php';
?>
<div class="page-header">
<h1>Our Facilities</h1>
<p>A closer look at what's on offer — construction, materials, capacity, and the house
rules that keep each venue safe and well maintained.</p>
</div>

<section>
<div class="card-grid">
<div class="stat-tile"><div class="stat-value"><?= (int)$totalFacilities ?></div><div class="stat-label">Facilities</div></div>
<div class="stat-tile"><div class="stat-value"><?= (int)$totalCourts ?></div><div class="stat-label">Total courts</div></div>
<div class="stat-tile"><div class="stat-value"><?= (int)$totalCapacity ?></div><div class="stat-label">Combined capacity</div></div>
</div>
</section>

<!-- <?php foreach ($facilities as $f): ?>
<?php
// $courtsStmt = $conn->prepare('SELECT name FROM courts WHERE facility_id = ? ORDER BY name');
// $courtsStmt->bind_param('i', $f['id']);
// $courtsStmt->execute();
// $facilityCourts = array_column($courtsStmt->get_result()->fetch_all(MYSQLI_ASSOC), 'name');
// $courtsStmt->close();
?>
<section class="facility-profile">
<div class="facility-profile-header">
<img class="card-thumb" src="<?= htmlspecialchars(facility_image_url($f)) ?>" alt="<?= htmlspecialchars($f['name']) ?>" loading="lazy">
<div>
<h3><?= htmlspecialchars($f['name']) ?></h3>
<p>&#128205; <?= htmlspecialchars($f['location']) ?> &middot; Capacity: <?= (int)$f['capacity'] ?> per session</p>
<p><?= count($facilityCourts) ?> court<?= count($facilityCourts) === 1 ? '' : 's' ?> available: <?= htmlspecialchars(implode(', ', $facilityCourts)) ?></p>
</div>
</div>

<?php if (!empty($f['description'])): ?>
<p><?= htmlspecialchars($f['description']) ?></p>
<?php endif; ?>

<?php if (!empty($f['materials'])): ?>
<h4>Construction &amp; Materials</h4>
<p><?= htmlspecialchars($f['materials']) ?></p>
<?php endif; ?>

<?php if (!empty($f['rules'])): ?>
<h4>House Rules &amp; Regulations</h4>
<ul class="policy-list">
<?php foreach (explode("\n", $f['rules']) as $rule): ?>
<?php $rule = trim($rule); ?>
<?php if ($rule !== ''): ?>
<li><?= htmlspecialchars($rule) ?></li>
<?php endif; ?>
<?php endforeach; ?>
</ul>
<?php endif; ?>
</section>
<?php endforeach; ?>-->

<section>
<h2>Venue Booking Guidelines</h2>
<ul class="policy-list">
<li>
    All bookings are <b>manually reviewed</b> by our liaison team and will be reviewed and replied within <b>5</b> working days.
</li>
<li>
    Space availability subjected to first come first serve basis.
</li>
<li>
    Rental rates are based on availability and can be adjusted as needed.
</li>
<li>
    Additional equipments and customized event requirements are available upon request.
</li>
</ul>
</section>

<section>
<h2>Frequently Asked Questions</h2>
<details class="faq-item">
<summary>Do I need to pay to book a venue?</summary>
<p>
    For non-TARUMT personnel or organisations, you will need to pay for the venue and the payment amount will be made available and negotiable after the booking has been reviewed. 
    <br>*20% deposit shall be paid upfront within 10 working days after acceptance of the booking.<br>
    <br>For TARUMT personnel or students, you will not need to pay for the venue.
    <br>However, you will require approval from your respective department for the event and any misconduct on the venue will result in disiplinary action and penalty.
</p>
</details>
<details class="faq-item">
<summary>Can I cancel or change my booking?</summary>
<p>Yes. Go to My Bookings on the homepage to edit the date/time or cancel a booking and the changing or the cancelation will be updated within 5 working days.
    <br>After cancellation has been confirmed, only at maximum of 50% deposit will be refunded.
</p>
</details>
<details class="faq-item">
<summary>What happens if a facility is closed for maintenance?</summary>
<p>
    Closed dates and time slots show as "Closed" with a reason on the Schedule page, and you won't be able to book them.
    <br> If the facility is closed for maintenance due to unforseen circumstances, our liaison team will contact you to notify and provide alternate available venue.
</p>
</details>
<details class="faq-item">
<summary>Is there a limit to how many facilities I can book?</summary>
<p>No fixed limit — book as many different slots as you need, as long as they don't overlap with existing bookings.</p>
</details>
</section>
<?php require 'partials/footer.php'; ?>
