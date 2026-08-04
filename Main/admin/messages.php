<?php
require '../config.php';
require '../auth.php';
require '../helpers.php';
require_admin();

$messages = db_fetch_all('SELECT * FROM contact_messages ORDER BY created_at DESC');

$pageTitle = 'Contact Messages';
require 'partials/header.php';
?>
<div class="page-header">
<h1>Contact Messages</h1>
<p>Messages submitted through the public Contact Us form.</p>
</div>
<?php if (empty($messages)): ?>
<div class="empty-state">
<div class="empty-state-icon">&#128231;</div>
<p>No messages yet.</p>
</div>
<?php else: ?>
<div style="display:flex; flex-direction:column; gap:16px;">
<?php foreach ($messages as $m): ?>
<div class="card" style="padding:20px;">
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
<h3 style="margin:0; font-size:1.1rem;"><?= htmlspecialchars($m->subject) ?></h3>
<span class="stat-label"><?= htmlspecialchars(date('d M Y, g:i a', strtotime($m->created_at))) ?></span>
</div>
<p style="margin-bottom:12px; line-height:1.5; color:var(--text-secondary);"><?= nl2br(htmlspecialchars($m->message)) ?></p>
<div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); padding-top:10px;">
<span class="stat-label">From: <strong><?= htmlspecialchars($m->name) ?></strong> (<?= htmlspecialchars($m->email) ?>)</span>
<form action="message_delete.php" method="post" style="display:inline" onsubmit="return confirm('Delete this message?');">
<input type="hidden" name="id" value="<?= (int)$m->id ?>">
<button type="submit" class="btn-small btn-danger">Delete</button>
</form>
</div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
<?php require 'partials/footer.php'; ?>
