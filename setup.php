<?php
// ============================================================
//  ByteSavor — setup.php
//
//  INSTRUCTIONS — run this ONCE only:
//  1. Fill in your admin details below
//  2. Upload to your Afrihost server
//  3. Visit: yourdomain.co.za/setup.php
//  4. !! DELETE this file immediately after running !!
//
//  This creates all database tables and your first admin user.
// ============================================================

require_once 'config/config.php';
$db  = getDB();
$log = [];

// ════════════════════════════════
//  1. USERS TABLE
//  Stores all staff: waiters, cashiers, managers, admins
// ════════════════════════════════
$db->exec("
    CREATE TABLE IF NOT EXISTS users (
        id           INT AUTO_INCREMENT PRIMARY KEY,
        name         VARCHAR(100)  NOT NULL,
        staff_id     VARCHAR(10)   NOT NULL UNIQUE,  -- e.g. W001, C002, M001, A001
        pin_hash     VARCHAR(255)  NOT NULL,          -- 4-digit PIN stored as bcrypt hash
        role         ENUM('admin','manager','cashier','waiter') NOT NULL DEFAULT 'waiter',
        restaurant_id INT          DEFAULT 1,
        is_active    TINYINT(1)    NOT NULL DEFAULT 1,
        created_at   TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
        last_login   TIMESTAMP     NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
$log[] = ['ok', "Table 'users' ready."];

// ════════════════════════════════
//  2. RESTAURANTS TABLE
//  Each restaurant client gets their own row.
//  Later: each gets their own database too.
// ════════════════════════════════
$db->exec("
    CREATE TABLE IF NOT EXISTS restaurants (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        name       VARCHAR(150)  NOT NULL,
        address    VARCHAR(255)  DEFAULT NULL,
        phone      VARCHAR(30)   DEFAULT NULL,
        email      VARCHAR(150)  DEFAULT NULL,
        logo_url   VARCHAR(255)  DEFAULT NULL,
        is_active  TINYINT(1)    NOT NULL DEFAULT 1,
        created_at TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
$log[] = ['ok', "Table 'restaurants' ready."];

// ════════════════════════════════
//  3. TABLES TABLE
//  Physical tables in the restaurant
// ════════════════════════════════
$db->exec("
    CREATE TABLE IF NOT EXISTS tables_layout (
        id            INT AUTO_INCREMENT PRIMARY KEY,
        restaurant_id INT          NOT NULL DEFAULT 1,
        table_number  VARCHAR(10)  NOT NULL,  -- e.g. T1, T2, VIP1
        capacity      INT          NOT NULL DEFAULT 4,
        status        ENUM('available','occupied','reserved','cleaning') DEFAULT 'available',
        created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
$log[] = ['ok', "Table 'tables_layout' ready."];

// ════════════════════════════════
//  4. MENU CATEGORIES TABLE
// ════════════════════════════════
$db->exec("
    CREATE TABLE IF NOT EXISTS menu_categories (
        id            INT AUTO_INCREMENT PRIMARY KEY,
        restaurant_id INT          NOT NULL DEFAULT 1,
        name          VARCHAR(80)  NOT NULL,  -- e.g. Starters, Mains, Drinks
        sort_order    INT          DEFAULT 0,
        is_active     TINYINT(1)   NOT NULL DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
$log[] = ['ok', "Table 'menu_categories' ready."];

// ════════════════════════════════
//  5. MENU ITEMS TABLE
// ════════════════════════════════
$db->exec("
    CREATE TABLE IF NOT EXISTS menu_items (
        id            INT AUTO_INCREMENT PRIMARY KEY,
        restaurant_id INT            NOT NULL DEFAULT 1,
        category_id   INT            NOT NULL,
        name          VARCHAR(120)   NOT NULL,
        description   TEXT           DEFAULT NULL,
        price         DECIMAL(10,2)  NOT NULL,
        image_url     VARCHAR(255)   DEFAULT NULL,
        is_available  TINYINT(1)     NOT NULL DEFAULT 1,
        created_at    TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES menu_categories(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
$log[] = ['ok', "Table 'menu_items' ready."];

// ════════════════════════════════
//  6. ORDERS TABLE
// ════════════════════════════════
$db->exec("
    CREATE TABLE IF NOT EXISTS orders (
        id            INT AUTO_INCREMENT PRIMARY KEY,
        restaurant_id INT          NOT NULL DEFAULT 1,
        table_id      INT          DEFAULT NULL,
        waiter_id     INT          DEFAULT NULL,
        order_type    ENUM('dine_in','takeaway','online') DEFAULT 'dine_in',
        status        ENUM('open','sent_to_kitchen','ready','paid','cancelled') DEFAULT 'open',
        total         DECIMAL(10,2) DEFAULT 0.00,
        notes         TEXT          DEFAULT NULL,
        created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
        updated_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
$log[] = ['ok', "Table 'orders' ready."];

// ════════════════════════════════
//  7. ORDER ITEMS TABLE
//  Each line item inside an order
// ════════════════════════════════
$db->exec("
    CREATE TABLE IF NOT EXISTS order_items (
        id           INT AUTO_INCREMENT PRIMARY KEY,
        order_id     INT            NOT NULL,
        menu_item_id INT            NOT NULL,
        quantity     INT            NOT NULL DEFAULT 1,
        unit_price   DECIMAL(10,2)  NOT NULL,
        notes        VARCHAR(255)   DEFAULT NULL,  -- e.g. 'no onions'
        FOREIGN KEY (order_id)     REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (menu_item_id) REFERENCES menu_items(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
$log[] = ['ok', "Table 'order_items' ready."];

// ════════════════════════════════
//  8. PAYMENTS TABLE
// ════════════════════════════════
$db->exec("
    CREATE TABLE IF NOT EXISTS payments (
        id             INT AUTO_INCREMENT PRIMARY KEY,
        order_id       INT            NOT NULL,
        amount         DECIMAL(10,2)  NOT NULL,
        method         ENUM('cash','card','contactless','online') NOT NULL,
        status         ENUM('pending','completed','failed','refunded') DEFAULT 'pending',
        reference      VARCHAR(100)   DEFAULT NULL,  -- Card/EFT reference number
        created_at     TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
$log[] = ['ok', "Table 'payments' ready."];

// ════════════════════════════════
//  CREATE DEFAULT RESTAURANT
// ════════════════════════════════
$r = $db->query("SELECT COUNT(*) as c FROM restaurants")->fetch();
if ($r['c'] == 0) {
    $db->exec("
        INSERT INTO restaurants (name, address, phone, email)
        VALUES ('My Restaurant', '1 Main Street, South Africa', '010 000 0000', 'owner@myrestaurant.co.za')
    ");
    $log[] = ['ok', "Default restaurant created."];
} else {
    $log[] = ['info', "Restaurant already exists — skipped."];
}

// ════════════════════════════════
//  CREATE FIRST ADMIN USER
//  !! CHANGE THESE DETAILS BEFORE RUNNING !!
// ════════════════════════════════
$adminName    = 'System Admin';   // ← Change to your name
$adminStaffId = 'A001';           // ← Admin staff ID
$adminPin     = '1234';           // ← Change this to your PIN before running!

$exists = $db->prepare("SELECT COUNT(*) as c FROM users WHERE staff_id = ?");
$exists->execute([$adminStaffId]);
if ($exists->fetch()['c'] == 0) {
    $hashed = password_hash($adminPin, PASSWORD_BCRYPT);
    $stmt   = $db->prepare("
        INSERT INTO users (name, staff_id, pin_hash, role, restaurant_id)
        VALUES (?, ?, ?, 'admin', 1)
    ");
    $stmt->execute([$adminName, $adminStaffId, $hashed]);
    $log[] = ['ok',  "Admin user created — Staff ID: <strong>$adminStaffId</strong> / PIN: <strong>$adminPin</strong>"];
    $log[] = ['warn', "Change your PIN after first login!"];
} else {
    $log[] = ['info', "Admin user already exists — skipped."];
}

$log[] = ['done', "Setup complete! <strong>DELETE this file from your server now.</strong>"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <title>ByteSavor Setup</title>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet"/>
  <style>
    body  { font-family:'Outfit',sans-serif; background:#080a0d; color:#eceae4; padding:48px 32px; max-width:640px; margin:0 auto; }
    h1    { color:#f5a623; font-size:28px; margin-bottom:8px; }
    p.sub { color:#5a6070; font-size:14px; margin-bottom:32px; }
    .item {
      padding:12px 16px; border-radius:8px; margin-bottom:10px;
      font-size:14px; display:flex; align-items:center; gap:10px;
      border-left:3px solid #1f2430; background:#0f1217;
    }
    .ok   { border-color:#3eb87a; }
    .warn { border-color:#f5a623; color:#f5a623; }
    .info { border-color:#5a6070; color:#5a6070; }
    .done { border-color:#e8520a; color:#e8520a; font-weight:600; font-size:15px; }
  </style>
</head>
<body>
  <h1>🍽️ ByteSavor — Setup</h1>
  <p class="sub">Creating your database tables and first admin account.</p>
  <?php foreach ($log as [$type, $msg]): ?>
    <div class="item <?= $type ?>">
      <?= $type==='ok'?'✅':($type==='warn'?'⚠️':($type==='done'?'🎉':'ℹ️')) ?>
      <?= $msg ?>
    </div>
  <?php endforeach; ?>
</body>
</html>
