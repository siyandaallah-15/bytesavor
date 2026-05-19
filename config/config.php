<?php
// ============================================================
//  ByteSavor — config/config.php
//  Database connection file.
//
//  INSTRUCTIONS:
//  1. In cPanel → MySQL Databases:
//     - Create a database e.g. mysite_bytesavor
//     - Create a user e.g. mysite_bsuser
//     - Set a strong password
//     - Add the user to the database with ALL PRIVILEGES
//  2. Fill in your details in the 4 defines below
//  3. Never share or upload this file publicly
// ============================================================

define('DB_HOST',    'localhost');         // Always localhost on Afrihost cPanel
define('DB_NAME',    'your_db_name');      // e.g. mysite_bytesavor
define('DB_USER',    'your_db_user');      // e.g. mysite_bsuser
define('DB_PASS',    'your_db_password');  // The password you set in cPanel
define('DB_CHARSET', 'utf8mb4');

// ── Connect to the database ──
// We use PDO — it's the safest and most modern way to talk to MySQL in PHP.
// This function is called from login.php and any other file that needs the DB.
function getDB() {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST
             . ";dbname=" . DB_NAME
             . ";charset=" . DB_CHARSET;

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // Throw errors
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // Return arrays
            PDO::ATTR_EMULATE_PREPARES   => false,                    // Real prepared statements
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log the real error privately, show a safe message publicly
            error_log("ByteSavor DB Error: " . $e->getMessage());
            http_response_code(500);
            die(json_encode([
                'success' => false,
                'message' => 'Database connection failed. Please contact support.'
            ]));
        }
    }

    return $pdo;
}
?>
