<?php
// ============================================================
//  ByteSavor — api/auth.php
//
//  This file is included at the top of every API file.
//  It does 3 things:
//  1. Starts the session and checks the user is logged in
//  2. Connects to the correct restaurant database
//     based on the restaurant_id in the session
//  3. Provides helper functions used across all API files
//
//  NEVER call this file directly from a browser.
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Always respond with JSON ──
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// ── Check logged in ──
if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    die(json_encode(['success' => false, 'message' => 'Unauthorised. Please log in.']));
}

// ── Session timeout — 8 hours ──
$timeout = 8 * 60 * 60;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    session_unset();
    session_destroy();
    http_response_code(401);
    die(json_encode(['success' => false, 'message' => 'Session expired. Please log in again.']));
}
$_SESSION['last_activity'] = time();

// ── Get restaurant ID ──
// This comes from the logged-in session.
// Each restaurant client has their own database.
$restaurantId = $_SESSION['restaurant_id'] ?? 1;

// ── Load the main config which holds DB credentials ──
require_once __DIR__ . '/../config/config.php';

// ══════════════════════════════════════════════════
//  getRestaurantDB($restaurantId)
//
//  Returns a PDO connection to the correct
//  restaurant database. Each restaurant client
//  on Afrihost has their own MySQL database.
//
//  In production: store each restaurant's DB
//  credentials in the master database, then
//  fetch and connect dynamically.
//
//  For now: all restaurants use the same DB
//  with restaurant_id as a filter column.
//  When a client needs isolation, swap this
//  function to connect to their own database.
// ══════════════════════════════════════════════════
function getRestaurantDB($restaurantId = 1) {
    // In production with isolated databases:
    // 1. Connect to master DB
    // 2. SELECT db_host, db_name, db_user, db_pass
    //    FROM restaurants WHERE id = $restaurantId
    // 3. Return new PDO connection to that DB
    //
    // For now: single shared DB, restaurant_id filters data
    return getDB();
}

// ══════════════════════════════════════════════════
//  Helper functions
// ══════════════════════════════════════════════════

// Send a JSON success response
function ok($data = [], $message = 'Success') {
    echo json_encode(['success' => true, 'message' => $message, 'data' => $data]);
    exit;
}

// Send a JSON error response
function fail($message = 'An error occurred', $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

// Get current user from session
function me() {
    return [
        'id'       => $_SESSION['user_id']   ?? null,
        'name'     => $_SESSION['user_name'] ?? 'Unknown',
        'staff_id' => $_SESSION['staff_id']  ?? '',
        'role'     => $_SESSION['role']      ?? '',
    ];
}

// Role check — call at top of any endpoint
// Example: requireRole(['admin', 'manager']);
function requireRole(array $allowed) {
    $role = $_SESSION['role'] ?? '';
    if (!in_array($role, $allowed)) {
        fail('Access denied. Your role cannot perform this action.', 403);
    }
}

// Only accept specific HTTP methods
// Example: requireMethod('POST');
function requireMethod(string $method) {
    if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method)) {
        fail('Method not allowed.', 405);
    }
}

// Read JSON body from POST requests that send JSON
function jsonBody() {
    $raw = file_get_contents('php://input');
    return json_decode($raw, true) ?? [];
}

// Log to audit trail
function auditLog($db, $restaurantId, $type, $detail) {
    $user = me();
    try {
        $stmt = $db->prepare("
            INSERT INTO audit_log
                (restaurant_id, event_type, staff_id, staff_name, detail, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $restaurantId,
            $type,
            $user['staff_id'],
            $user['name'],
            $detail,
        ]);
    } catch (Exception $e) {
        // Audit failure should never break the main action
        error_log('Audit log failed: ' . $e->getMessage());
    }
}
?>
