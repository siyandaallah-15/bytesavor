<?php
// ============================================================
//  ByteSavor — api/tables.php
//
//  GET  ?action=list                → all tables + status
//  POST action=update_status        → update a table status
//  POST action=reserve              → manager reserves a table
//  POST action=unreserve            → manager removes reservation
//  POST action=check_cleaning       → auto-release cleaning tables
//                                     after 5 minutes
// ============================================================

require_once __DIR__ . '/auth.php';

$db     = getRestaurantDB($restaurantId);
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {

    // ════════════════════════════════════════
    //  GET: All tables with current status
    //  Polled every 30 seconds by waiter screen
    // ════════════════════════════════════════
    case 'list':
        requireMethod('GET');

        // First auto-release any cleaning tables over 5 minutes
        $db->prepare("
            UPDATE tables_layout
            SET status = 'available', cleaning_since = NULL
            WHERE restaurant_id = ?
              AND status = 'cleaning'
              AND cleaning_since IS NOT NULL
              AND TIMESTAMPDIFF(SECOND, cleaning_since, NOW()) >= 300
        ")->execute([$restaurantId]);

        $stmt = $db->prepare("
            SELECT
                id, table_number, capacity, status,
                reserved_for, reserved_time,
                cleaning_since,
                CASE
                    WHEN status = 'cleaning' AND cleaning_since IS NOT NULL
                    THEN GREATEST(0, 300 - TIMESTAMPDIFF(SECOND, cleaning_since, NOW()))
                    ELSE NULL
                END AS cleaning_seconds_left
            FROM tables_layout
            WHERE restaurant_id = ?
            ORDER BY table_number ASC
        ");
        $stmt->execute([$restaurantId]);
        ok($stmt->fetchAll());
        break;

    // ════════════════════════════════════════
    //  POST: Update table status
    //  Called when order is created/closed
    // ════════════════════════════════════════
    case 'update_status':
        requireMethod('POST');
        requireRole(['waiter', 'cashier', 'manager', 'admin']);

        $body   = jsonBody();
        $id     = intval($body['table_id'] ?? 0);
        $status = $body['status'] ?? '';

        $valid = ['available', 'occupied', 'cleaning', 'reserved'];
        if (!$id || !in_array($status, $valid)) fail('Invalid table ID or status.');

        $cleaningSince = $status === 'cleaning' ? 'NOW()' : 'NULL';

        $db->prepare("
            UPDATE tables_layout
            SET status = ?,
                cleaning_since = " . ($status === 'cleaning' ? 'NOW()' : 'NULL') . "
            WHERE id = ? AND restaurant_id = ?
        ")->execute([$status, $id, $restaurantId]);

        auditLog($db, $restaurantId, 'change', "Table #$id status → $status");
        ok(['table_id' => $id, 'status' => $status], 'Table status updated.');
        break;

    // ════════════════════════════════════════
    //  POST: Reserve a table (manager only)
    // ════════════════════════════════════════
    case 'reserve':
        requireMethod('POST');
        requireRole(['manager', 'admin']);

        $body        = jsonBody();
        $id          = intval($body['table_id']      ?? 0);
        $reservedFor = trim($body['reserved_for']    ?? '');
        $reservedTime= trim($body['reserved_time']   ?? '');

        if (!$id)          fail('Table ID required.');
        if (!$reservedFor) fail('Reservation name is required.');

        $db->prepare("
            UPDATE tables_layout
            SET status = 'reserved',
                reserved_for = ?,
                reserved_time = ?,
                cleaning_since = NULL
            WHERE id = ? AND restaurant_id = ?
        ")->execute([$reservedFor, $reservedTime ?: null, $id, $restaurantId]);

        auditLog($db, $restaurantId, 'change',
            "Table #$id reserved for '$reservedFor'" . ($reservedTime ? " at $reservedTime" : '')
        );
        ok(['table_id' => $id], 'Table reserved.');
        break;

    // ════════════════════════════════════════
    //  POST: Remove reservation (manager only)
    // ════════════════════════════════════════
    case 'unreserve':
        requireMethod('POST');
        requireRole(['manager', 'admin']);

        $body = jsonBody();
        $id   = intval($body['table_id'] ?? 0);
        if (!$id) fail('Table ID required.');

        $db->prepare("
            UPDATE tables_layout
            SET status = 'available',
                reserved_for = NULL,
                reserved_time = NULL
            WHERE id = ? AND restaurant_id = ?
        ")->execute([$id, $restaurantId]);

        auditLog($db, $restaurantId, 'change', "Table #$id reservation removed");
        ok(['table_id' => $id], 'Reservation removed.');
        break;

    // ════════════════════════════════════════
    //  POST: Force check and release cleaning tables
    //  Called automatically every 30 seconds
    //  by the waiter and kitchen screens
    // ════════════════════════════════════════
    case 'check_cleaning':
        requireMethod('POST');

        $stmt = $db->prepare("
            UPDATE tables_layout
            SET status = 'available', cleaning_since = NULL
            WHERE restaurant_id = ?
              AND status = 'cleaning'
              AND cleaning_since IS NOT NULL
              AND TIMESTAMPDIFF(SECOND, cleaning_since, NOW()) >= 300
        ");
        $stmt->execute([$restaurantId]);
        $released = $stmt->rowCount();

        ok(['released' => $released],
            $released > 0 ? "$released table(s) released from cleaning." : 'No tables to release.');
        break;

    default:
        fail('Unknown action: ' . htmlspecialchars($action));
}
?>
