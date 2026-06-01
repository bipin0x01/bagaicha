<?php
/**
 * Bagaicha - Payment Success Handler
 * Decodes the base64 GET data from eSewa, performs HMAC signature verification,
 * marks order as completed in SQLite, and displays success receipt.
 */
require_once dirname(__DIR__) . '/config/bootstrap.php';
require_once ROOT_PATH . '/lib/esewa.php';

$payment_verified = false;
$order_details = null;
$error_message = "";

if (isset($_GET['method']) && $_GET['method'] === 'cod' && isset($_GET['uuid'])) {
    $transaction_uuid = $_GET['uuid'];
    
    // Check if order exists in Database
    $stmt = $db->prepare("SELECT * FROM orders WHERE transaction_uuid = :uuid AND payment_method = 'cod'");
    $stmt->bindValue(':uuid', $transaction_uuid, SQLITE3_TEXT);
    $res = $stmt->execute();
    $order_details = $res->fetchArray(SQLITE3_ASSOC);
    
    if ($order_details) {
        $payment_verified = true;
    } else {
        $error_message = "Error: Cash on Delivery order record not found.";
    }
} elseif (isset($_GET['data'])) {
    $encoded_data = $_GET['data'];
    
    // Decode base64 JSON payload
    $decoded_json = base64_decode($encoded_data);
    $response = json_decode($decoded_json, true);

    if ($response && isset($response['status']) && $response['status'] === 'COMPLETE') {
        $transaction_uuid = $response['transaction_uuid'];

        // eSewa callback signatures use signed_field_names from the response payload,
        // not the same three fields used when initiating payment.
        $secret_key = "8gBm/:&EnhH.1/q"; // Sandbox Secret Key

        if (esewa_verify_callback_signature($response, $secret_key, $decoded_json)) {
            
            // Check if order exists in Database
            $stmt = $db->prepare("SELECT * FROM orders WHERE transaction_uuid = :uuid");
            $stmt->bindValue(':uuid', $transaction_uuid, SQLITE3_TEXT);
            $res = $stmt->execute();
            $order_details = $res->fetchArray(SQLITE3_ASSOC);

            if ($order_details) {
                if ($order_details['status'] === 'pending') {
                    // Update Order Status to completed
                    $update = $db->prepare("UPDATE orders SET status = 'completed' WHERE id = :id");
                    $update->bindValue(':id', $order_details['id'], SQLITE3_INTEGER);
                    $update->execute();
                    $order_details['status'] = 'completed'; // Reflect local update
                }
                $payment_verified = true;
            } else {
                $error_message = "Error: Order record not found in system database.";
            }
        } else {
            $error_message = "Security Warning: Cryptographic signature mismatch. Transaction untrusted.";
        }
    } else {
        $error_message = "Payment Error: eSewa transaction returned incomplete status.";
    }
} else {
    $error_message = "Invalid Request: No payment credentials received.";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php 
    $page_title = $payment_verified ? "Order Placed Successfully | Bagaicha" : "Verification Failed | Bagaicha";
    $page_description = "View your Bagaicha purchase receipt details, delivery status, and order reference numbers.";
    require INCLUDES_PATH . '/partials/head.php'; 
    ?>
    <?php if ($payment_verified): ?>
    <!-- Clear cart localStorage dynamically upon successful checkout confirmation -->
    <script>
        localStorage.removeItem("bagaicha_cart");
    </script>
    <?php endif; ?>
</head>

<body class="bg-gray-50/30 text-gray-800 antialiased font-sans">
    <!-- Header -->
    <?php require INCLUDES_PATH . '/header.php'; ?>

    <!-- Success Card Container -->
    <main class="min-h-[70vh] flex items-center justify-center py-16 px-6 md:px-12 bg-gray-50/30">
        <div class="bg-white rounded-3xl border border-gray-100 shadow-xl w-full max-w-xl p-8 md:p-10 text-center animate-fade-in">
            <?php if ($payment_verified && $order_details): ?>
                <!-- Green success icon -->
                <div class="w-16 h-16 rounded-full bg-emerald-50 border-2 border-emerald-500 flex items-center justify-center text-emerald-500 text-3xl font-bold mx-auto mb-6 shadow-sm animate-bounce">
                    ✓
                </div>
                
                <h2 class="text-xl md:text-2xl font-extrabold text-gray-800 mb-2"><?php echo ($order_details['payment_method'] === 'cod') ? 'Order Placed Successfully!' : 'Payment Successful!'; ?></h2>
                <p class="text-xs md:text-sm text-gray-500 leading-relaxed max-w-md mx-auto mb-8"><?php echo ($order_details['payment_method'] === 'cod') ? 'Thank you! Your Cash on Delivery order has been successfully recorded.' : 'Thank you for your purchase. Your order has been placed successfully.'; ?></p>

                <!-- Order Receipt details -->
                <div class="bg-gray-50/50 border border-gray-100 rounded-2xl p-6 text-left mb-8 text-xs md:text-sm text-gray-600">
                    <h4 class="text-xs font-bold text-gray-800 border-b border-gray-100 pb-3 mb-4 uppercase tracking-wider">Order Receipt</h4>
                    
                    <div class="flex justify-between py-2 border-b border-gray-50">
                        <span>Order Reference:</span>
                        <strong class="text-gray-800 font-semibold truncate w-40 text-right" title="<?php echo htmlspecialchars($order_details['transaction_uuid']); ?>"><?php echo htmlspecialchars($order_details['transaction_uuid']); ?></strong>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-50">
                        <span>Name:</span>
                        <strong class="text-gray-800 font-semibold"><?php echo htmlspecialchars($order_details['fname'] . ' ' . $order_details['lname']); ?></strong>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-50">
                        <span>Email Address:</span>
                        <strong class="text-gray-800 font-semibold"><?php echo htmlspecialchars($order_details['email']); ?></strong>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-50">
                        <span>Delivery Address:</span>
                        <strong class="text-gray-800 font-semibold"><?php echo htmlspecialchars($order_details['address']); ?></strong>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-50">
                        <span>Payment Method:</span>
                        <strong class="text-gray-800 font-bold uppercase tracking-wider"><?php echo htmlspecialchars($order_details['payment_method']); ?></strong>
                    </div>
                    <div class="flex justify-between items-center pt-4 border-t border-dashed border-gray-200 text-sm md:text-base font-extrabold text-gray-800">
                        <span><?php echo ($order_details['payment_method'] === 'cod') ? 'Amount to Pay (COD):' : 'Amount Paid:'; ?></span>
                        <span class="text-primary text-base md:text-lg">Rs. <?php echo htmlspecialchars($order_details['total_amount']); ?></span>
                    </div>
                </div>

                <div class="flex gap-4 justify-center">
                    <a href="/shop.php" class="bg-white hover:bg-gray-50 text-gray-700 font-bold border border-gray-200 rounded-xl px-5 py-3 text-xs tracking-wider uppercase transition-all duration-150 cursor-pointer">Continue Shopping</a>
                    <a href="/index.php" class="bg-primary hover:bg-primary-dark text-white font-bold rounded-xl px-5 py-3 text-xs tracking-wider uppercase transition-all duration-150 cursor-pointer shadow-sm hover:shadow-md">Go to Homepage</a>
                </div>
            <?php else: ?>
                <!-- Red warning/failure icon -->
                <div class="w-16 h-16 rounded-full bg-rose-50 border-2 border-rose-500 flex items-center justify-center text-rose-500 text-3xl font-bold mx-auto mb-6 shadow-sm animate-pulse">
                    ✕
                </div>
                
                <h2 class="text-xl md:text-2xl font-extrabold text-gray-800 mb-2">Verification Failed</h2>
                <p class="text-xs md:text-sm text-red-600 leading-relaxed max-w-md mx-auto mb-8"><?php echo htmlspecialchars($error_message); ?></p>

                <div class="flex gap-4 justify-center">
                    <a href="/checkout.php" class="bg-white hover:bg-gray-50 text-gray-700 font-bold border border-gray-200 rounded-xl px-5 py-3 text-xs tracking-wider uppercase transition-all duration-150 cursor-pointer">Retry Checkout</a>
                    <a href="/shop.php" class="bg-primary hover:bg-primary-dark text-white font-bold rounded-xl px-5 py-3 text-xs tracking-wider uppercase transition-all duration-150 cursor-pointer shadow-sm hover:shadow-md">Back to Catalog</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <?php require INCLUDES_PATH . '/footer.php'; ?>
</body>

</html>
