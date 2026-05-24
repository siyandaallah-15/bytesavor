<?php
// ============================================================
//  ByteSavor — migrate_payroll.php
//
//  Run this ONCE after setup.php if you already ran setup.
//  It adds the pay columns to the users table.
//
//  Visit: yourdomain.co.za/migrate_payroll.php
//  DELETE this file after running.
// ============================================================

require_once 'config/config.php';
$db  = getDB();
$log = [];

$columns = [
    'pay_type'       => "ALTER TABLE users ADD COLUMN pay_type ENUM('hourly','salary') NOT NULL DEFAULT 'hourly' AFTER bio_template",
    'hourly_rate'    => "ALTER TABLE users ADD COLUMN hourly_rate DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER pay_type",
    'monthly_salary' => "ALTER TABLE users ADD COLUMN monthly_salary DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER hourly_rate",
];

foreach ($columns as $col => $sql) {
    try {
        $db->exec($sql);
        $log[] = ['ok', "Column '$col' added to users table."];
    } catch (PDOException $e) {
        // Column already exists — not an error
        if (str_contains($e->getMessage(), 'Duplicate column')) {
            $log[] = ['info', "Column '$col' already exists — skipped."];
        } else {
            $log[] = ['warn', "Column '$col' — " . $e->getMessage()];
        }
    }
}

$log[] = ['done', "Payroll migration complete! <strong>DELETE this file now.</strong>"];
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"/><title>ByteSavor — Payroll Migration</title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet"/>
<style>body{font-family:'Outfit',sans-serif;background:#080a0d;color:#eceae4;padding:48px 32px;max-width:600px;margin:0 auto}h1{color:#f5a623;font-size:26px;margin-bottom:20px}.item{padding:11px 16px;border-radius:8px;margin-bottom:9px;font-size:14px;display:flex;align-items:center;gap:10px;border-left:3px solid #1f2430;background:#0f1217}.ok{border-color:#3eb87a}.warn{border-color:#f5a623;color:#f5a623}.info{border-color:#5a6070;color:#5a6070}.done{border-color:#e8520a;color:#e8520a;font-weight:700}</style>
</head><body>
<h1>🍽️ ByteSavor — Payroll Migration</h1>
<?php foreach($log as [$type,$msg]):?>
<div class="item <?=$type?>"><?=$type==='ok'?'✅':($type==='warn'?'⚠️':($type==='done'?'🎉':'ℹ️'))?> <?=$msg?></div>
<?php endforeach;?>
</body></html>
