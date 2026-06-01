<?php
/**
 * Bagaicha - Payment Failure Handler
 * Decodes the optional base64 GET payload from eSewa,
 * marks order as failed in SQLite, and displays cancellation message.
 */
require_once 'db.php';
session_start();

$transaction_uuid = null;
if (isset($_GET['data'])) {
    $encoded_data = $_GET['data'];
    $decoded_json = base64_decode($encoded_data);
    $response = json_decode($decoded_json, true);

    if ($response && isset($response['transaction_uuid'])) {
        $transaction_uuid = $response['transaction_uuid'];
        
        // Update database order to failed
        $stmt = $db->prepare("UPDATE orders SET status = 'failed' WHERE transaction_uuid = :uuid AND status = 'pending'");
        $stmt->bindValue(':uuid', $transaction_uuid, SQLITE3_TEXT);
        $stmt->execute();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php 
    $page_title = "Payment Failed | Bagaicha";
    $page_description = "Your eSewa transaction could not be completed successfully. Please retry or contact support.";
    require_once 'assets/partials/_head.php'; 
    ?>
</head>

<body class="bg-gray-50/30 text-gray-800 antialiased font-sans">
    <!-- Header -->
    <?php require_once 'header.php'; ?>

    <!-- Failure Card Container -->
    <main class="min-h-[70vh] flex items-center justify-center py-16 px-6 md:px-12 bg-gray-50/30">
        <div class="bg-white rounded-3xl border border-gray-100 shadow-xl w-full max-w-xl p-8 md:p-10 text-center animate-fade-in">
            <!-- Red error cross icon -->
            <div class="w-16 h-16 rounded-full bg-rose-50 border-2 border-rose-500 flex items-center justify-center text-rose-500 text-3xl font-bold mx-auto mb-6 shadow-sm animate-pulse">
                ✕
            </div>
            
            <h2 class="text-xl md:text-2xl font-extrabold text-gray-800 mb-2">Payment Cancelled or Failed</h2>
            <p class="text-xs md:text-sm text-gray-500 leading-relaxed max-w-md mx-auto mb-6">
                Your eSewa transaction could not be completed successfully. This could be due to cancellation by user, network latency, or insufficient balance.
            </p>

            <?php if ($transaction_uuid): ?>
            <div class="bg-gray-50 border border-gray-100 rounded-xl p-3.5 text-xs text-gray-600 font-semibold mb-8 max-w-xs mx-auto">
                Order Reference: <strong class="text-gray-800 font-bold"><?php echo htmlspecialchars($transaction_uuid); ?></strong>
            </div>
            <?php endif; ?>

            <div class="flex gap-4 justify-center">
                <a href="checkout.php" class="bg-white hover:bg-gray-50 text-gray-700 font-bold border border-gray-200 rounded-xl px-5 py-3 text-xs tracking-wider uppercase transition-all duration-150 cursor-pointer">Retry Checkout</a>
                <a href="shop.php" class="bg-primary hover:bg-primary-dark text-white font-bold rounded-xl px-5 py-3 text-xs tracking-wider uppercase transition-all duration-150 cursor-pointer shadow-sm hover:shadow-md">Return to Shop</a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php require_once 'footer.php'; ?>
</body>

</html>
