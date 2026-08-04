<?php
require 'config.php';
require 'auth.php';
require 'helpers.php';

$error = '';
$eventId = (int)($_POST['event_id'] ?? $_GET['event_id'] ?? 0);

$event = db_fetch_one("
    SELECT e.*, f.name AS facility_name, f.location, co.name AS court_name
    FROM events e
    JOIN facilities f ON f.id = e.facility_id
    LEFT JOIN courts co ON co.id = e.court_id
    WHERE e.id = ? AND e.status = 'published'
", [$eventId]);

if (!$event) {
    die('Event not found.');
}

$rawTickets = $_POST['tickets'] ?? [];
$selectedItems = [];
$totalAmount = 0.00;
$totalQty = 0;

foreach ($rawTickets as $typeId => $qty) {
    $qty = (int)$qty;
    if ($qty > 0) {
        $tt = db_fetch_one('SELECT * FROM ticket_types WHERE id = ? AND event_id = ?', [(int)$typeId, $eventId]);
        if ($tt) {
            $itemTotal = (float)$tt->price * $qty;
            $selectedItems[] = [
                'ticket_type_id' => $tt->id,
                'name'           => $tt->name,
                'price'          => (float)$tt->price,
                'qty'            => $qty,
                'item_total'     => $itemTotal,
                'remaining'      => $tt->remaining_quantity
            ];
            $totalAmount += $itemTotal;
            $totalQty += $qty;
        }
    }
}

// Check if confirming the purchase
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_order'])) {
    $buyerName  = trim($_POST['buyer_name'] ?? '');
    $buyerEmail = trim($_POST['buyer_email'] ?? '');
    $paymentMethod = $_POST['payment_method'] ?? 'FPX Online Banking';
    $userId = current_user_id();

    if ($buyerName === '' || $buyerEmail === '') {
        $error = 'Buyer name and email address are required.';
    } elseif ($totalQty < 1) {
        $error = 'Please select at least one ticket to purchase.';
    } else {
        try {
            $orderRef = db_transaction(function() use ($userId, $buyerName, $buyerEmail, $totalAmount, $selectedItems) {
                // Verify quantities under transaction lock
                foreach ($selectedItems as $item) {
                    $avail = (int)db_fetch_value('SELECT remaining_quantity FROM ticket_types WHERE id = ?', [$item['ticket_type_id']]);
                    if ($avail < $item['qty']) {
                        throw new Exception("Sorry, remaining stock for " . $item['name'] . " is insufficient (" . $avail . " remaining).");
                    }
                }

                $orderRef = generate_order_ref();
                $orderId = db_insert('orders', [
                    'order_ref'      => $orderRef,
                    'user_id'        => $userId,
                    'buyer_name'     => $buyerName,
                    'buyer_email'    => $buyerEmail,
                    'total_amount'   => $totalAmount,
                    'payment_status' => 'paid'
                ]);

                foreach ($selectedItems as $item) {
                    // Decrement quota
                    db_query('UPDATE ticket_types SET remaining_quantity = remaining_quantity - ? WHERE id = ?', [$item['qty'], $item['ticket_type_id']]);

                    // Generate individual tickets
                    for ($i = 0; $i < $item['qty']; $i++) {
                        $code = generate_ticket_code();
                        db_insert('tickets', [
                            'order_id'       => $orderId,
                            'ticket_type_id' => $item['ticket_type_id'],
                            'ticket_code'    => $code,
                            'attendee_name'  => $buyerName,
                            'attendee_email' => $buyerEmail
                        ]);
                    }
                }

                return $orderRef;
            });

            header('Location: my_tickets.php?order_ref=' . urlencode($orderRef) . '&success=1');
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// Default buyer info for logged in user
$defaultName = current_user_name() ?? '';
$defaultEmail = '';
if ($uid = current_user_id()) {
    $uObj = db_fetch_one('SELECT email FROM users WHERE id = ?', [$uid]);
    if ($uObj) { $defaultEmail = $uObj->email; }
}

$pageTitle = 'Checkout - ' . $event->title;
require 'partials/header.php';
?>
<div style="max-width: 800px; margin: 0 auto;">
<h1>Ticket Order Checkout</h1>
<p class="stat-label">Review your tickets and complete buyer information.</p>

<?php if ($error): ?><p class="alert alert-error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

<?php if (empty($selectedItems)): ?>
<div class="empty-state">
<div class="empty-state-icon">&#9888;&#65039;</div>
<p>No tickets selected. Please select ticket quantities on the event page.</p>
<a href="event_details.php?id=<?= (int)$eventId ?>" class="btn">Back to Event Details</a>
</div>
<?php else: ?>

<div class="form-card" style="margin-bottom: 24px;">
<h2>Event Details</h2>
<p style="margin-bottom: 4px;"><strong><?= htmlspecialchars($event->title) ?></strong></p>
<p class="stat-label">📍 <?= htmlspecialchars($event->facility_name) ?> &middot; 📅 <?= htmlspecialchars(date('D, d M Y @ g:i A', strtotime($event->start_datetime))) ?></p>

<h3 style="margin-top: 20px;">Order Summary</h3>
<table>
<tr><th>Ticket Type</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr>
<?php foreach ($selectedItems as $item): ?>
<tr>
<td><?= htmlspecialchars($item['name']) ?></td>
<td><?= format_myr($item['price']) ?></td>
<td><?= (int)$item['qty'] ?></td>
<td><strong><?= format_myr($item['item_total']) ?></strong></td>
</tr>
<?php endforeach; ?>
<tr style="border-top: 2px solid var(--border-color);">
<td colspan="3" style="text-align: right;"><strong>Total Amount:</strong></td>
<td><strong style="font-size: 1.2rem; color: #28a745;"><?= format_myr($totalAmount) ?></strong></td>
</tr>
</table>
</div>

<div class="form-card">
<h2>Buyer Information &amp; Payment</h2>
<form method="post">
<input type="hidden" name="event_id" value="<?= (int)$eventId ?>">
<input type="hidden" name="confirm_order" value="1">
<?php foreach ($selectedItems as $item): ?>
<input type="hidden" name="tickets[<?= (int)$item['ticket_type_id'] ?>]" value="<?= (int)$item['qty'] ?>">
<?php endforeach; ?>

<label>Full Name <span class="required-mark">*</span>
<input type="text" name="buyer_name" value="<?= htmlspecialchars($_POST['buyer_name'] ?? $defaultName) ?>" required>
</label>

<label>Email Address <span class="required-mark">*</span> (Tickets will be linked to this email)
<input type="email" name="buyer_email" value="<?= htmlspecialchars($_POST['buyer_email'] ?? $defaultEmail) ?>" required>
</label>

<label>Payment Method
<select name="payment_method">
<option value="FPX Online Banking">FPX Online Banking (Maybank, CIMB, RHB, Public Bank)</option>
<option value="TNG eWallet">Touch 'n Go eWallet</option>
<option value="Credit / Debit Card">Credit / Debit Card (Visa / MasterCard)</option>
</select>
</label>

<p class="form-hint">This is a simulated sandbox payment gateway. Clicking "Complete Purchase" instantly verifies quota and issues your digital ticket passes.</p>

<button type="submit" class="btn" style="width: 100%; padding: 12px; font-size: 1.1rem; margin-top: 12px;">Complete Purchase &amp; Issue Tickets &rarr;</button>
</form>
</div>

<?php endif; ?>
</div>
<?php require 'partials/footer.php'; ?>
