<?php
require '../config.php';
require '../auth.php';
require '../helpers.php';

// Only admin can access
if (!current_user_is_admin()) {
    header('Location: ../login.php');
    exit;
}

$pageTitle = 'Manage Events';
require 'partials/header.php';
?>
<h1>Events Management</h1>
<a href="event_create.php" class="btn" style="margin-bottom:12px;">+ Create New Event</a>
<table class="table" style="width:100%; border-collapse:collapse;">
    <thead>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Venue</th>
            <th>Dates</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $events = db_fetch_all('SELECT e.*, f.name AS facility_name FROM events e JOIN facilities f ON f.id = e.facility_id ORDER BY e.start_datetime DESC');
    foreach ($events as $e):
    ?>
        <tr>
            <td><?= htmlspecialchars($e->id) ?></td>
            <td><?= htmlspecialchars($e->title) ?></td>
            <td><?= htmlspecialchars($e->facility_name) ?></td>
            <td><?= date('d M Y', strtotime($e->start_datetime)) ?> – <?= date('d M Y', strtotime($e->end_datetime)) ?></td>
            <td><?= htmlspecialchars(ucfirst($e->status)) ?></td>
            <td>
                <a href="event_edit.php?id=<?= $e->id ?>" class="btn">Edit</a>
                <a href="event_delete.php?id=<?= $e->id ?>" class="btn" onclick="return confirm('Delete this event?');">Delete</a>
                <a href="../event_details.php?event_id=<?= $e->id ?>" class="btn" target="_blank">View</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require '../partials/footer.php'; ?>
