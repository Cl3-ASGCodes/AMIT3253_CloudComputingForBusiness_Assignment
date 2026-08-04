<?php
require '../config.php';
require '../auth.php';
require '../helpers.php';
require_admin();

$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);

$myId = current_user_id();
$users = db_fetch_all('
    SELECT u.id, u.name, u.email, u.created_at, l.user_type 
    FROM users u 
    LEFT JOIN login l ON l.id = u.login_id 
    ORDER BY u.name
');

$pageTitle = 'Manage User Accounts';
require 'partials/header.php';
?>
<div class="page-header">
<div class="page-header-top">
<div>
<h1>User Accounts</h1>
<p>Grant or revoke admin access, or manage student and guest accounts.</p>
</div>
<a class="btn btn-small" href="user_create.php">+ Add Admin Account</a>
</div>
</div>

<?php if ($flashError): ?><p class="alert alert-error"><?= htmlspecialchars($flashError) ?></p><?php endif; ?>
<table>
<tr><th>Name</th><th>Email</th><th>Role / Type</th><th>Joined</th><th>Actions</th></tr>
<?php foreach ($users as $u): ?>
<?php $isAdmin = ($u->user_type === 'admin'); ?>
<tr>
<td><?= htmlspecialchars($u->name) ?></td>
<td><?= htmlspecialchars($u->email) ?></td>
<td><?= $isAdmin ? '<span class="badge badge-accent">Admin</span>' : '<span class="badge badge-neutral">' . htmlspecialchars(ucfirst($u->user_type ?? 'user')) . '</span>' ?></td>
<td><?= htmlspecialchars(date('d M Y', strtotime($u->created_at))) ?></td>
<td>
<?php if ((int)$u->id === (int)$myId): ?>
<span class="stat-label">(you)</span>
<?php else: ?>
<form action="user_toggle_admin.php" method="post" style="display:inline" onsubmit="return confirm('<?= $isAdmin ? 'Remove admin access from ' : 'Grant admin access to ' ?><?= htmlspecialchars($u->name) ?>?');">
<input type="hidden" name="id" value="<?= (int)$u->id ?>">
<button type="submit" class="btn btn-secondary btn-small"><?= $isAdmin ? 'Revoke Admin' : 'Make Admin' ?></button>
</form>
<form action="user_delete.php" method="post" style="display:inline" onsubmit="return confirm('Delete this user? This will PERMANENTLY delete their account together with ALL of their bookings and orders.');">
<input type="hidden" name="id" value="<?= (int)$u->id ?>">
<button type="submit" class="btn-small btn-danger">Delete</button>
</form>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</table>
<?php require 'partials/footer.php'; ?>
