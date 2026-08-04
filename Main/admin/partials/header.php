<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$facilityPages = ['facilities.php', 'facility_create.php', 'facility_edit.php', 'court_create.php', 'court_edit.php'];
$eventPages = ['events.php', 'event_create.php', 'event_edit.php'];
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<script>
(function () {
    var saved = localStorage.getItem('theme');
    if (saved === 'dark' || saved === 'light') {
        document.documentElement.setAttribute('data-theme', saved);
    }
})();
</script>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'Admin - TARC Venue & Event Ticketing') ?></title>
<link rel="icon" type="image/png" href="../assets/favicon.png">
<link rel="stylesheet" href="../style.css?v=<?= @filemtime(__DIR__ . '/../../style.css') ?>">
</head>
<body>
<nav class="navbar">
<a class="brand" href="dashboard.php"><img src="../assets/tarumt-logo.png" alt="TAR UMT" class="brand-logo">Admin &middot; Venue & Ticketing</a>
<div class="nav-links">
<a href="dashboard.php" class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
<a href="events.php" class="<?= in_array($currentPage, $eventPages) ? 'active' : '' ?>">Events</a>
<a href="orders.php" class="<?= $currentPage === 'orders.php' ? 'active' : '' ?>">Orders</a>
<a href="checkin.php" class="<?= $currentPage === 'checkin.php' ? 'active' : '' ?>">Check-in</a>
<a href="facilities.php" class="<?= in_array($currentPage, $facilityPages) ? 'active' : '' ?>">Venues</a>
<a href="schedule.php" class="<?= $currentPage === 'schedule.php' ? 'active' : '' ?>">Schedule</a>
<a href="bookings.php" class="<?= $currentPage === 'bookings.php' ? 'active' : '' ?>">Bookings</a>
<a href="closures.php" class="<?= $currentPage === 'closures.php' ? 'active' : '' ?>">Closures</a>
<a href="messages.php" class="<?= $currentPage === 'messages.php' ? 'active' : '' ?>">Messages</a>
<a href="users.php" class="<?= $currentPage === 'users.php' ? 'active' : '' ?>">Users</a>
<div class="user-menu">
<button type="button" class="nav-user user-menu-trigger" aria-haspopup="true" aria-expanded="false">
<span class="user-avatar"><?= htmlspecialchars(mb_strtoupper(mb_substr(current_user_name(), 0, 1))) ?></span> Hi, <?= htmlspecialchars(current_user_name()) ?>
</button>
<div class="user-menu-dropdown">
<a href="../account.php">My Account</a>
<a href="../logout.php">Logout</a>
</div>
</div>
<button id="theme-toggle" class="theme-toggle" type="button" aria-label="Toggle dark mode">&#9728;</button>
</div>
</nav>
<main class="container">