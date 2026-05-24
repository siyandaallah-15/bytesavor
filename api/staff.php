<?php
// ============================================================
//  ByteSavor — api/staff.php
//
//  GET  ?action=list              → all staff for restaurant
//  GET  ?action=get&id=X          → single staff member
//  POST action=add                → add new staff member
//  POST action=edit               → edit staff details
//  POST action=toggle_active      → activate / deactivate
//  POST action=reset_pin          → manager resets a staff PIN
//  POST action=enroll_biometric   → placeholder for fingerprint
//  POST action=clock_in           → record clock-in time
//  POST action=clock_out          → record clock-out time
//  GET  ?action=on_shift          → who is currently clocked in
// ============================================================

require_once __DIR__ . '/auth.php';

$db     = getRestaurantDB($restaurantId);
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$user   = me();

switch ($action) {

    // ════════════════════════════════════════
    //  GET: All staff for this restaurant
    // ════════════════════════════════════════
    case 'list':
        requireMethod('GET');
        requireRole(['manager', 'admin']);

        $stmt = $db->prepare("
            SELECT
                id, name, staff_id, role, is_active,
                pin_set, bio_enrolled, last_login, created_at
            FROM users
            WHERE restaurant_id = ?
            ORDER BY role ASC, name ASC
        ");
        $stmt->execute([$restaurantId]);
        ok($stmt->fetchAll());
        break;

    // ════════════════════════════════════════
    //  GET: Single staff member
    // ════════════════════════════════════════
    case 'get':
        requireMethod('GET');
        requireRole(['manager', 'admin']);

        $id = intval($_GET['id'] ?? 0);
        if (!$id) fail('Staff ID required.');

        $stmt = $db->prepare("
            SELECT id, name, staff_id, role, is_active,
                   pin_set, bio_enrolled, last_login, created_at
            FROM users WHERE id = ? AND restaurant_id = ?
        ");
        $stmt->execute([$id, $restaurantId]);
        $staff = $stmt->fetch();
        if (!$staff) fail('Staff member not found.', 404);

        ok($staff);
        break;

    // ════════════════════════════════════════
    //  POST: Add new staff member
    //  Manager sets name, role, PIN
    //  Staff ID is auto-generated
    // ════════════════════════════════════════
    case 'add':
        requireMethod('POST');
        requireRole(['manager', 'admin']);

        $body = jsonBody();
        $name = trim($body['name'] ?? '');
        $role = trim($body['role'] ?? 'waiter');
        $pin  = trim($body['pin']  ?? '');

        if (!$name) fail('Staff name is required.');
        if (!in_array($role, ['admin','manager','cashier','waiter'])) fail('Invalid role.');
        if (strlen($pin) !== 4 || !ctype_digit($pin)) fail('PIN must be exactly 4 digits.');

        // Auto-generate staff ID — prefix by role
        $prefixMap = ['waiter'=>'W','cashier'=>'C','manager'=>'M','admin'=>'A'];
        $prefix    = $prefixMap[$role];

        $countStmt = $db->prepare("
            SELECT COUNT(*) AS cnt FROM users
            WHERE restaurant_id = ? AND role = ?
        ");
        $countStmt->execute([$restaurantId, $role]);
        $count    = $countStmt->fetch()['cnt'];
        $staffId  = $prefix . str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        // Make sure staff ID is unique — increment if taken
        $checkStmt = $db->prepare("SELECT id FROM users WHERE staff_id = ? AND restaurant_id = ?");
        $attempt   = 0;
        do {
            $attempt++;
            $checkStmt->execute([$staffId, $restaurantId]);
            if ($checkStmt->fetch()) {
                $staffId = $prefix . str_pad($count + $attempt, 3, '0', STR_PAD_LEFT);
            } else {
                break;
            }
        } while ($attempt < 100);

        $pinHash = password_hash($pin, PASSWORD_BCRYPT);

        $stmt = $db->prepare("
            INSERT INTO users
                (name, staff_id, pin_hash, role, restaurant_id, is_active, pin_set, bio_enrolled)
            VALUES (?, ?, ?, ?, ?, 1, 1, 0)
        ");
        $stmt->execute([$name, $staffId, $pinHash, $role, $restaurantId]);
        $newId = $db->lastInsertId();

        auditLog($db, $restaurantId, 'change',
            "New staff added: $name ($staffId) — role: $role"
        );

        ok([
            'id'       => $newId,
            'staff_id' => $staffId,
            'name'     => $name,
            'role'     => $role,
        ], "$name added as $role ($staffId).");
        break;

    // ════════════════════════════════════════
    //  POST: Edit staff details
    // ════════════════════════════════════════
    case 'edit':
        requireMethod('POST');
        requireRole(['manager', 'admin']);

        $body   = jsonBody();
        $id     = intval($body['id'] ?? 0);
        if (!$id) fail('Staff ID required.');

        $allowed = ['name', 'role'];
        $fields  = [];
        $params  = [];

        foreach ($allowed as $f) {
            if (array_key_exists($f, $body)) {
                $fields[] = "$f = ?";
                $params[] = trim($body[$f]);
            }
        }
        if (empty($fields)) fail('Nothing to update.');

        $params[] = $id;
        $params[] = $restaurantId;

        $db->prepare("UPDATE users SET " . implode(', ', $fields) . " WHERE id = ? AND restaurant_id = ?")
           ->execute($params);

        auditLog($db, $restaurantId, 'change', "Staff #$id details updated");
        ok(['id' => $id], 'Staff details updated.');
        break;

    // ════════════════════════════════════════
    //  POST: Activate or deactivate a staff member
    // ════════════════════════════════════════
    case 'toggle_active':
        requireMethod('POST');
        requireRole(['manager', 'admin']);

        $body = jsonBody();
        $id   = intval($body['id'] ?? 0);
        if (!$id) fail('Staff ID required.');

        // Cannot deactivate yourself
        if ($id === $user['id']) fail('You cannot deactivate your own account.');

        $db->prepare("
            UPDATE users SET is_active = NOT is_active
            WHERE id = ? AND restaurant_id = ?
        ")->execute([$id, $restaurantId]);

        $s = $db->prepare("SELECT name, staff_id, is_active FROM users WHERE id = ?");
        $s->execute([$id]);
        $s = $s->fetch();

        auditLog($db, $restaurantId, 'change',
            "Staff {$s['name']} ({$s['staff_id']}) " . ($s['is_active'] ? 'activated' : 'deactivated')
        );

        ok([
            'id'        => $id,
            'is_active' => $s['is_active'],
        ], "{$s['name']} " . ($s['is_active'] ? 'activated' : 'deactivated') . '.');
        break;

    // ════════════════════════════════════════
    //  POST: Reset a staff member's PIN
    //  Manager only — sets a new hashed PIN
    // ════════════════════════════════════════
    case 'reset_pin':
        requireMethod('POST');
        requireRole(['manager', 'admin']);

        $body      = jsonBody();
        $id        = intval($body['id']  ?? 0);
        $newPin    = trim($body['pin']   ?? '');

        if (!$id)   fail('Staff ID required.');
        if (strlen($newPin) !== 4 || !ctype_digit($newPin)) {
            fail('PIN must be exactly 4 digits.');
        }

        $pinHash = password_hash($newPin, PASSWORD_BCRYPT);

        $db->prepare("
            UPDATE users SET pin_hash = ?, pin_set = 1
            WHERE id = ? AND restaurant_id = ?
        ")->execute([$pinHash, $id, $restaurantId]);

        $s = $db->prepare("SELECT name, staff_id FROM users WHERE id = ?");
        $s->execute([$id]);
        $s = $s->fetch();

        auditLog($db, $restaurantId, 'pin',
            "PIN reset for {$s['name']} ({$s['staff_id']}) by {$user['name']}"
        );

        ok(['id' => $id], "PIN updated for {$s['name']}.");
        break;

    // ════════════════════════════════════════
    //  POST: Enroll biometric fingerprint
    //  Placeholder — wire to hardware SDK
    //  when fingerprint scanner is connected
    // ════════════════════════════════════════
    case 'enroll_biometric':
        requireMethod('POST');
        requireRole(['manager', 'admin']);

        $body = jsonBody();
        $id   = intval($body['id'] ?? 0);
        if (!$id) fail('Staff ID required.');

        // ── PRODUCTION: Replace this block with your
        //    fingerprint SDK integration.
        //    The SDK should:
        //    1. Prompt the scanner to capture fingerprint
        //    2. Return a unique biometric template/hash
        //    3. Store that hash in the bio_template column
        //
        //    $bioTemplate = FingerprintSDK::capture();
        //    Store $bioTemplate in users.bio_template
        // ──

        // For now: mark as enrolled (demo)
        $db->prepare("
            UPDATE users SET bio_enrolled = 1 WHERE id = ? AND restaurant_id = ?
        ")->execute([$id, $restaurantId]);

        $s = $db->prepare("SELECT name, staff_id FROM users WHERE id = ?");
        $s->execute([$id]);
        $s = $s->fetch();

        auditLog($db, $restaurantId, 'change',
            "Biometric enrolled for {$s['name']} ({$s['staff_id']})"
        );

        ok(['id' => $id], "Fingerprint enrolled for {$s['name']}. Connect scanner to capture.");
        break;

    // ════════════════════════════════════════
    //  POST: Clock in
    // ════════════════════════════════════════
    case 'clock_in':
        requireMethod('POST');

        $db->prepare("
            INSERT INTO shift_log (user_id, restaurant_id, clock_in)
            VALUES (?, ?, NOW())
        ")->execute([$user['id'], $restaurantId]);

        auditLog($db, $restaurantId, 'login',
            "{$user['name']} ({$user['staff_id']}) clocked in"
        );

        ok([], 'Clocked in successfully.');
        break;

    // ════════════════════════════════════════
    //  POST: Clock out
    // ════════════════════════════════════════
    case 'clock_out':
        requireMethod('POST');

        $db->prepare("
            UPDATE shift_log SET clock_out = NOW()
            WHERE user_id = ? AND restaurant_id = ?
              AND clock_out IS NULL
            ORDER BY clock_in DESC LIMIT 1
        ")->execute([$user['id'], $restaurantId]);

        auditLog($db, $restaurantId, 'logout',
            "{$user['name']} ({$user['staff_id']}) clocked out"
        );

        ok([], 'Clocked out successfully.');
        break;

    // ════════════════════════════════════════
    //  GET: Staff currently on shift
    // ════════════════════════════════════════
    case 'on_shift':
        requireMethod('GET');
        requireRole(['manager', 'admin']);

        $stmt = $db->prepare("
            SELECT u.id, u.name, u.staff_id, u.role,
                   sl.clock_in,
                   TIMESTAMPDIFF(MINUTE, sl.clock_in, NOW()) AS minutes_on_shift
            FROM shift_log sl
            JOIN users u ON sl.user_id = u.id
            WHERE sl.restaurant_id = ?
              AND sl.clock_out IS NULL
              AND DATE(sl.clock_in) = CURDATE()
            ORDER BY sl.clock_in ASC
        ");
        $stmt->execute([$restaurantId]);
        ok($stmt->fetchAll());
        break;

    default:
        fail('Unknown action: ' . htmlspecialchars($action));
}
?>
