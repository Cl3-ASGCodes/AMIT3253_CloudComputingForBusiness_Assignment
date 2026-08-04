<?php
$loggedIn = current_user_id() !== null;
$currentPage = basename($_SERVER['PHP_SELF']);
function nav_active($page, $current) {
    return $page === $current ? ' active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
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
<meta name="description" content="<?= htmlspecialchars($pageDescription ?? 'Book campus event venues online - halls, auditoriums, and event spaces available in real time.') ?>">
<title><?= htmlspecialchars($pageTitle ?? 'TARC Event Venue Booking') ?></title>
<link rel="icon" type="image/png" href="assets/favicon.png">
<link rel="stylesheet" href="style.css?v=<?= @filemtime(__DIR__ . '/../style.css') ?>">

<style>
/* Header Controls & Responsive Mobile Menu Styles */
.nav-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.theme-toggle {
    background: transparent;
    border: none;
    font-size: 1.2rem;
    cursor: pointer;
    color: var(--text-color, inherit);
    padding: 0.4rem 0.6rem;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

@media (max-width: 768px) {
    .navbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 1rem;
        position: relative;
        background-color: var(--bg-card, var(--bg-surface, #ffffff));
        color: var(--text-color, #222222);
    }

    .mobile-nav-toggle {
        display: inline-flex;
        flex-direction: column;
        justify-content: space-around;
        width: 28px;
        height: 24px;
        background: transparent;
        border: none;
        cursor: pointer;
        padding: 0;
        z-index: 1001;
    }

    .mobile-nav-toggle span {
        width: 100%;
        height: 3px;
        background-color: var(--text-color, #222222);
        border-radius: 2px;
        transition: all 0.3s ease;
    }

    .nav-links {
        display: none;
        flex-direction: column;
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        box-sizing: border-box;
        background-color: var(--bg-card, var(--bg-surface, #76a2d4ee));
        color: var(--text-color, #222222);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        padding: 0.75rem 1rem;
        gap: 0.25rem;
        z-index: 1000;
        border-bottom: 1px solid var(--border-color, #e0e0e0);
    }

    .nav-links.mobile-open {
        display: flex;
    }

    .nav-links a, 
    .nav-links .user-menu, 
    .nav-links .user-menu-trigger {
        width: 100%;
        box-sizing: border-box;
        display: block;
        padding: 0.75rem 1rem;
        color: var(--text-color, inherit);
        text-decoration: none;
        border-radius: 6px;
        text-align: left;
    }

    .nav-links a:hover, 
    .nav-links a.active,
    .nav-links .user-menu-trigger:hover {
        background-color: var(--bg-hover, rgba(128, 128, 128, 0.15));
    }

    .user-menu-dropdown {
        position: static;
        width: 100%;
        box-shadow: none;
        background-color: var(--bg-secondary, rgba(0, 0, 0, 0.05));
        border: 1px solid var(--border-color, #e0e0e0);
        border-radius: 6px;
        padding: 0.5rem 0;
        margin-top: 0.25rem;
    }

    .user-menu-dropdown a {
        padding: 0.5rem 1rem;
    }

    .footer-grid {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
}

@media (min-width: 769px) {
    .mobile-nav-toggle {
        display: none;
    }
}
</style>
<style>
    @media (max-width: 768px) {
        .navbar {
            padding: 0.75rem 1rem;
        }
    }
</style>
</head>
<body>
<nav class="navbar">
<a class="brand" href="index.php">
    <img src="assets/tarumt-logo.png" alt="TAR UMT" class="brand-logo">
    <span>TARC Event Venue Booking</span>
</a>

<div class="nav-controls">
    <button id="theme-toggle" class="theme-toggle pc-theme-toggle" type="button" aria-label="Toggle dark mode">&#9728;</button>
    <button type="button" class="mobile-nav-toggle" aria-label="Toggle navigation menu" aria-expanded="false">
        <span></span>
        <span></span>
        <span></span>
    </button>
</div>
<script>
    // Mobile navigation toggle
    const toggleBtn = document.querySelector('.mobile-nav-toggle');
    const navLinks = document.querySelector('.nav-links');
    if (toggleBtn && navLinks) {
        toggleBtn.addEventListener('click', () => {
            const expanded = toggleBtn.getAttribute('aria-expanded') === 'true';
            toggleBtn.setAttribute('aria-expanded', String(!expanded));
            navLinks.classList.toggle('mobile-open');
            toggleBtn.classList.toggle('active');
        });
        // close menu when a link is clicked (optional for better UX)
        navLinks.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                if (navLinks.classList.contains('mobile-open')) {
                    navLinks.classList.remove('mobile-open');
                    toggleBtn.setAttribute('aria-expanded', 'false');
                    toggleBtn.classList.remove('active');
                }
            });
        });
    }
</script>

<div class="nav-links">
<a href="index.php" class="<?= trim(nav_active('index.php', $currentPage)) ?>">Home</a>
<a href="events.php" class="<?= trim(nav_active('events.php', $currentPage)) ?>">Events & Tickets</a>
<a href="facilities.php" class="<?= trim(nav_active('facilities.php', $currentPage)) ?>">Venues</a>
<a href="about.php" class="<?= trim(nav_active('about.php', $currentPage)) ?>">About</a>
<a href="contact.php" class="<?= trim(nav_active('contact.php', $currentPage)) ?>">Contact</a>
<?php if ($loggedIn): ?>
<a href="my_tickets.php" class="<?= trim(nav_active('my_tickets.php', $currentPage)) ?>">My Tickets</a>
<div class="user-menu">
<button type="button" class="nav-user user-menu-trigger" aria-haspopup="true" aria-expanded="false">
<span class="user-avatar"><?= htmlspecialchars(mb_strtoupper(mb_substr(current_user_name(), 0, 1))) ?></span> Hi, <?= htmlspecialchars(current_user_name()) ?>
</button>
<div class="user-menu-dropdown">
<a href="my_tickets.php">My Tickets</a>
<a href="account.php">My Account</a>
<a href="logout.php">Logout</a>
</div>
</div>
<?php else: ?>
<a href="login.php">Login</a>
<a href="register.php">Register</a>
<?php endif; ?>
</div>
<script>
    // Mobile navigation toggle
    const toggleBtn = document.querySelector('.mobile-nav-toggle');
    const navLinks = document.querySelector('.nav-links');
    if (toggleBtn && navLinks) {
        toggleBtn.addEventListener('click', () => {
            const expanded = toggleBtn.getAttribute('aria-expanded') === 'true';
            toggleBtn.setAttribute('aria-expanded', String(!expanded));
            navLinks.classList.toggle('mobile-open');
        });
    }
</script>
</nav>
<main class="container">