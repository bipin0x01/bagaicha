<?php
/**
 * Bagaicha - Premium Order Details Page
 * Displays customer billing data, shipping logistics, and itemized orders breakdowns.
 * Integrates storefront header & footer and links to the minimal invoice.php sheet.
 */
require_once dirname(__DIR__) . '/config/bootstrap.php';

// Redirect to login if guest
if (!isset($_SESSION['email'])) {
    header("Location: " . url('login.php'));
    exit;
}

$session_email = $_SESSION['email'];
$is_admin = ($session_email === 'admin@bagaicha.com');

$uuid = isset($_GET['uuid']) ? trim($_GET['uuid']) : '';
if (empty($uuid)) {
    header("Location: " . url('profile.php'));
    exit;
}

// Fetch order details
$stmt = $db->prepare("SELECT * FROM orders WHERE transaction_uuid = :uuid");
$stmt->bindValue(':uuid', $uuid, SQLITE3_TEXT);
$res = $stmt->execute();
$order = $res ? $res->fetchArray(SQLITE3_ASSOC) : null;

if (!$order) {
    header("Location: " . url('profile.php'));
    exit;
}

// Security Check: Only the placing customer OR the admin can view the order details
if (!$is_admin && strtolower($order['email']) !== strtolower($session_email)) {
    header("Location: " . url('profile.php'));
    exit;
}

