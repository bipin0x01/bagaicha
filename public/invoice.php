<?php
/**
 * Bagaicha - Minimal Printable Invoice Page
 * Renders a clean, header-less, footer-less, direct-to-print invoice sheet.
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

// Security Check: Only the placing customer OR the admin can view the invoice
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
    $page_title = "Invoice #" . htmlspecialchars(substr($order['transaction_uuid'], 0, 12));
    require INCLUDES_PATH . '/partials/head.php'; 
    ?>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                color: #000000 !important;
                padding: 0 !important;
            }
            .print-container {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                border: none !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>

<body class="bg-gray-100/40 text-gray-800 antialiased font-sans min-h-screen py-12 px-4 flex flex-col justify-between">

    <div class="flex-1">
        <!-- Action Bar (no-print) -->
        <div class="max-w-3xl mx-auto no-print flex items-center justify-between mb-8 animate-fade-in">
            <button onclick="window.close()" class="text-xs font-bold text-gray-500 hover:text-gray-800 flex items-center gap-1.5 transition-colors cursor-pointer bg-transparent border-none">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                Close Window
            </button>
            <button onclick="window.print()" class="bg-primary hover:bg-primary-dark text-white text-xs font-bold rounded-xl px-5 py-2.5 transition-colors flex items-center gap-2 cursor-pointer shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Print Invoice
            </button>
        </div>

        <!-- Invoice Card Sheet -->
        <div class="print-container max-w-3xl mx-auto bg-white border border-gray-100 rounded-3xl p-8 md:p-12 shadow-md relative overflow-hidden animate-fade-in">
            
            <!-- Logo & Brand Info -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-gray-100 pb-8 mb-8 gap-6">
                <div>
                    <h1 class="text-3xl font-extrabold text-brand-dark tracking-tight"><span class="text-primary">B</span>agaicha</h1>
                    <p class="text-xs text-gray-400 mt-1.5 font-medium">Premium Handcrafted Bonsai &amp; Arborist Catalog</p>
                </div>
                <div class="text-left sm:text-right">
                    <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Receipt Invoice</span>
                    <strong class="text-sm font-mono text-gray-700 block max-w-[200px] truncate" title="<?php echo htmlspecialchars($order['transaction_uuid']); ?>">#<?php echo htmlspecialchars(substr($order['transaction_uuid'], 0, 16)); ?></strong>
                    <span class="text-xs text-gray-400 font-medium block mt-1"><?php echo date('F d, Y - h:i A', strtotime($order['created_at'])); ?></span>
                </div>
            </div>

            <!-- Client Billing & Shipping Logistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 border-b border-gray-100 pb-8 mb-8">
                <div>
                    <h4 class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-3">Invoice To</h4>
                    <strong class="text-sm font-bold text-gray-800 block"><?php echo htmlspecialchars($order['fname'] . ' ' . $order['lname']); ?></strong>
                    <span class="text-xs text-gray-500 block mt-1.5 font-medium"><?php echo htmlspecialchars($order['email']); ?></span>
                    <span class="text-xs text-gray-500 block font-medium"><?php echo htmlspecialchars($order['phone']); ?></span>
                </div>
                <div class="md:text-right">
                    <h4 class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-3">Shipping &amp; Logistics</h4>
                    <span class="text-xs text-gray-500 block font-medium">Destination: <strong class="text-gray-700 font-bold"><?php echo htmlspecialchars($order['address']); ?></strong></span>
                    <span class="text-xs text-gray-500 block mt-1.5 font-medium">Gateway: <strong class="text-gray-700 font-bold uppercase"><?php echo htmlspecialchars($order['payment_method']); ?></strong></span>
                    
                    <div class="mt-3 flex md:justify-end">
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
                            Status: <?php echo htmlspecialchars($status); ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Ledger Products Table -->
            <div class="mb-8">
                <h4 class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-4">Itemized Ledger</h4>
                <div class="border border-gray-100 rounded-2xl overflow-hidden shadow-inner bg-gray-50/20">
                    <table class="w-full text-left text-xs md:text-sm border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100 text-[10px] font-bold uppercase tracking-wider text-gray-500">
                                <th class="px-6 py-3 w-16">Item</th>
                                <th class="px-6 py-3">Product Spec</th>
                                <th class="px-6 py-3 text-right">Unit Price</th>
                                <th class="px-6 py-3 text-center w-24">Qty</th>
                                <th class="px-6 py-3 text-right w-36">Total Cost</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white text-gray-700 font-medium">
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td class="px-6 py-3">
                                        <div class="w-10 h-10 rounded-lg border border-gray-100 overflow-hidden shrink-0">
                                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="thumbnail" class="w-full h-full object-cover">
                                        </div>
                                    </td>
                                    <td class="px-6 py-3">
                                        <strong class="text-gray-800 font-bold block text-xs md:text-sm"><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                    </td>
                                    <td class="px-6 py-3 text-right font-mono">
                                        Rs. <?php echo number_format($item['price'], 2); ?>
                                    </td>
                                    <td class="px-6 py-3 text-center font-mono font-bold text-gray-650">
                                        <?php echo htmlspecialchars($item['quantity']); ?>
                                    </td>
                                    <td class="px-6 py-3 text-right font-mono font-bold text-gray-800">
                                        Rs. <?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Ledger Breakdown -->
            <div class="flex justify-end">
                <div class="w-full sm:w-80 space-y-3 text-xs md:text-sm text-gray-500 font-semibold border-t border-gray-100 pt-6">
                    <div class="flex justify-between">
                        <span>Ledger Subtotal</span>
                        <span class="text-gray-700 font-bold font-mono">Rs. <?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Shipping logistics Fee</span>
                        <span class="text-emerald-600 font-bold uppercase tracking-wider">Free</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Integrated Government VAT (13%)</span>
                        <span class="text-emerald-600 font-bold uppercase tracking-wider">Included</span>
                    </div>
                    <div class="flex justify-between items-center border-t border-gray-200 pt-4 text-gray-850 font-black text-sm md:text-base">
                        <span>Total Paid Amount</span>
                        <span class="text-primary text-base md:text-lg font-mono">Rs. <?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                </div>
            </div>

            <!-- Professional Watermark & Signature Seals -->
            <div class="mt-16 pt-8 border-t border-dashed border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-6 text-center sm:text-left">
                <div>
                    <h5 class="text-xs font-bold text-gray-800">Need Help?</h5>
                    <p class="text-[11px] text-gray-400 mt-1 font-medium">Support: contact@bagaicha.com | +977 9819284721</p>
                </div>
                <div>
                    <div class="border-b border-gray-200 w-36 h-8 mx-auto sm:mr-0 flex items-center justify-center font-serif text-gray-400 italic text-sm select-none">
                        Bagaicha Nepal
                    </div>
                    <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-widest text-center mt-1">Authorized Seal</span>
                </div>
            </div>

        </div>
    </div>

    <!-- Small professional bottom copyright banner (no-print) -->
    <div class="max-w-3xl mx-auto text-center mt-8 no-print text-[10px] text-gray-400 font-medium">
        &copy; Bagaicha 2026. Official invoice receipt generated securely.
    </div>

</body>

</html>
