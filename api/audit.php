<?php
// ============================================================
//  ByteSavor — api/audit.php
//
//  GET  ?action=list              → paginated audit trail
//  GET  ?action=export            → export as CSV
//
//  Audit entries are WRITTEN by every other API file
//  using the auditLog() helper in auth.php.
//  This file only READS them.
//
//  Every action in the system is logged:
//  login, logout, order, payment, void,
//  pin, menu, change
// ============================================================

require_once __DIR__ . '/auth.php';

$db     = getRestaurantDB($restaurantId);
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {

    // ════════════════════════════════════════
    //  GET: Paginated audit trail with filters
    // ════════════════════════════════════════
    case 'list':
        requireMethod('GET');
        requireRole(['manager', 'admin']);

        // Filters
        $type     = $_GET['type']      ?? null;   // login|logout|order|payment|void|pin|menu|change
        $staffId  = $_GET['staff_id']  ?? null;   // e.g. W001
        $from     = $_GET['from']      ?? date('Y-m-d');
        $to       = $_GET['to']        ?? date('Y-m-d');
        $search   = trim($_GET['search'] ?? '');
        $page     = max(1, intval($_GET['page'] ?? 1));
        $perPage  = 50;
        $offset   = ($page - 1) * $perPage;

        $where  = ['restaurant_id = ?', 'created_at BETWEEN ? AND ?'];
        $params = [$restaurantId, $from . ' 00:00:00', $to . ' 23:59:59'];

        if ($type)    { $where[] = 'event_type = ?'; $params[] = $type; }
        if ($staffId) { $where[] = 'staff_id = ?';   $params[] = $staffId; }
        if ($search)  {
            $where[]  = '(staff_name LIKE ? OR detail LIKE ?)';
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $whereStr = implode(' AND ', $where);

        // Total count for pagination
        $countStmt = $db->prepare("SELECT COUNT(*) AS total FROM audit_log WHERE $whereStr");
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];

        // Fetch page
        $stmt = $db->prepare("
            SELECT id, event_type, staff_id, staff_name, detail, created_at
            FROM audit_log
            WHERE $whereStr
            ORDER BY created_at DESC
            LIMIT $perPage OFFSET $offset
        ");
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        ok([
            'rows'       => $rows,
            'total'      => $total,
            'page'       => $page,
            'per_page'   => $perPage,
            'total_pages'=> ceil($total / $perPage),
        ]);
        break;

    // ════════════════════════════════════════
    //  GET: Export audit trail as CSV
    // ════════════════════════════════════════
    case 'export':
        requireMethod('GET');
        requireRole(['admin']);

        $from = $_GET['from'] ?? date('Y-m-d');
        $to   = $_GET['to']   ?? date('Y-m-d');

        $stmt = $db->prepare("
            SELECT created_at, event_type, staff_id, staff_name, detail
            FROM audit_log
            WHERE restaurant_id = ?
              AND created_at BETWEEN ? AND ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$restaurantId, $from . ' 00:00:00', $to . ' 23:59:59']);
        $rows = $stmt->fetchAll();

        // Override JSON header for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="bytesavor-audit-' . $from . '-to-' . $to . '.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['Timestamp', 'Type', 'Staff ID', 'Staff Name', 'Detail']);
        foreach ($rows as $row) {
            fputcsv($out, [
                $row['created_at'],
                $row['event_type'],
                $row['staff_id'],
                $row['staff_name'],
                $row['detail'],
            ]);
        }
        fclose($out);
        exit;

    default:
        fail('Unknown action: ' . htmlspecialchars($action));
}
?>
