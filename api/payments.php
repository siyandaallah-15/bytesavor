<?php
// ============================================================
//  ByteSavor — api/payments.php
//
//  POST action=process       → process a full payment
//  POST action=split         → process a split payment
//  GET  ?action=list         → all payments for a date range
//  GET  ?action=get&id=X     → single payment detail
//  POST action=refund        → refund a payment (admin only)
//  POST action=receipt       → send receipt via email/WhatsApp
// ============================================================

require_once __DIR__ . '/auth.php';

$db     = getRestaurantDB($restaurantId);
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$user   = me();

switch ($action) {

    // ════════════════════════════════════════
    //  POST: Process a full payment
    //  Called by cashier/waiter payment screen
    // ════════════════════════════════════════
    case 'process':
        requireMethod('POST');
        requireRole(['waiter', 'cashier', 'manager', 'admin']);

        $body      = jsonBody();
        $orderId   = intval($body['order_id']  ?? 0);
        $method    = trim($body['method']      ?? '');
        $amount    = floatval($body['amount']  ?? 0);
        $reference = trim($body['reference']   ?? '');
        $tendered  = floatval($body['tendered'] ?? 0); // Cash only

        if (!$orderId) fail('Order ID required.');
        if (!$method)  fail('Payment method required.');
        if ($amount <= 0) fail('Payment amount must be greater than zero.');

        $validMethods = ['cash','card','tap','eft','snapscan','zapper','ubereats','mrd','online'];
        if (!in_array($method, $validMethods)) fail('Invalid payment method.');

        // Verify order exists and belongs to this restaurant
        $orderStmt = $db->prepare("
            SELECT o.id, o.total, o.status, o.waiter_id, o.table_id
            FROM orders o
            WHERE o.id = ? AND o.restaurant_id = ?
        ");
        $orderStmt->execute([$orderId, $restaurantId]);
        $order = $orderStmt->fetch();

        if (!$order) fail('Order not found.', 404);
        if (in_array($order['status'], ['paid','cancelled'])) {
            fail('This order has already been closed.');
        }

        // Bill ownership check —
        // Only the staff who opened the bill can close it
        // (exception: cashier, manager, admin can close any bill)
        $role = $_SESSION['role'];
        if (!in_array($role, ['cashier','manager','admin']) && $order['waiter_id'] != $user['id']) {
            fail('Only the staff member who opened this bill can close it.', 403);
        }

        // Cash: verify tendered amount covers total
        if ($method === 'cash' && $tendered > 0 && $tendered < $amount) {
            fail('Amount tendered is less than the total due.');
        }

        $change = ($method === 'cash' && $tendered > 0) ? ($tendered - $amount) : 0;

        $db->beginTransaction();
        try {
            // Insert payment record
            $stmt = $db->prepare("
                INSERT INTO payments
                    (order_id, amount, method, status, reference, tendered, change_given, created_at)
                VALUES (?, ?, ?, 'completed', ?, ?, ?, NOW())
            ");
            $stmt->execute([$orderId, $amount, $method, $reference ?: null, $tendered ?: null, $change]);
            $paymentId = $db->lastInsertId();

            // Mark order as paid
            $db->prepare("
                UPDATE orders SET status = 'paid', updated_at = NOW()
                WHERE id = ? AND restaurant_id = ?
            ")->execute([$orderId, $restaurantId]);

            // Set table to cleaning if dine-in
            if ($order['table_id']) {
                $db->prepare("
                    UPDATE tables_layout
                    SET status = 'cleaning', cleaning_since = NOW()
                    WHERE id = ? AND restaurant_id = ?
                ")->execute([$order['table_id'], $restaurantId]);
            }

            $db->commit();

            auditLog($db, $restaurantId, 'payment',
                "Payment R" . number_format($amount, 2) . " via $method — Order #$orderId" .
                ($reference ? " Ref: $reference" : '') .
                ($change > 0 ? " Change: R" . number_format($change, 2) : '')
            );

            ok([
                'payment_id' => $paymentId,
                'order_id'   => $orderId,
                'amount'     => $amount,
                'change'     => $change,
                'method'     => $method,
            ], 'Payment processed successfully.');

        } catch (Exception $e) {
            $db->rollBack();
            error_log('Payment error: ' . $e->getMessage());
            fail('Payment processing failed. Please try again.');
        }
        break;

    // ════════════════════════════════════════
    //  POST: Process a split payment
    //  Partial payment on selected items only
    // ════════════════════════════════════════
    case 'split':
        requireMethod('POST');
        requireRole(['waiter', 'cashier', 'manager', 'admin']);

        $body       = jsonBody();
        $orderId    = intval($body['order_id']   ?? 0);
        $itemIds    = $body['item_ids']           ?? []; // order_item IDs to split
        $method     = trim($body['method']        ?? '');
        $reference  = trim($body['reference']     ?? '');
        $tendered   = floatval($body['tendered']  ?? 0);

        if (!$orderId || empty($itemIds)) fail('Order ID and item IDs required.');
        if (!$method) fail('Payment method required.');

        // Calculate amount for selected items only
        $placeholders = implode(',', array_fill(0, count($itemIds), '?'));
        $splitStmt = $db->prepare("
            SELECT SUM(quantity * unit_price) AS split_total
            FROM order_items
            WHERE id IN ($placeholders) AND order_id = ?
        ");
        $splitStmt->execute([...$itemIds, $orderId]);
        $result = $splitStmt->fetch();
        $splitAmount = floatval($result['split_total'] ?? 0);

        if ($splitAmount <= 0) fail('No valid items selected for split.');

        $change = ($method === 'cash' && $tendered > 0) ? ($tendered - $splitAmount) : 0;

        // Insert split payment
        $stmt = $db->prepare("
            INSERT INTO payments
                (order_id, amount, method, status, reference, tendered, change_given, is_split, created_at)
            VALUES (?, ?, ?, 'completed', ?, ?, ?, 1, NOW())
        ");
        $stmt->execute([$orderId, $splitAmount, $method, $reference ?: null, $tendered ?: null, $change]);
        $paymentId = $db->lastInsertId();

        auditLog($db, $restaurantId, 'payment',
            "Split payment R" . number_format($splitAmount, 2) . " via $method — Order #$orderId"
        );

        ok([
            'payment_id'   => $paymentId,
            'split_amount' => $splitAmount,
            'change'       => $change,
        ], 'Split payment processed.');
        break;

    // ════════════════════════════════════════
    //  GET: List payments with date filter
    // ════════════════════════════════════════
    case 'list':
        requireMethod('GET');
        requireRole(['manager', 'admin']);

        $from   = $_GET['from'] ?? date('Y-m-d');
        $to     = $_GET['to']   ?? date('Y-m-d');
        $method = $_GET['method'] ?? null;

        $where  = ['o.restaurant_id = ?', 'p.created_at BETWEEN ? AND ?'];
        $params = [$restaurantId, $from . ' 00:00:00', $to . ' 23:59:59'];

        if ($method) { $where[] = 'p.method = ?'; $params[] = $method; }

        $stmt = $db->prepare("
            SELECT p.*, o.order_type, tl.table_number,
                   u.name AS staff_name, u.staff_id
            FROM payments p
            JOIN orders o ON p.order_id = o.id
            LEFT JOIN tables_layout tl ON o.table_id = tl.id
            LEFT JOIN users u ON o.waiter_id = u.id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY p.created_at DESC
        ");
        $stmt->execute($params);
        ok($stmt->fetchAll());
        break;

    // ════════════════════════════════════════
    //  GET: Single payment detail
    // ════════════════════════════════════════
    case 'get':
        requireMethod('GET');
        requireRole(['waiter', 'cashier', 'manager', 'admin']);

        $id   = intval($_GET['id'] ?? 0);
        if (!$id) fail('Payment ID required.');

        $stmt = $db->prepare("
            SELECT p.*, o.order_type, o.notes AS order_notes,
                   tl.table_number,
                   u.name AS staff_name, u.staff_id
            FROM payments p
            JOIN orders o ON p.order_id = o.id
            LEFT JOIN tables_layout tl ON o.table_id  = tl.id
            LEFT JOIN users         u  ON o.waiter_id = u.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        $payment = $stmt->fetch();
        if (!$payment) fail('Payment not found.', 404);

        // Get order items for receipt
        $itemStmt = $db->prepare("
            SELECT oi.quantity, oi.unit_price, oi.notes, oi.is_takeaway,
                   mi.name AS item_name, mi.emoji
            FROM order_items oi
            JOIN menu_items mi ON oi.menu_item_id = mi.id
            WHERE oi.order_id = ?
        ");
        $itemStmt->execute([$payment['order_id']]);
        $payment['items'] = $itemStmt->fetchAll();

        ok($payment);
        break;

    // ════════════════════════════════════════
    //  POST: Refund a payment (admin only)
    // ════════════════════════════════════════
    case 'refund':
        requireMethod('POST');
        requireRole(['admin']);

        $body   = jsonBody();
        $id     = intval($body['payment_id'] ?? 0);
        $reason = trim($body['reason']       ?? '');

        if (!$id)     fail('Payment ID required.');
        if (!$reason) fail('Reason is required for a refund.');

        $db->prepare("
            UPDATE payments SET status = 'refunded' WHERE id = ?
        ")->execute([$id]);

        auditLog($db, $restaurantId, 'payment', "Payment #$id refunded — Reason: $reason");
        ok(['payment_id' => $id], 'Payment refunded and logged.');
        break;

    // ════════════════════════════════════════
    //  POST: Send receipt via email or WhatsApp
    //  In production: wire to email/WA API
    // ════════════════════════════════════════
    case 'receipt':
        requireMethod('POST');
        requireRole(['waiter', 'cashier', 'manager', 'admin']);

        $body      = jsonBody();
        $paymentId = intval($body['payment_id'] ?? 0);
        $email     = trim($body['email']        ?? '');
        $whatsapp  = trim($body['whatsapp']     ?? '');
        $print     = $body['print']             ?? false;

        if (!$paymentId) fail('Payment ID required.');
        if (!$email && !$whatsapp && !$print) fail('Please choose at least one receipt option.');

        // In production:
        // if ($email)    sendReceiptEmail($paymentId, $email);
        // if ($whatsapp) sendReceiptWhatsApp($paymentId, $whatsapp);
        // if ($print)    triggerPrint($paymentId);

        $sent = [];
        if ($print)    $sent[] = 'print';
        if ($email)    $sent[] = 'email to ' . $email;
        if ($whatsapp) $sent[] = 'WhatsApp to ' . $whatsapp;

        auditLog($db, $restaurantId, 'payment',
            "Receipt for Payment #$paymentId sent via: " . implode(', ', $sent)
        );

        ok(['sent_via' => $sent], 'Receipt sent via: ' . implode(', ', $sent));
        break;

    default:
        fail('Unknown action: ' . htmlspecialchars($action));
}
?>
