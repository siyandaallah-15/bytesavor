<?php
// ============================================================
//  ByteSavor — logout.php
//  Clears the session completely and sends the user
//  back to the login page.
//
//  Link to this from any dashboard page:
//  <a href="../logout.php">Sign Out</a>
// ============================================================

session_start();
session_unset();
session_destroy();

// Clear the session cookie properly
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

header('Location: index.html');
exit;
?>
