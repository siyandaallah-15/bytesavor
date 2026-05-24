<?php
// ============================================================
//  ByteSavor — api/stock.php
//
//  GET  ?action=list              → all stock items
//  GET  ?action=low               → items below low alert level
//  POST action=add                → add a stock item
//  POST action=update             → update stock item details
//  POST action=adjust             → adjust quantity (add/remove)
//  POST action=delete             → remove a stock item
// ============================================================

require_once __DIR__ . '/auth.php';

$db     = getRestaurantDB($restaurantId);
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {

    // ════════════════════════════════════════
    //  GET: All stock items
    // ════════════════════════════════════════
    case 'list':
        requireMethod('GET');
        requireRole(['manager', 'admin']);

        $stmt = $db->prepare("
            SELECT *,
                CASE
                    WHEN quantity <= 0         THEN 'critical'
                    WHEN quantity <= low_alert THEN 'low'
                    ELSE 'ok'
                END AS stock_status
            FROM stock_items
            WHERE restaurant_id = ?
            ORDER BY name ASC
        ");
        $stmt->execute([$restaurantId]);
        ok($stmt->fetchAll());
        break;

    // ════════════════════════════════════════
    //  GET: Items below low stock threshold
    //  Used for dashboard alerts
    // ════════════════════════════════════════
    case 'low':
        requireMethod('GET');
        requireRole(['manager', 'admin']);

        $stmt = $db->prepare("
            SELECT *, 'low' AS stock_status
            FROM stock_items
            WHERE restaurant_id = ? AND quantity <= low_alert
            ORDER BY quantity ASC
        ");
        $stmt->execute([$restaurantId]);
        ok($stmt->fetchAll());
        break;

    // ════════════════════════════════════════
    //  POST: Add a new stock item
    // ════════════════════════════════════════
    case 'add':
        requireMethod('POST');
        requireRole(['manager', 'admin']);

        $body     = jsonBody();
        $name     = trim($body['name']     ?? '');
        $unit     = trim($body['unit']     ?? 'units');
        $qty      = floatval($body['quantity']  ?? 0);
        $low      = floatval($body['low_alert'] ?? 10);
        $cost     = floatval($body['cost_per_unit'] ?? 0);
        $supplier = trim($body['supplier'] ?? '');

        if (!$name) fail('Stock item name is required.');

        $stmt = $db->prepare("
            INSERT INTO stock_items
                (restaurant_id, name, unit, quantity, low_alert, cost_per_unit, supplier)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$restaurantId, $name, $unit, $qty, $low, $cost, $supplier ?: null]);
        $id = $db->lastInsertId();

        auditLog($db, $restaurantId, 'change',
            "Stock item added: $name — $qty $unit"
        );

        ok(['id' => $id], "$name added to stock.");
        break;

    // ════════════════════════════════════════
    //  POST: Update stock item details
    // ════════════════════════════════════════
    case 'update':
        requireMethod('POST');
        requireRole(['manager', 'admin']);

        $body    = jsonBody();
        $id      = intval($body['id'] ?? 0);
        if (!$id) fail('Stock item ID required.');

        $allowed = ['name','unit','low_alert','cost_per_unit','supplier'];
        $fields  = [];
        $params  = [];

        foreach ($allowed as $f) {
            if (array_key_exists($f, $body)) {
                $fields[] = "$f = ?";
                $params[] = in_array($f, ['low_alert','cost_per_unit'])
                    ? floatval($body[$f])
                    : trim($body[$f]);
            }
        }
        if (empty($fields)) fail('Nothing to update.');

        $params[] = $id;
        $params[] = $restaurantId;

        $db->prepare("UPDATE stock_items SET " . implode(', ', $fields) . " WHERE id = ? AND restaurant_id = ?")
           ->execute($params);

        auditLog($db, $restaurantId, 'change', "Stock item #$id updated");
        ok(['id' => $id], 'Stock item updated.');
        break;

    // ════════════════════════════════════════
    //  POST: Adjust quantity
    //  type: 'add' or 'remove'
    //  Used when stock is received or used
    // ════════════════════════════════════════
    case 'adjust':
        requireMethod('POST');
        requireRole(['manager', 'admin']);

        $body   = jsonBody();
        $id     = intval($body['id']       ?? 0);
        $type   = trim($body['type']       ?? 'add'); // 'add' or 'remove'
        $amount = floatval($body['amount'] ?? 0);
        $reason = trim($body['reason']     ?? '');

        if (!$id)      fail('Stock item ID required.');
        if ($amount <= 0) fail('Amount must be greater than zero.');
        if (!in_array($type, ['add','remove'])) fail('Type must be add or remove.');

        $op = $type === 'add' ? '+' : '-';

        $db->prepare("
            UPDATE stock_items
            SET quantity = GREATEST(0, quantity $op ?)
            WHERE id = ? AND restaurant_id = ?
        ")->execute([$amount, $id, $restaurantId]);

        $s = $db->prepare("SELECT name, quantity, unit FROM stock_items WHERE id = ?");
        $s->execute([$id]);
        $s = $s->fetch();

        auditLog($db, $restaurantId, 'change',
            "Stock adjusted: {$s['name']} — $type $amount {$s['unit']}" .
            ($reason ? " — Reason: $reason" : '') .
            " — New qty: {$s['quantity']}"
        );

        ok([
            'id'       => $id,
            'name'     => $s['name'],
            'quantity' => $s['quantity'],
            'unit'     => $s['unit'],
        ], "Stock updated. New quantity: {$s['quantity']} {$s['unit']}.");
        break;

    // ════════════════════════════════════════
    //  POST: Delete a stock item
    // ════════════════════════════════════════
    case 'delete':
        requireMethod('POST');
        requireRole(['admin']);

        $body = jsonBody();
        $id   = intval($body['id'] ?? 0);
        if (!$id) fail('Stock item ID required.');

        $s = $db->prepare("SELECT name FROM stock_items WHERE id = ? AND restaurant_id = ?");
        $s->execute([$id, $restaurantId]);
        $s = $s->fetch();
        if (!$s) fail('Stock item not found.', 404);

        $db->prepare("DELETE FROM stock_items WHERE id = ? AND restaurant_id = ?")
           ->execute([$id, $restaurantId]);

        auditLog($db, $restaurantId, 'change', "Stock item deleted: {$s['name']}");
        ok([], "{$s['name']} removed from stock.");
        break;

    default:
        fail('Unknown action: ' . htmlspecialchars($action));
}
?>
