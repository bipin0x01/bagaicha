<?php
require_once dirname(__DIR__) . '/config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['cart_data'])) {
    header("Location: " . url('shop.php'));
    exit;
}

$fname = trim($_POST['fname']);
$lname = trim($_POST['lname']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);
$address = trim($_POST['address']);
$cart_data = json_decode($_POST['cart_data'], true);

if (empty($fname) || empty($lname) || empty($email) || empty($phone) || empty($address) || empty($cart_data)) {
    die("Error: Invalid form submission. Please fill all fields.");
}

// Calculate cart total amount
$total_amount_raw = 0;
foreach ($cart_data as $item) {
    $total_amount_raw += (float)$item['price'] * (int)$item['quantity'];
}
$total_amount = number_format($total_amount_raw, 2, '.', '');

$transaction_uuid = 'cod-' . time() . '-' . rand(1000, 9999);

// 1. Insert order into Database with payment_method as 'cod' and unpaid payment status
$order_stmt = $db->prepare("INSERT INTO orders (transaction_uuid, total_amount, status, payment_status, fname, lname, email, phone, address, payment_method) VALUES (:uuid, :total, 'processing', 'pending', :fname, :lname, :email, :phone, :address, 'cod')");
$order_stmt->bindValue(':uuid', $transaction_uuid, SQLITE3_TEXT);
$order_stmt->bindValue(':total', $total_amount, SQLITE3_FLOAT);
$order_stmt->bindValue(':fname', $fname, SQLITE3_TEXT);
$order_stmt->bindValue(':lname', $lname, SQLITE3_TEXT);
$order_stmt->bindValue(':email', $email, SQLITE3_TEXT);
$order_stmt->bindValue(':phone', $phone, SQLITE3_TEXT);
$order_stmt->bindValue(':address', $address, SQLITE3_TEXT);

$result = $order_stmt->execute();
if (!$result) {
    die("Database Error: Failed to record order details.");
}

$order_id = $db->lastInsertRowID();

// 2. Insert items into order_items table
$item_stmt = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :qty, :price)");
$prod_lookup = $db->prepare("SELECT id FROM products WHERE name = :name");

foreach ($cart_data as $item) {
    $prod_lookup->bindValue(':name', $item['name'], SQLITE3_TEXT);
    $prod_res = $prod_lookup->execute();
    $prod_row = $prod_res->fetchArray(SQLITE3_ASSOC);
    $product_id = $prod_row ? $prod_row['id'] : 1;

    $item_stmt->bindValue(':order_id', $order_id, SQLITE3_INTEGER);
    $item_stmt->bindValue(':product_id', $product_id, SQLITE3_INTEGER);
    $item_stmt->bindValue(':qty', $item['quantity'], SQLITE3_INTEGER);
    $item_stmt->bindValue(':price', $item['price'], SQLITE3_FLOAT);
    $item_stmt->execute();
}

// Redirect to success page for COD order
header('Location: ' . url('success.php?method=cod&uuid=' . urlencode($transaction_uuid)));
exit;
