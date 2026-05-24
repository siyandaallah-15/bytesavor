<?php
// ============================================================
//  ByteSavor — api/orders.php
//
//  Handles all order operations:
//  GET    ?action=list              → all open orders (kitchen + waiter)
//  GET    ?action=get&id=X          → single order detail
//  POST   action=create             → waiter creates a new order
//  POST   action=update_status      → kitchen advances order status
//  POST   action=add_item           → add item to existing order
//  POST   action=remove_item        → remove item from order
//  POST   action=void               → manager voids an order
//  POST   action=close              → cashier closes a paid order
//
//  Kitchen display polls GET?action=list every 30 seconds.
// ============================================================

require_once __DIR__ . '/auth.php';

$db     = getRestaurantDB($restaurantId);
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$user   = me();

switch ($action) {

    // ════════════════════════════════════════
    //  GET: List all open orders
    //  Used by: kitchen display, waiter screen
    //  Polls every 30 seconds
    // ════════════════════════════════════════
    case 'list':
        requireMethod('GET');

        // Optional filter by status
        $status = $_GET['status'] ?? null;
        $type   = $_GET['type']   ?? null;

        $where  = ['o.restaurant_id = ?'];
        $params = [$restaurantId];

        if ($status) { $where[] = 'o.status = ?';     $params[] = $status; }
        if ($type)   { $where[] = 'o.order_type = ?'; $params[] = $type;   }

        // Default: exclude fully collected orders from live feed
        if (!$status) {
            $where[] = "o.status != 'collected'";
        }

        $sql = "
            SELECT
                o.id, o.order_type, o.status, o.total, o.notes,
                o.created_at, o.updated_at,
                tl.table_number,
                u.name AS waiter_name, u.staff_id AS waiter_staff_id,
                COUNT(oi.id) AS item_count
            FROM orders o
            LEFT JOIN tables_layout tl ON o.table_id = tl.id
            LEFT JOIN users         u  ON o.waiter_id = u.id
            LEFT JOIN order_items   oi ON oi.order_id = o.id
            WHERE " . implode(' AND ', $where) . "
            GROUP BY o.id
            ORDER BY o.created_at ASC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll();

        // Attach items to each order
        foreach ($orders as &$order) {
            $stmt2 = $db->prepare("
                SELECT oi.id, oi.quantity, oi.unit_price, oi.notes, oi.is_takeaway,
                       mi.name AS item_name, mi.emoji
                FROM order_items oi
                JOIN menu_items mi ON oi.menu_item_id = mi.id
                WHERE oi.order_id = ?
            ");
            $stmt2->execute([$order['id']]);
            $order['items'] = $stmt2->fetchAll();
        }

        ok($orders);
        break;

    // ════════════════════════════════════════
    //  GET: Single order detail
    // ════════════════════════════════════════
    case 'get':
        requireMethod('GET');
        $id = intval($_GET['id'] ?? 0);
        if (!$id) fail('Order ID required.');

        $stmt = $db->prepare("
            SELECT o.*, tl.table_number,
                   u.name AS waiter_name, u.staff_id AS waiter_staff_id
            FROM orders o
            LEFT JOIN tables_layout tl ON o.table_id  = tl.id
            LEFT JOIN users         u  ON o.waiter_id = u.id
            WHERE o.id = ? AND o.restaurant_id = ?
        ");
        $stmt->execute([$id, $restaurantId]);
        $order = $stmt->fetch();
        if (!$order) fail('Order not found.', 404);

        $stmt2 = $db->prepare("
            SELECT oi.*, mi.name AS item_name, mi.emoji
            FROM order_items oi
            JOIN menu_items mi ON oi.menu_item_id = mi.id
            WHERE oi.order_id = ?
        ");
        $stmt2->execute([$id]);
        $order['items'] = $stmt2->fetchAll();

        ok($order);
        break;

    // ════════════════════════════════════════
    //  POST: Create new order
    //  Called by waiter when confirming order
    // ════════════════════════════════════════
    case 'create':
        requireMethod('POST');
        requireRole(['waiter', 'cashier', 'manager', 'admin']);

        $body      = jsonBody();
        $tableId   = intval($body['table_id']   ?? 0);
        $orderType = $body['order_type'] ?? 'dine_in';
        $notes     = trim($body['notes'] ?? '');
        $items     = $body['items']      ?? [];

        if (empty($items)) fail('Cannot create an empty order.');

        $validTypes = ['dine_in', 'takeaway', 'online'];
        if (!in_array($orderType, $validTypes)) fail('Invalid order type.');

        // Calculate total
        $total = 0;
        $menuIds = array_column($items, 'menu_item_id');
        if (empty($menuIds)) fail('No valid menu items.');

        $placeholders = implode(',', array_fill(0, count($menuIds), '?'));
        $priceStmt = $db->prepare("SELECT id, price FROM menu_items WHERE id IN ($placeholders) AND restaurant_id = ?");
        $priceStmt->execute([...$menuIds, $restaurantId]);
        $prices = array_column($priceStmt->fetchAll(), 'price', 'id');

        foreach ($items as $item) {
            $mid = intval($item['menu_item_id']);
            $qty = intval($item['quantity'] ?? 1);
            if (!isset($prices[$mid])) fail('Invalid menu item ID: ' . $mid);
            $total += $prices[$mid] * $qty;
        }

        // Insert order
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("
                INSERT INTO orders
                    (restaurant_id, table_id, waiter_id, order_type, status, total, notes)
                VALUES (?, ?, ?, ?, 'open', ?, ?)
            ");
            $stmt->execute([
                $restaurantId,
                $tableId ?: null,
                $user['id'],
                $orderType,
                $total,
                $notes,
            ]);
            $orderId = $db->lastInsertId();

            // Insert each item
            $itemStmt = $db->prepare("
                INSERT INTO order_items
                    (order_id, menu_item_id, quantity, unit_price, notes, is_takeaway)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            foreach ($items as $item) {
                $mid = intval($item['menu_item_id']);
                $qty = intval($item['quantity'] ?? 1);
                $itemStmt->execute([
                    $orderId,
                    $mid,
                    $qty,
                    $prices[$mid],
                    trim($item['notes'] ?? ''),
                    $item['is_takeaway'] ? 1 : 0,
                ]);
            }

            // Mark table as occupied
            if ($tableId) {
                $db->prepare("UPDATE tables_layout SET status = 'occupied' WHERE id = ? AND restaurant_id = ?")
                   ->execute([$tableId, $restaurantId]);
            }

            $db->commit();

            auditLog($db, $restaurantId, 'order',
                "Order #$orderId created — " . ($tableId ? "Table $tableId" : $orderType) . " — R" . number_format($total, 2)
            );

            ok(['order_id' => $orderId, 'total' => $total], 'Order created successfully.');

        } catch (Exception $e) {
            $db->rollBack();
            error_log('Order create error: ' . $e->getMessage());
            fail('Failed to create order. Please try again.');
        }
        break;

    // ════════════════════════════════════════
    //  POST: Update order status
    //  Called by kitchen display buttons
    //  Flow: open → sent_to_kitchen → ready → collected
    // ════════════════════════════════════════
    case 'update_status':
        requireMethod('POST');
        requireRole(['waiter', 'cashier', 'manager', 'admin']);

        $body   = jsonBody();
        $id     = intval($body['order_id'] ?? 0);
        $status = $body['status'] ?? '';

        $validStatuses = ['open', 'sent_to_kitchen', 'ready', 'collected', 'cancelled'];
        if (!$id || !in_array($status, $validStatuses)) fail('Invalid order ID or status.');

        $stmt = $db->prepare("
            UPDATE orders SET status = ?, updated_at = NOW()
            WHERE id = ? AND restaurant_id = ?
        ");
        $stmt->execute([$status, $id, $restaurantId]);

        if ($stmt->rowCount() === 0) fail('Order not found or no change made.', 404);

        auditLog($db, $restaurantId, 'order', "Order #$id status → $status");
        ok(['order_id' => $id, 'status' => $status], 'Order status updated.');
        break;

    // ════════════════════════════════════════
    //  POST: Add item to existing open order
    // ════════════════════════════════════════
    case 'add_item':
        requireMethod('POST');
        requireRole(['waiter', 'manager', 'admin']);

        $body       = jsonBody();
        $orderId    = intval($body['order_id']     ?? 0);
        $menuItemId = intval($body['menu_item_id'] ?? 0);
        $qty        = intval($body['quantity']     ?? 1);
        $notes      = trim($body['notes']          ?? '');
        $isTakeaway = $body['is_takeaway']         ?? false;

        if (!$orderId || !$menuItemId) fail('Order ID and menu item ID required.');

        // Verify order is open and belongs to this restaurant
        $order = $db->prepare("SELECT id, waiter_id, status FROM orders WHERE id = ? AND restaurant_id = ?");
        $order->execute([$orderId, $restaurantId]);
        $order = $order->fetch();

        if (!$order) fail('Order not found.', 404);
        if ($order['status'] === 'collected' || $order['status'] === 'cancelled') {
            fail('Cannot add items to a closed order.');
        }

        // Only the waiter who owns the order can add items (or manager/admin)
        $role = $_SESSION['role'];
        if (!in_array($role, ['manager','admin']) && $order['waiter_id'] != $user['id']) {
            fail('You can only modify your own orders.', 403);
        }

        // Get item price
        $priceStmt = $db->prepare("SELECT price FROM menu_items WHERE id = ? AND restaurant_id = ?");
        $priceStmt->execute([$menuItemId, $restaurantId]);
        $menuItem = $priceStmt->fetch();
        if (!$menuItem) fail('Menu item not found.');

        $db->prepare("
            INSERT INTO order_items (order_id, menu_item_id, quantity, unit_price, notes, is_takeaway)
            VALUES (?, ?, ?, ?, ?, ?)
        ")->execute([$orderId, $menuItemId, $qty, $menuItem['price'], $notes, $isTakeaway ? 1 : 0]);

        // Recalculate order total
        $totStmt = $db->prepare("SELECT SUM(quantity * unit_price) AS total FROM order_items WHERE order_id = ?");
        $totStmt->execute([$orderId]);
        $newTotal = $totStmt->fetch()['total'];
        $db->prepare("UPDATE orders SET total = ? WHERE id = ?")->execute([$newTotal, $orderId]);

        ok(['order_id' => $orderId, 'new_total' => $newTotal], 'Item added to order.');
        break;

    // ════════════════════════════════════════
    //  POST: Void an order (manager only)
    // ════════════════════════════════════════
    case 'void':
        requireMethod('POST');
        requireRole(['manager', 'admin']);

        $body   = jsonBody();
        $id     = intval($body['order_id'] ?? 0);
        $reason = trim($body['reason']    ?? '');

        if (!$id)     fail('Order ID required.');
        if (!$reason) fail('A reason is required to void an order.');

        $db->prepare("
            UPDATE orders SET status = 'cancelled', notes = CONCAT(IFNULL(notes,''), ' | VOID: ', ?), updated_at = NOW()
            WHERE id = ? AND restaurant_id = ?
        ")->execute([$reason, $id, $restaurantId]);

        auditLog($db, $restaurantId, 'void', "Order #$id voided — Reason: $reason");
        ok(['order_id' => $id], 'Order voided and logged.');
        break;

    // ════════════════════════════════════════
    //  POST: Close order after payment
    // ════════════════════════════════════════
    case 'close':
        requireMethod('POST');
        requireRole(['waiter', 'cashier', 'manager', 'admin']);

        $body    = jsonBody();
        $id      = intval($body['order_id'] ?? 0);
        if (!$id) fail('Order ID required.');

        // Verify ownership
        $order = $db->prepare("SELECT waiter_id, table_id, status FROM orders WHERE id = ? AND restaurant_id = ?");
        $order->execute([$id, $restaurantId]);
        $order = $order->fetch();
        if (!$order) fail('Order not found.', 404);

        $role = $_SESSION['role'];
        if (!in_array($role, ['manager','admin','cashier']) && $order['waiter_id'] != $user['id']) {
            fail('Only the staff member who opened this order can close it.', 403);
        }

        $db->prepare("UPDATE orders SET status = 'paid', updated_at = NOW() WHERE id = ? AND restaurant_id = ?")
           ->execute([$id, $restaurantId]);

        // Set table to cleaning if dine-in
        if ($order['table_id']) {
            $db->prepare("UPDATE tables_layout SET status = 'cleaning', cleaning_since = NOW() WHERE id = ? AND restaurant_id = ?")
               ->execute([$order['table_id'], $restaurantId]);
        }

        auditLog($db, $restaurantId, 'order', "Order #$id closed/paid");
        ok(['order_id' => $id], 'Order closed successfully.');
        break;

    default:
        fail('Unknown action: ' . htmlspecialchars($action));
}
?>
