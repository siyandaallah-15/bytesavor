<?php
// ============================================================
//  ByteSavor — login.php
//  Receives POST from index.html.
//  Checks staff_id + PIN + role, then starts a session
//  and tells the frontend where to redirect.
// ============================================================

session_start();
require_once 'config/config.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'message' => 'Method not allowed.']));
}

header('Content-Type: application/json');

// ── Read inputs ──
$staffId  = trim($_POST['staff_id'] ?? '');   // e.g. W001 (optional)
$pin      = trim($_POST['pin']      ?? '');   // 4-digit PIN
$role     = trim($_POST['role']     ?? '');   // waiter / cashier / manager / admin

// ── Validate ──
if (empty($pin) || strlen($pin) !== 4 || !ctype_digit($pin)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid 4-digit PIN.']);
    exit;
}

$validRoles = ['admin', 'manager', 'cashier', 'waiter'];
if (!in_array($role, $validRoles)) {
    echo json_encode(['success' => false, 'message' => 'Invalid role selected.']);
    exit;
}

// ── Look up the user ──
$db = getDB();

// If a staff ID was entered, look up by staff_id + role
// If no staff ID, look up by PIN + role (PIN must be unique per role in this case)
if (!empty($staffId)) {
    $stmt = $db->prepare("
        SELECT id, name, staff_id, pin_hash, role, is_active
        FROM users
        WHERE staff_id = ? AND role = ?
        LIMIT 1
    ");
    $stmt->execute([$staffId, $role]);
} else {
    $stmt = $db->prepare("
        SELECT id, name, staff_id, pin_hash, role, is_active
        FROM users
        WHERE role = ? AND is_active = 1
        ORDER BY id ASC
        LIMIT 50
    ");
    $stmt->execute([$role]);
}

$users = $stmt->fetchAll();

// ── Verify PIN against matched users ──
$matched = null;
foreach ($users as $u) {
    if (password_verify($pin, $u['pin_hash'])) {
        $matched = $u;
        break;
    }
}

if (!$matched) {
    // Same message for wrong ID or wrong PIN — security best practice
    echo json_encode(['success' => false, 'message' => 'Incorrect Staff ID or PIN. Please try again.']);
    exit;
}

// ── Check account is active ──
if (!$matched['is_active']) {
    echo json_encode(['success' => false, 'message' => 'Your account has been deactivated. Please speak to your manager.']);
    exit;
}

// ── Check role matches ──
if ($matched['role'] !== $role) {
    echo json_encode([
        'success' => false,
        'message' => "This account is registered as a '{$matched['role']}'. Please select the correct role."
    ]);
    exit;
}

// ── All good — start the session ──
session_regenerate_id(true); // Prevents session hijacking

$_SESSION['logged_in']     = true;
$_SESSION['user_id']       = $matched['id'];
$_SESSION['user_name']     = $matched['name'];
$_SESSION['staff_id']      = $matched['staff_id'];
$_SESSION['role']          = $matched['role'];
$_SESSION['last_activity'] = time();

// ── Update last login timestamp ──
$stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
$stmt->execute([$matched['id']]);

// ── Where to send each role ──
$redirectMap = [
    'admin'   => 'admin/dashboard.php',
    'manager' => 'manager/dashboard.php',
    'cashier' => 'cashier/dashboard.php',
    'waiter'  => 'waiter/dashboard.php',
];

echo json_encode([
    'success'  => true,
    'message'  => 'Login successful.',
    'name'     => $matched['name'],
    'role'     => $matched['role'],
    'staff_id' => $matched['staff_id'],
    'redirect' => $redirectMap[$matched['role']],
]);
exit;
?>
