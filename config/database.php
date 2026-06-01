<?php
/**
 * SQLite database connection, schema setup, and seed data.
 */

$db_file = STORAGE_PATH . '/database/bagaicha.db';

if (!file_exists(dirname($db_file))) {
    mkdir(dirname($db_file), 0777, true);
}

try {
    $db = new SQLite3($db_file);
} catch (Exception $e) {
    die('Database Connection failed: ' . $e->getMessage());
}

$db->exec("CREATE TABLE IF NOT EXISTS products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    price REAL NOT NULL,
    discount_price REAL,
    image_url TEXT NOT NULL,
    description TEXT
)");

$db->exec("CREATE TABLE IF NOT EXISTS orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    transaction_uuid TEXT UNIQUE NOT NULL,
    total_amount REAL NOT NULL,
    status TEXT NOT NULL DEFAULT 'pending',
    fname TEXT NOT NULL,
    lname TEXT NOT NULL,
    email TEXT NOT NULL,
    phone TEXT NOT NULL,
    address TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$db->exec("CREATE TABLE IF NOT EXISTS order_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    quantity INTEGER NOT NULL,
    price REAL NOT NULL,
    FOREIGN KEY(order_id) REFERENCES orders(id),
    FOREIGN KEY(product_id) REFERENCES products(id)
)");

$schema_check = $db->query('PRAGMA table_info(users)');
$has_phone_column = false;
if ($schema_check) {
    while ($col = $schema_check->fetchArray(SQLITE3_ASSOC)) {
        if ($col['name'] === 'phone') {
            $has_phone_column = true;
            break;
        }
    }
}
if (!$has_phone_column) {
    $db->exec('DROP TABLE IF EXISTS users');
}

$db->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    fname TEXT NOT NULL,
    lname TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    phone TEXT NOT NULL,
    address TEXT NOT NULL,
    password TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$product_count = $db->querySingle('SELECT COUNT(*) FROM products');

if ($product_count === 0) {
    $products = [
        ['Chinese Privet Bonsai', 5000, 6200, '/assets/img/products/chinese-privet-bonsai.jpg', 'Beautiful indoor Chinese Privet Bonsai tree.'],
        ['Crab Apple Bonsai Tree', 8000, 9100, '/assets/img/products/Crab-apple-bonsai-tree-Malus-bonsai-tree.jpg', 'Stunning blooming Crab Apple Bonsai Tree.'],
        ['Dawn Redwood Bonsai', 2500, 3400, '/assets/img/products/dawn-redwood-bonsai.jpg', 'Vigorous deciduous Dawn Redwood Bonsai.'],
        ['Japanese Maple Bonsai', 2900, 3400, '/assets/img/products/japanese-maple.jpg', 'Colorful Japanese Maple Bonsai Tree.'],
        ['Beech Bonsai', 3400, 4400, '/assets/img/products/beech-bonsai.png', 'Graceful structure Beech Bonsai.'],
        ['Juniper Bonsai', 1000, 1400, '/assets/img/products/juniper-bonsai.jpg', 'Classic, rugged evergreen Juniper Bonsai.'],
        ['Carmona Bonsai', 9000, 10000, '/assets/img/products/carmona-bonsai.jpg', 'Flowering Fukien Tea Carmona Bonsai.'],
        ['Azalea Bonsai Tree', 7000, 9000, '/assets/img/products/azalea-bonsai-tree.jpg', 'Flowering Azalea Bonsai Tree.'],
        ['Bald Cypress', 4000, 5100, '/assets/img/products/bald-cypress.jpg', 'Formal upright Bald Cypress Bonsai.'],
        ['Chinese Elm Bonsai', 3000, 4400, '/assets/img/products/chinese-elm-bonsai.jpg', 'Tough and resilient Chinese Elm Bonsai.'],
        ['Ficus Ginseng', 7900, 8400, '/assets/img/products/ficus-ginseng.jpg', 'Spectacular thick-trunked Ficus Ginseng.'],
        ['Olive Bonsai', 7950, 8120, '/assets/img/products/olive-bonsai.jpg', 'Traditional Mediterranean Olive Bonsai.'],
    ];

    $stmt = $db->prepare('INSERT INTO products (name, price, discount_price, image_url, description) VALUES (:name, :price, :discount, :img, :desc)');
    foreach ($products as $p) {
        $stmt->bindValue(':name', $p[0], SQLITE3_TEXT);
        $stmt->bindValue(':price', $p[1], SQLITE3_FLOAT);
        $stmt->bindValue(':discount', $p[2], SQLITE3_FLOAT);
        $stmt->bindValue(':img', $p[3], SQLITE3_TEXT);
        $stmt->bindValue(':desc', $p[4], SQLITE3_TEXT);
        $stmt->execute();
    }
}

$orders_schema_check = $db->query('PRAGMA table_info(orders)');
$has_payment_method = false;
$has_payment_status = false;
if ($orders_schema_check) {
    while ($col = $orders_schema_check->fetchArray(SQLITE3_ASSOC)) {
        if ($col['name'] === 'payment_method') {
            $has_payment_method = true;
        }
        if ($col['name'] === 'payment_status') {
            $has_payment_status = true;
        }
    }
}
if (!$has_payment_method) {
    $db->exec("ALTER TABLE orders ADD COLUMN payment_method TEXT DEFAULT 'esewa'");
}
if (!$has_payment_status) {
    $db->exec("ALTER TABLE orders ADD COLUMN payment_status TEXT DEFAULT 'pending'");
}

// Backfill payment_status for existing rows
$db->exec("
    UPDATE orders
    SET payment_status = CASE
        WHEN payment_method = 'esewa' AND status = 'completed' THEN 'paid'
        WHEN payment_method = 'esewa' AND status IN ('failed', 'cancelled') THEN 'failed'
        WHEN payment_method = 'cod' AND status = 'completed' THEN 'paid'
        WHEN payment_method = 'cod' AND status = 'cancelled' THEN 'cancelled'
        ELSE COALESCE(payment_status, 'pending')
    END
    WHERE payment_status IS NULL OR payment_status = ''
");

$admin_check = $db->querySingle("SELECT COUNT(*) FROM users WHERE email = 'admin@bagaicha.com'");
if ($admin_check === 0) {
    $admin_pwd = password_hash('admin123', PASSWORD_DEFAULT);
    $admin_stmt = $db->prepare("INSERT INTO users (fname, lname, email, phone, address, password) VALUES ('Admin', 'Bagaicha', 'admin@bagaicha.com', '9800000000', 'Kathmandu', :pwd)");
    $admin_stmt->bindValue(':pwd', $admin_pwd, SQLITE3_TEXT);
    $admin_stmt->execute();
}
