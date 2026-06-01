<?php
/**
 * Bagaicha - User Profile Page
 * Displays account metadata and purchases history (orders log and item breakdowns) from SQLite.
 */
require_once dirname(__DIR__) . '/config/bootstrap.php';

// Redirect to login if guest
if (!isset($_SESSION['user_email'])) {
    header("Location: " . url('login.php'));
    exit;
}

$fname = $_SESSION['user_fname'] ?? '';
$lname = $_SESSION['user_lname'] ?? '';
$email = $_SESSION['user_email'] ?? '';

// 1. Fetch User details (e.g. registration date, phone, address) using email
$user_stmt = $db->prepare("SELECT created_at, phone, address FROM users WHERE email = :email");
$user_stmt->bindValue(':email', $email, SQLITE3_TEXT);
$user_res = $user_stmt->execute();
$user_row = $user_res->fetchArray(SQLITE3_ASSOC);
$reg_date = $user_row ? format_utc_datetime($user_row['created_at'], 'F d, Y') : "N/A";
$phone = $user_row ? $user_row['phone'] : (isset($_SESSION['user_phone']) ? $_SESSION['user_phone'] : "N/A");
$address = $user_row ? $user_row['address'] : (isset($_SESSION['user_address']) ? $_SESSION['user_address'] : "N/A");

// 2. Fetch Order History for this user based on email
$orders = [];
$orders_stmt = $db->prepare("SELECT * FROM orders WHERE email = :email ORDER BY created_at DESC");
$orders_stmt->bindValue(':email', $email, SQLITE3_TEXT);
$orders_res = $orders_stmt->execute();

