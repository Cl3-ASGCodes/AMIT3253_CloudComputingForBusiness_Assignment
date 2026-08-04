<?php
require '../config.php';
require '../auth.php';
require '../helpers.php';

// Only admin access
if (!current_user_is_admin()) {
    header('Location: ../login.php');
    exit;
}

$pageTitle = 'All Ticket Orders';
require 'partials/header.php';
?>
<h1>Ticket Orders</h1>
<table class="table" style="width:100%; border-collapse:collapse;">
    <thead>
        <tr>
            <th>Order Ref</th>
            <th>Buyer</th>
            <th>Email</th>
            <th>Total</th>
            <th>Status</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $orders = db_fetch_all('SELECT o.*, u.name AS user_name FROM orders o LEFT JOIN users u ON u.id = o.user_id ORDER BY o.created_at DESC');
        foreach ($orders as $o):
        ?>
        <tr>
            <td><?= htmlspecialchars($o->order_ref) ?></td>
            <td><?= htmlspecialchars($o->buyer_name) ?><?php if ($o->user_name): ?> (<?= htmlspecialchars($o->user_name) ?>)<?php endif; ?></td>
            <td><?= htmlspecialchars($o->buyer_email) ?></td>
            <td><?= format_myr($o->total_amount) ?></td>
            <td><?= htmlspecialchars(ucfirst($o->payment_status)) ?></td>
            <td><?= date('d M Y H:i', strtotime($o->created_at)) ?></td>
            <td>
                <a href="order_view.php?order_ref=<?= urlencode($o->order_ref) ?>" class="btn">View</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php require '../partials/footer.php'; ?>
