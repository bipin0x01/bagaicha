<?php
/**
 * Bagaicha - eSewa Redirect Page
 * Persists order in SQLite, calculates HMAC signature, and auto-submits form to eSewa epay sandbox.
 */
require_once 'db.php';

// Prevent direct access without checkout details
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['cart_data'])) {
    header("Location: shop.php");
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
// Format to exactly two decimal places as required by eSewa ePay v2 signature
$total_amount = number_format($total_amount_raw, 2, '.', '');

// Generate unique transaction UUID
$transaction_uuid = 'bagaicha-' . time() . '-' . rand(1000, 9999);

// 1. Insert order into Database
$order_stmt = $db->prepare("INSERT INTO orders (transaction_uuid, total_amount, status, fname, lname, email, phone, address) VALUES (:uuid, :total, 'pending', :fname, :lname, :email, :phone, :address)");
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

// Retrieve the newly created order ID
$order_id = $db->lastInsertRowID();

// 2. Insert items into order_items table
$item_stmt = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :qty, :price)");
$prod_lookup = $db->prepare("SELECT id FROM products WHERE name = :name");

foreach ($cart_data as $item) {
    // Look up real product ID by name
    $prod_lookup->bindValue(':name', $item['name'], SQLITE3_TEXT);
    $prod_res = $prod_lookup->execute();
    $prod_row = $prod_res->fetchArray(SQLITE3_ASSOC);
    $product_id = $prod_row ? $prod_row['id'] : 1; // Default fallback to ID 1 if not matched

    $item_stmt->bindValue(':order_id', $order_id, SQLITE3_INTEGER);
    $item_stmt->bindValue(':product_id', $product_id, SQLITE3_INTEGER);
    $item_stmt->bindValue(':qty', $item['quantity'], SQLITE3_INTEGER);
    $item_stmt->bindValue(':price', $item['price'], SQLITE3_FLOAT);
    $item_stmt->execute();
}

// 3. Compute eSewa ePay v2 Signature
// Message format: total_amount=X,transaction_uuid=Y,product_code=EPAYTEST
$product_code = "EPAYTEST"; // Official Sandbox Merchant Code
$secret_key = "8gBm/:&EnhH.1/q"; // Official Sandbox Secret Key

$signing_string = "total_amount={$total_amount},transaction_uuid={$transaction_uuid},product_code={$product_code}";
$hmac_hash = hash_hmac('sha256', $signing_string, $secret_key, true);
$signature = base64_encode($hmac_hash);

// Determine dynamic return URLs
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$base_dir = rtrim(dirname($_SERVER['REQUEST_URI']), '/\\');
$success_url = "{$protocol}://{$host}{$base_dir}/success.php";
$failure_url = "{$protocol}://{$host}{$base_dir}/failure.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Connecting to eSewa...</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f8f8;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .loader-card {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            text-align: center;
            max-width: 400px;
        }

        .spinner {
            border: 4px solid rgba(104, 45, 145, 0.1);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border-left-color: #682d91;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>

<body>
    <div class="loader-card">
        <div class="spinner"></div>
        <h3 style="color: #333; margin-bottom: 10px;">Connecting to eSewa Payment Gateway</h3>
        <p style="color: #666; font-size: 14px;">Please do not refresh or close this window. Redirecting securely...</p>

        <!-- eSewa ePay v2 Form submission -->
        <form id="esewa-payment-form" action="https://rc-epay.esewa.com.np/api/epay/main/v2/form" method="POST" style="display: none;">
            <input type="text" id="amount" name="amount" value="<?php echo $total_amount; ?>" required>
            <input type="text" id="tax_amount" name="tax_amount" value="0" required>
            <input type="text" id="total_amount" name="total_amount" value="<?php echo $total_amount; ?>" required>
            <input type="text" id="transaction_uuid" name="transaction_uuid" value="<?php echo $transaction_uuid; ?>" required>
            <input type="text" id="product_code" name="product_code" value="<?php echo $product_code; ?>" required>
            <input type="text" id="product_service_charge" name="product_service_charge" value="0" required>
            <input type="text" id="product_delivery_charge" name="product_delivery_charge" value="0" required>
            <input type="text" id="success_url" name="success_url" value="<?php echo $success_url; ?>" required>
            <input type="text" id="failure_url" name="failure_url" value="<?php echo $failure_url; ?>" required>
            <input type="text" id="signed_field_names" name="signed_field_names" value="total_amount,transaction_uuid,product_code" required>
            <input type="text" id="signature" name="signature" value="<?php echo $signature; ?>" required>
        </form>
    </div>

    <script>
        // Automatic form post submission
        document.addEventListener("DOMContentLoaded", () => {
            setTimeout(() => {
                document.getElementById("esewa-payment-form").submit();
            }, 1500); // Small delay to show transition beautifully
        });
    </script>
</body>

</html>