if ($orders_res) {
    while ($row = $orders_res->fetchArray(SQLITE3_ASSOC)) {
        // Query items for each order
        $order_id = $row['id'];
        $items_stmt = $db->prepare("
            SELECT oi.*, p.name as product_name, p.image_url 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = :order_id
        ");
        $items_stmt->bindValue(':order_id', $order_id, SQLITE3_INTEGER);
        $items_res = $items_stmt->execute();
        
        $items = [];
        if ($items_res) {
            while ($item_row = $items_res->fetchArray(SQLITE3_ASSOC)) {
                $items[] = $item_row;
            }
        }
        $row['items'] = $items;
        $orders[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php 
    $page_title = "My Profile | Bagaicha";
    $page_description = "Manage your Bagaicha account, view order invoice logs, delivery coordinates, and purchase stats.";
    require INCLUDES_PATH . '/partials/head.php'; 
    ?>
</head>

<body class="bg-gray-50/30 text-gray-800 antialiased font-sans">
    <!-- Header -->
    <?php require INCLUDES_PATH . '/header.php'; ?>

    <!-- Main Container -->
    <main class="max-w-7xl mx-auto py-12 md:py-16 px-6 md:px-12 flex flex-col lg:flex-row gap-12 animate-fade-in">
        <!-- Left Side: Profile info card -->
        <div class="w-full lg:w-80 shrink-0 bg-white border border-gray-100 rounded-3xl p-8 shadow-sm flex flex-col items-center text-center h-fit">
            <div class="w-24 h-24 rounded-full bg-purple-50 flex items-center justify-center border border-purple-100 mb-5 overflow-hidden shadow-inner shrink-0">
                <img src="/assets/img/misc/user.png" alt="Profile" class="w-12 h-12 object-contain">
            </div>
            <h2 class="text-xl font-extrabold text-gray-800 mb-1 leading-tight"><?php echo htmlspecialchars($fname . ' ' . $lname); ?></h2>
            <p class="text-xs text-gray-400 mb-6 font-medium"><?php echo htmlspecialchars($email); ?></p>

            <div class="w-full border-t border-gray-100 pt-6 text-left space-y-4">
                <div>
                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Phone Number</span>
                    <strong class="text-sm font-semibold text-gray-700"><?php echo htmlspecialchars($phone); ?></strong>
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Delivery Address</span>
                    <strong class="text-sm font-semibold text-gray-700"><?php echo htmlspecialchars($address); ?></strong>
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Member Since</span>
                    <strong class="text-sm font-semibold text-gray-700"><?php echo $reg_date; ?></strong>
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Total Orders</span>
                    <strong class="text-sm font-semibold text-gray-700"><?php echo count($orders); ?> orders</strong>
                </div>
            </div>
            
            <a href="/logout.php" class="w-full bg-white hover:bg-red-50 text-red-500 font-bold border border-red-200 hover:border-red-300 rounded-xl py-3.5 text-xs tracking-wider uppercase mt-8 text-center block transition-colors cursor-pointer" data-confirm-logout="true">Log Out</a>
        </div>

        <!-- Right Side: Order History log -->
        <div class="flex-1 min-w-0">
            <h2 class="text-xl font-extrabold text-gray-800 border-b-2 border-primary pb-3 mb-6 w-fit">Purchase History</h2>
            
            <?php if (empty($orders)): ?>
                <div class="bg-white border border-gray-100 rounded-3xl py-14 px-4 text-center shadow-sm">
                    <p class="text-gray-500 text-sm mb-4">You have not made any purchases yet.</p>
                    <a href="/shop.php" class="inline-block bg-primary hover:bg-primary-dark text-white text-xs font-bold rounded-xl px-5 py-2.5 transition-colors">Shop Bonsais Now</a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-5 gap-3 mb-6">
                    <input id="profile-order-search" type="text" placeholder="Search by reference..." class="md:col-span-2 w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-xs text-gray-700">
                    <select id="profile-method-filter" class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-xs text-gray-700 cursor-pointer">
                        <option value="all">All methods</option>
                        <option value="esewa">eSewa</option>
                        <option value="cod">COD</option>
                    </select>
                    <select id="profile-payment-filter" class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-xs text-gray-700 cursor-pointer">
                        <option value="all">All payment status</option>
                        <option value="paid">Paid</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Failed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <select id="profile-order-status-filter" class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-xs text-gray-700 cursor-pointer">
                        <option value="all">All order statuses</option>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="completed">Completed</option>
                        <option value="failed">Failed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <select id="profile-order-sort" class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-xs text-gray-700 cursor-pointer md:col-span-2">
                        <option value="latest">Sort: Latest first</option>
                        <option value="oldest">Sort: Oldest first</option>
                        <option value="amount-desc">Amount: High to low</option>
                        <option value="amount-asc">Amount: Low to high</option>
                    </select>
                </div>
                <?php foreach ($orders as $order): ?>
                    <div class="profile-order-card bg-white border border-gray-200 rounded-xl mb-4 overflow-hidden"
                         data-reference="<?php echo htmlspecialchars(strtolower($order['transaction_uuid']), ENT_QUOTES, 'UTF-8'); ?>"
                         data-method="<?php echo htmlspecialchars(strtolower($order['payment_method'] ?? 'esewa'), ENT_QUOTES, 'UTF-8'); ?>"
                         data-payment="<?php echo htmlspecialchars(strtolower($order['payment_status'] ?? 'pending'), ENT_QUOTES, 'UTF-8'); ?>"
                         data-status="<?php echo htmlspecialchars(strtolower($order['status'] ?? 'pending'), ENT_QUOTES, 'UTF-8'); ?>"
                         data-created="<?php echo htmlspecialchars((string)strtotime($order['created_at']), ENT_QUOTES, 'UTF-8'); ?>"
                         data-total="<?php echo htmlspecialchars((string)((float)$order['total_amount']), ENT_QUOTES, 'UTF-8'); ?>">
                        <!-- Order header -->
                        <div class="px-6 py-4 border-b border-gray-100 grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 gap-4 items-center">
                            <div>
                                <span class="block text-[10px] font-semibold text-gray-500 mb-0.5">Order Date</span>
                                <span class="text-xs font-semibold text-gray-700"><?php echo format_utc_datetime($order['created_at'], 'F d, Y - h:i A'); ?></span>
                            </div>
                            <div>
                                <span class="block text-[10px] font-semibold text-gray-500 mb-0.5">Reference</span>
                                <span class="text-xs font-mono font-semibold text-gray-700 truncate block w-28" title="<?php echo htmlspecialchars($order['transaction_uuid']); ?>"><?php echo htmlspecialchars($order['transaction_uuid']); ?></span>
                            </div>
                            <div>
                                <span class="block text-[10px] font-semibold text-gray-500 mb-0.5">Total</span>
                                <span class="text-xs font-extrabold text-primary">Rs. <?php echo htmlspecialchars($order['total_amount']); ?></span>
                            </div>
                            <div>
                                <span class="block text-[10px] font-semibold text-gray-500 mb-0.5">Method</span>
                                <?php 
                                    $p_method = isset($order['payment_method']) ? $order['payment_method'] : 'esewa';
                                    $is_cod = ($p_method === 'cod');
                                ?>
                                <span class="inline-block px-2 py-0.5 border text-[9px] font-bold rounded-md uppercase tracking-wider <?php echo $is_cod ? 'bg-amber-50 text-amber-700 border-amber-100' : 'bg-emerald-50 text-emerald-700 border-emerald-100'; ?>">
                                    <?php echo htmlspecialchars($is_cod ? 'COD' : 'eSewa'); ?>
                                </span>
                            </div>
                            <div>
                                <span class="block text-[10px] font-semibold text-gray-500 mb-0.5">Payment</span>
                                <?php 
                                    $payment_status = $order['payment_status'] ?? 'pending';
                                    if ($payment_status === 'paid') {
                                        $badge_class = 'bg-emerald-50 text-emerald-700 border-emerald-100';
                                    } elseif ($payment_status === 'failed') {
                                        $badge_class = 'bg-rose-50 text-rose-700 border-rose-100';
                                    } elseif ($payment_status === 'cancelled') {
                                        $badge_class = 'bg-slate-100 text-slate-600 border-slate-200';
                                    } else {
                                        $badge_class = 'bg-amber-50 text-amber-700 border-amber-100';
                                    }
                                ?>
                                <span class="inline-block px-2 py-0.5 border text-[9px] font-bold rounded-md uppercase tracking-wider <?php echo $badge_class; ?>">
                                    <?php echo htmlspecialchars($payment_status); ?>
                                </span>
                            </div>
                        </div>

                        <!-- Order delivery details -->
                        <div class="px-6 py-3 border-b border-gray-100 text-xs text-gray-500 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                            <div>
                                Shipping Address: <strong class="text-gray-700 font-semibold"><?php echo htmlspecialchars($order['address']); ?></strong> 
                                <span class="mx-2 text-gray-300">|</span> Phone Number: <strong class="text-gray-700 font-semibold"><?php echo htmlspecialchars($order['phone']); ?></strong>
                            </div>
                            <a href="/order_details.php?uuid=<?php echo urlencode($order['transaction_uuid']); ?>" class="text-xs font-bold text-primary hover:text-primary-dark hover:underline flex items-center gap-1 transition-colors">
                                View Details &amp; Invoice
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </a>
                        </div>

                        <!-- Purchased items -->
                        <div class="px-6 py-4 divide-y divide-gray-100">
                            <?php foreach ($order['items'] as $item): ?>
                                <div class="flex justify-between items-center py-3 first:pt-0 last:pb-0">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg overflow-hidden border border-gray-100 shrink-0">
                                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="w-full h-full object-cover">
                                        </div>
                                        <div>
                                            <span class="font-bold text-gray-800 text-xs"><?php echo htmlspecialchars($item['product_name']); ?></span>
                                            <span class="text-gray-400 text-[10px] font-medium ml-2">(Qty: <?php echo htmlspecialchars($item['quantity']); ?>)</span>
                                        </div>
                                    </div>
                                    <span class="font-bold text-gray-700 text-xs">Rs. <?php echo htmlspecialchars($item['price'] * $item['quantity']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div id="profile-orders-empty" class="hidden bg-white border border-gray-100 rounded-3xl py-12 px-4 text-center shadow-sm">
                    <p class="text-gray-500 text-sm">No orders match selected filters.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <?php require INCLUDES_PATH . '/footer.php'; ?>
    <script>
        function filterProfileOrders() {
            const search = (document.getElementById('profile-order-search')?.value || '').trim().toLowerCase();
            const method = document.getElementById('profile-method-filter')?.value || 'all';
            const payment = document.getElementById('profile-payment-filter')?.value || 'all';
            const orderStatus = document.getElementById('profile-order-status-filter')?.value || 'all';
            const sort = document.getElementById('profile-order-sort')?.value || 'latest';
            const cards = Array.from(document.querySelectorAll('.profile-order-card'));
            const listParent = cards[0]?.parentElement;
            let visible = 0;

            cards.sort((a, b) => {
                if (sort === 'oldest') return Number(a.dataset.created || 0) - Number(b.dataset.created || 0);
                if (sort === 'amount-desc') return Number(b.dataset.total || 0) - Number(a.dataset.total || 0);
                if (sort === 'amount-asc') return Number(a.dataset.total || 0) - Number(b.dataset.total || 0);
                return Number(b.dataset.created || 0) - Number(a.dataset.created || 0);
            });
            if (listParent) cards.forEach(card => listParent.appendChild(card));

            cards.forEach(card => {
                const matchesSearch = !search || (card.dataset.reference || '').includes(search);
                const matchesMethod = method === 'all' || (card.dataset.method || '') === method;
                const matchesPayment = payment === 'all' || (card.dataset.payment || '') === payment;
                const matchesStatus = orderStatus === 'all' || (card.dataset.status || '') === orderStatus;
                const show = matchesSearch && matchesMethod && matchesPayment && matchesStatus;
                card.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            const empty = document.getElementById('profile-orders-empty');
            if (empty) empty.classList.toggle('hidden', visible > 0);
        }

        window.addEventListener('DOMContentLoaded', () => {
            const controls = [
                document.getElementById('profile-order-search'),
                document.getElementById('profile-method-filter'),
                document.getElementById('profile-payment-filter'),
                document.getElementById('profile-order-status-filter'),
                document.getElementById('profile-order-sort')
            ];
            controls.forEach(el => {
                if (!el) return;
                el.addEventListener('input', filterProfileOrders);
                el.addEventListener('change', filterProfileOrders);
            });
            filterProfileOrders();
        });
    </script>
</body>

</html>
