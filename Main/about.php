<?php
require 'config.php';
require 'auth.php';
require 'helpers.php';

$bookingCount = db_count('bookings');
$memberCount  = (int)db_fetch_value("
    SELECT COUNT(*) 
    FROM users u
    JOIN login l ON u.login_id = l.id
    WHERE l.user_type != 'admin'
");

$pageTitle = 'About Us';
require 'partials/header.php';
?>
<div class="about-hero">
<div class="about-hero-icons">&#127917; &#127908; &#127979; &#127881;</div>
<h1>TARC Event Venue Booking</h1>
<p>Serving the TAR UMT community since 2010</p>
</div>

<section>
<h2>Our Story</h2>
<p>Founded in 2010, TAR UMT Event Venues have grown from a single function hall
into a comprehensive collection of premier event spaces serving thousands of students, staff, and campus event organizers every year.
What started as a manual paper reservation sheet at the administration office is now this online booking
platform — built to replace manual queues with a simple, transparent system so anyone
on campus can check availability and reserve an event venue in under a minute.</p>
</section>

<section>
<h2>How It Works</h2>
<div class="timeline">
<div class="timeline-step timeline-step-left">
<div class="timeline-marker"></div>
<div class="timeline-content">
<h3>1. Browse Venues</h3>
<p>Explore available halls, auditoriums, and function rooms with real-time schedule information.</p>
</div>
</div>
<div class="timeline-step timeline-step-right">
<div class="timeline-marker"></div>
<div class="timeline-content">
<h3>2. Check Availability</h3>
<p>View each time slot's status on the Schedule page — Available, Booked or Closed.</p>
</div>
</div>
<div class="timeline-step timeline-step-left">
<div class="timeline-marker"></div>
<div class="timeline-content">
<h3>3. Make a Reservation</h3>
<p>Pick a date and time slot that works for your event — it's reserved the moment you confirm.</p>
</div>
</div>
<div class="timeline-step timeline-step-right">
<div class="timeline-marker"></div>
<div class="timeline-content">
<h3>4. Get Confirmation</h3>
<p>Receive an instant on-screen confirmation with a reference number for your event records.</p>
</div>
</div>
<div class="timeline-step timeline-step-left">
<div class="timeline-marker"></div>
<div class="timeline-content">
<h3>5. Host Your Event</h3>
<p>Arrive at your reserved slot. No manual queues, no double-booking, no hassle.</p>
</div>
</div>
</div>
</section>

<section>
<h2>By the Numbers</h2>
<div class="card-grid">
<div class="stat-tile"><div class="stat-value"><?= (int)$bookingCount ?></div><div class="stat-label">Bookings made</div></div>
<div class="stat-tile"><div class="stat-value"><?= (int)$memberCount ?></div><div class="stat-label">Members registered</div></div>
</div>
</section>
<div class="card-actions">
<a class="btn btn-secondary btn-small" href="facilities.php">See what's available &rarr;</a>
<a class="btn btn-secondary btn-small" href="contact.php">Contact us &rarr;</a>
</div>
<?php require 'partials/footer.php'; ?>