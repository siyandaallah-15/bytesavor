<?php
// ============================================================
//  ByteSavor — api/menu.php
//
//  GET  ?action=categories          → all categories
//  GET  ?action=items&cat_id=X      → items by category
//  GET  ?action=all                 → all categories with items
//  POST action=add_category         → add a category
//  POST action=add_item             → add a menu item
//  POST action=edit_item            → edit a menu item
//  POST action=toggle_item          → toggle availability
//  POST action=delete_item          → delete a menu item
//  POST action=import_csv           → bulk import items
// ============================================================

require_once __DIR__ . '/auth.php';

$db     = getRestaurantDB($restaurantId);
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {

    // ════════════════════════════════════════
    //  GET: All categories
    // ════════════════════════════════════════
    case 'categories':
        requireMethod('GET');
        $stmt = $db->prepare("
            SELECT mc.*, COUNT(mi.id) AS item_count
            FROM menu_categories mc
            LEFT JOIN menu_items mi ON mi.category_id = mc.id AND mi.restaurant_id = mc.restaurant_id
            WHERE mc.restaurant_id = ?
            GROUP BY mc.id
            ORDER BY mc.sort_order ASC, mc.name ASC
        ");
        $stmt->execute([$restaurantId]);
        ok($stmt->fetchAll());
        break;

    // ════════════════════════════════════════
    //  GET: Items by category
    // ════════════════════════════════════════
    case 'items':
        requireMethod('GET');
        $catId     = intval($_GET['cat_id']    ?? 0);
        $available = $_GET['available_only']   ?? null;

        $where  = ['mi.restaurant_id = ?'];
        $params = [$restaurantId];
        if ($catId) { $where[] = 'mi.category_id = ?'; $params[] = $catId; }
        if ($available === '1') { $where[] = 'mi.is_available = 1'; }

        $stmt = $db->prepare("
            SELECT mi.*, mc.name AS category_name, mc.emoji AS category_emoji
            FROM menu_items mi
            JOIN menu_categories mc ON mi.category_id = mc.id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY mc.sort_order ASC, mi.name ASC
        ");
        $stmt->execute($params);
        ok($stmt->fetchAll());
        break;

    // ════════════════════════════════════════
    //  GET: All categories with nested items
    //  Used by waiter POS screen on load
    // ════════════════════════════════════════
    case 'all':
        requireMethod('GET');

        $catStmt = $db->prepare("
            SELECT * FROM menu_categories
            WHERE restaurant_id = ? AND is_active = 1
            ORDER BY sort_order ASC, name ASC
        ");
        $catStmt->execute([$restaurantId]);
        $categories = $catStmt->fetchAll();

        $itemStmt = $db->prepare("
            SELECT * FROM menu_items
            WHERE restaurant_id = ? AND is_available = 1
            ORDER BY name ASC
        ");
        $itemStmt->execute([$restaurantId]);
        $allItems = $itemStmt->fetchAll();

        // Nest items into their category
        $itemsByCat = [];
        foreach ($allItems as $item) {
            $itemsByCat[$item['category_id']][] = $item;
        }
        foreach ($categories as &$cat) {
            $cat['items'] = $itemsByCat[$cat['id']] ?? [];
        }

        ok($categories);
        break;

    // ════════════════════════════════════════
    //  POST: Add category
    // ════════════════════════════════════════
    case 'add_category':
        requireMethod('POST');
        requireRole(['admin', 'manager']);

        $body  = jsonBody();
        $name  = trim($body['name']  ?? '');
        $emoji = trim($body['emoji'] ?? '🍽️');
        if (!$name) fail('Category name is required.');

        $stmt = $db->prepare("
            INSERT INTO menu_categories (restaurant_id, name, emoji, sort_order)
            VALUES (?, ?, ?, (SELECT IFNULL(MAX(sort_order),0)+1 FROM menu_categories mc2 WHERE mc2.restaurant_id = ?))
        ");
        $stmt->execute([$restaurantId, $name, $emoji, $restaurantId]);
        $id = $db->lastInsertId();

        auditLog($db, $restaurantId, 'menu', "Category added: $name");
        ok(['id' => $id, 'name' => $name], 'Category added.');
        break;

    // ════════════════════════════════════════
    //  POST: Add menu item
    // ════════════════════════════════════════
    case 'add_item':
        requireMethod('POST');
        requireRole(['admin', 'manager']);

        $body     = jsonBody();
        $name     = trim($body['name']        ?? '');
        $catId    = intval($body['category_id'] ?? 0);
        $price    = floatval($body['price']   ?? 0);
        $desc     = trim($body['description'] ?? '');
        $emoji    = trim($body['emoji']       ?? '🍽️');
        $imgUrl   = trim($body['image_url']   ?? '');
        $avail    = isset($body['is_available']) ? (int)$body['is_available'] : 1;

        if (!$name)  fail('Item name is required.');
        if (!$catId) fail('Category is required.');
        if ($price <= 0) fail('A valid price is required.');

        $stmt = $db->prepare("
            INSERT INTO menu_items
                (restaurant_id, category_id, name, description, price, emoji, image_url, is_available)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$restaurantId, $catId, $name, $desc, $price, $emoji, $imgUrl, $avail]);
        $id = $db->lastInsertId();

        auditLog($db, $restaurantId, 'menu', "Item added: $name — R" . number_format($price, 2));
        ok(['id' => $id], "$name added to menu.");
        break;

    // ════════════════════════════════════════
    //  POST: Edit menu item
    // ════════════════════════════════════════
    case 'edit_item':
        requireMethod('POST');
        requireRole(['admin', 'manager']);

        $body  = jsonBody();
        $id    = intval($body['id'] ?? 0);
        if (!$id) fail('Item ID required.');

        $fields = [];
        $params = [];
        $allowed = ['name','description','price','emoji','image_url','is_available','category_id'];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $body)) {
                $fields[] = "$f = ?";
                $params[] = $f === 'price' ? floatval($body[$f]) : ($f === 'is_available' ? intval($body[$f]) : $body[$f]);
            }
        }
        if (empty($fields)) fail('Nothing to update.');

        $params[] = $id;
        $params[] = $restaurantId;
        $db->prepare("UPDATE menu_items SET " . implode(', ', $fields) . " WHERE id = ? AND restaurant_id = ?")
           ->execute($params);

        auditLog($db, $restaurantId, 'menu', "Item #$id updated");
        ok(['id' => $id], 'Item updated.');
        break;

    // ════════════════════════════════════════
    //  POST: Toggle item availability
    // ════════════════════════════════════════
    case 'toggle_item':
        requireMethod('POST');
        requireRole(['admin', 'manager', 'waiter']);

        $body = jsonBody();
        $id   = intval($body['id'] ?? 0);
        if (!$id) fail('Item ID required.');

        $db->prepare("
            UPDATE menu_items SET is_available = NOT is_available WHERE id = ? AND restaurant_id = ?
        ")->execute([$id, $restaurantId]);

        $item = $db->prepare("SELECT name, is_available FROM menu_items WHERE id = ?");
        $item->execute([$id]);
        $item = $item->fetch();

        auditLog($db, $restaurantId, 'menu', "Item '{$item['name']}' " . ($item['is_available'] ? 'shown on' : 'hidden from') . " POS");
        ok(['id' => $id, 'is_available' => $item['is_available']], 'Item availability updated.');
        break;

    // ════════════════════════════════════════
    //  POST: Delete menu item
    // ════════════════════════════════════════
    case 'delete_item':
        requireMethod('POST');
        requireRole(['admin']);

        $body = jsonBody();
        $id   = intval($body['id'] ?? 0);
        if (!$id) fail('Item ID required.');

        $item = $db->prepare("SELECT name FROM menu_items WHERE id = ? AND restaurant_id = ?");
        $item->execute([$id, $restaurantId]);
        $item = $item->fetch();
        if (!$item) fail('Item not found.', 404);

        $db->prepare("DELETE FROM menu_items WHERE id = ? AND restaurant_id = ?")->execute([$id, $restaurantId]);

        auditLog($db, $restaurantId, 'menu', "Item deleted: {$item['name']}");
        ok([], "'{$item['name']}' deleted from menu.");
        break;

    // ════════════════════════════════════════
    //  POST: Import items from CSV data
    //  Frontend sends parsed CSV rows as JSON
    // ════════════════════════════════════════
    case 'import_csv':
        requireMethod('POST');
        requireRole(['admin', 'manager']);

        $body = jsonBody();
        $rows = $body['rows'] ?? [];
        if (empty($rows)) fail('No rows to import.');

        $added  = 0;
        $errors = [];

        $db->beginTransaction();
        try {
            foreach ($rows as $i => $row) {
                [$catName, $name, $desc, $price, $emoji, $avail] = array_pad($row, 6, '');
                $catName = trim($catName);
                $name    = trim($name);
                $price   = floatval($price);

                if (!$catName || !$name || $price <= 0) {
                    $errors[] = "Row " . ($i+1) . " skipped — missing name, category or price.";
                    continue;
                }

                // Get or create category
                $catStmt = $db->prepare("SELECT id FROM menu_categories WHERE restaurant_id = ? AND name = ?");
                $catStmt->execute([$restaurantId, $catName]);
                $cat = $catStmt->fetch();

                if (!$cat) {
                    $db->prepare("INSERT INTO menu_categories (restaurant_id, name, emoji) VALUES (?,?,?)")
                       ->execute([$restaurantId, $catName, '🍽️']);
                    $catId = $db->lastInsertId();
                } else {
                    $catId = $cat['id'];
                }

                $db->prepare("
                    INSERT INTO menu_items (restaurant_id, category_id, name, description, price, emoji, is_available)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ")->execute([$restaurantId, $catId, $name, $desc, $price, $emoji ?: '🍽️', $avail !== '0' ? 1 : 0]);
                $added++;
            }
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            fail('Import failed: ' . $e->getMessage());
        }

        auditLog($db, $restaurantId, 'menu', "$added items imported via CSV");
        ok(['added' => $added, 'errors' => $errors], "$added items imported successfully.");
        break;

    default:
        fail('Unknown action: ' . htmlspecialchars($action));
}
?>
