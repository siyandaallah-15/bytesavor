<?php
// ============================================================
//  ByteSavor — config/session_check.php
//
//  Paste this ONE line at the very top of every
//  protected page (waiter, cashier, manager, admin dashboards):
//
//     <?php require_once '../config/session_check.php'; ?>
//
//  It will:
//  - Check the user is logged in
//  - Send them back to login if they are not
//  - Let you restrict pages by role using allowRoles()
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Is the user logged in? ──
if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../index.html');
    exit;
}

// ── Session timeout — 8 hours (a full restaurant shift) ──
$timeout = 8 * 60 * 60;
if (isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > $timeout) {
        session_unset();
        session_destroy();
        header('Location: ../index.html?msg=Session expired. Please sign in again.');
        exit;
    }
}
$_SESSION['last_activity'] = time();

// ── Role protection ──
// Usage: allowRoles(['admin', 'manager']);
// Call this on any page to restrict who can access it.
// Example: a waiter who tries to access the admin page
// gets sent back to their own dashboard automatically.
function allowRoles(array $allowed) {
    $userRole = $_SESSION['role'] ?? '';
    if (!in_array($userRole, $allowed)) {
        $map = [
            'admin'   => '../admin/dashboard.php',
            'manager' => '../manager/dashboard.php',
            'cashier' => '../cashier/dashboard.php',
            'waiter'  => '../waiter/dashboard.php',
        ];
        header('Location: ' . ($map[$userRole] ?? '../index.html'));
        exit;
    }
}

// ── Helper: get the current logged-in user's info ──
// Use this anywhere: $user = currentUser();
function currentUser() {
    return [
        'id'       => $_SESSION['user_id']   ?? null,
        'name'     => $_SESSION['user_name'] ?? 'Staff',
        'staff_id' => $_SESSION['staff_id']  ?? '',
        'role'     => $_SESSION['role']      ?? '',
    ];
}
?>