// Fetch ordered items
$items = [];
$items_stmt = $db->prepare("
    SELECT oi.*, p.name as product_name, p.image_url 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = :order_id
");
$items_stmt->bindValue(':order_id', $order['id'], SQLITE3_INTEGER);
$items_res = $items_stmt->execute();
if ($items_res) {
    while ($row = $items_res->fetchArray(SQLITE3_ASSOC)) {
        $items[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php 
    $page_title = "Order Details #" . htmlspecialchars(substr($order['transaction_uuid'], 0, 8)) . " | Bagaicha";
    $page_description = "Check your Bagaicha purchase status, billing details, and items listing.";
    require INCLUDES_PATH . '/partials/head.php'; 
    ?>
</head>

<body class="bg-gray-50/30 text-gray-800 antialiased font-sans">
    <!-- Header -->
    <?php require INCLUDES_PATH . '/header.php'; ?>

    <!-- Shop Hero Header -->
    <div class="bg-brand-dark px-6 md:px-12 py-12 border-b border-white/5">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row md:items-end md:justify-between gap-6 text-white animate-fade-in">
            <div>
                <span class="text-[10px] font-extrabold uppercase tracking-widest text-primary-light bg-primary/30 border border-primary-light/20 rounded-full px-3.5 py-1.5 inline-block mb-3.5">Order Management</span>
                <h1 class="text-2xl md:text-3xl font-extrabold leading-tight tracking-tight">Order #<?php echo htmlspecialchars(substr($order['transaction_uuid'], 0, 16)); ?></h1>
                <p class="text-xs text-gray-300 mt-2 font-medium">Placed on <?php echo date('F d, Y - h:i A', strtotime($order['created_at'])); ?></p>
            </div>
            <div class="flex gap-3">
                <a href="/invoice.php?uuid=<?php echo urlencode($order['transaction_uuid']); ?>" target="_blank" class="bg-primary hover:bg-primary-dark text-white text-xs font-bold rounded-xl px-5 py-3.5 transition-all flex items-center gap-2 cursor-pointer shadow-sm hover:shadow-md hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Print / View Invoice
                </a>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <main class="max-w-7xl mx-auto py-12 md:py-16 px-6 md:px-12">
        
        <!-- Breadcrumb / Return Navigation -->
        <div class="flex items-center justify-between mb-8 animate-fade-in">
            <a href="<?php echo url($is_admin ? 'admin.php' : 'profile.php'); ?>" class="text-xs font-bold text-primary hover:text-primary-dark flex items-center gap-1.5 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Dashboard
            </a>
        </div>

        <!-- Order Layout Grid -->
        <div class="flex flex-col lg:flex-row gap-10 items-start animate-fade-in">
            
            <!-- Left Panel: Summary, Billing & Shipping Metadata -->
            <div class="w-full lg:w-80 shrink-0 bg-white border border-gray-100 rounded-3xl p-6 md:p-8 shadow-sm space-y-6">
                
                <!-- Status Badge Header -->
                <div>
                    <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-2">Delivery Status</span>
                    <?php 
                        $status = $order['status'];
                        if ($status === 'completed') {
                            $badge_class = 'bg-emerald-50 text-emerald-700 border-emerald-100';
                        } elseif ($status === 'failed') {
                            $badge_class = 'bg-rose-50 text-rose-700 border-rose-100';
                        } elseif ($status === 'cancelled') {
                            $badge_class = 'bg-slate-100 text-slate-600 border-slate-200';
                        } else {
                            $badge_class = 'bg-amber-50 text-amber-700 border-amber-100';
                        }
                    ?>
                    <span class="inline-block px-3 py-1 border text-[10px] font-extrabold rounded-full uppercase tracking-wider <?php echo $badge_class; ?>">
                        <?php echo htmlspecialchars($status); ?>
                    </span>
                </div>

                <div class="border-t border-gray-150/40 my-4"></div>

                <!-- Customer Details -->
                <div class="space-y-4">
                    <div>
                        <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Billed To</span>
                        <strong class="text-sm font-bold text-gray-800 block"><?php echo htmlspecialchars($order['fname'] . ' ' . $order['lname']); ?></strong>
                        <span class="text-xs text-gray-500 block mt-1 font-medium"><?php echo htmlspecialchars($order['email']); ?></span>
                        <span class="text-xs text-gray-500 block font-medium"><?php echo htmlspecialchars($order['phone']); ?></span>
                    </div>

                    <div>
                        <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Shipping Destination</span>
                        <strong class="text-sm font-semibold text-gray-700 block leading-relaxed"><?php echo htmlspecialchars($order['address']); ?></strong>
                    </div>

                    <div>
                        <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Payment details</span>
                        <strong class="text-xs font-semibold text-gray-700 block uppercase">Gateway: <?php echo htmlspecialchars($order['payment_method']); ?></strong>
                    </div>

                    <div>
                        <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Reference UUID</span>
                        <strong class="text-xs font-mono text-gray-700 block truncate" title="<?php echo htmlspecialchars($order['transaction_uuid']); ?>"><?php echo htmlspecialchars($order['transaction_uuid']); ?></strong>
                    </div>
                </div>
            </div>

            <!-- Right Panel: Purchased Items Breakdown & Pricing Table -->
            <div class="flex-1 min-w-0 w-full bg-white border border-gray-100 rounded-3xl p-6 md:p-8 shadow-sm">
                <h3 class="text-base font-bold text-gray-850 border-b border-gray-100 pb-3 mb-6">Ordered Bonsais</h3>
                
                <div class="border border-gray-100 rounded-2xl overflow-hidden shadow-inner bg-gray-50/20 mb-6">
                    <table class="w-full text-left text-xs md:text-sm border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100 text-[10px] font-bold uppercase tracking-wider text-gray-500">
                                <th class="px-6 py-4 w-16">Item</th>
                                <th class="px-6 py-4">Bonsai Tree</th>
                                <th class="px-6 py-4 text-right">Unit Price</th>
                                <th class="px-6 py-4 text-center w-24">Quantity</th>
                                <th class="px-6 py-4 text-right w-36">Total Cost</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white text-gray-700 font-medium">
                            <?php foreach ($items as $item): ?>
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="w-12 h-12 rounded-lg border border-gray-100 overflow-hidden shrink-0">
                                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="thumbnail" class="w-full h-full object-cover">
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <strong class="text-gray-800 font-bold block text-xs md:text-sm"><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                    </td>
                                    <td class="px-6 py-4 text-right font-mono text-xs">
                                        Rs. <?php echo number_format($item['price'], 2); ?>
                                    </td>
                                    <td class="px-6 py-4 text-center font-mono font-bold text-gray-650 text-xs">
                                        <?php echo htmlspecialchars($item['quantity']); ?>
                                    </td>
                                    <td class="px-6 py-4 text-right font-mono font-bold text-gray-800 text-xs">
                                        Rs. <?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Ledger Aggregations -->
                <div class="flex justify-end">
                    <div class="w-full sm:w-80 space-y-3.5 text-xs md:text-sm text-gray-500 font-semibold border-t border-gray-100 pt-6">
                        <div class="flex justify-between">
                            <span>Cart Subtotal</span>
                            <span class="text-gray-700 font-bold font-mono">Rs. <?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Shipping logistics Fee</span>
                            <span class="text-emerald-600 font-bold uppercase tracking-wider">Free</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Government VAT (13%)</span>
                            <span class="text-emerald-600 font-bold uppercase tracking-wider">Included</span>
                        </div>
                        <div class="flex justify-between items-center border-t border-gray-200 pt-4 text-gray-850 font-black text-sm md:text-base">
                            <span>Total Payable Amount</span>
                            <span class="text-primary text-base md:text-lg font-mono">Rs. <?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </main>

    <!-- Footer -->
    <?php require INCLUDES_PATH . '/footer.php'; ?>
</body>

</html>
