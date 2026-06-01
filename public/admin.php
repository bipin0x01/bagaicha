<?php
/**
 * Bagaicha - Simple Admin Panel
 * Self-contained UAT Portal to view orders, update delivery statuses, and create/manage products.
 */
require_once dirname(__DIR__) . '/config/bootstrap.php';

$message = "";
$message_type = ""; // 'success' or 'error'

// 1. Process Admin Login Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if ($email === 'admin@bagaicha.com' && $password === 'admin123') {
        // Keep admin and customer sessions separate.
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_email'] = "admin@bagaicha.com";
        $_SESSION['admin_fname'] = "Admin";
        $_SESSION['admin_lname'] = "Bagaicha";
        
        $message = "Welcome back, Admin!";
        $message_type = "success";
    } else {
        $message = "Invalid Admin Credentials!";
        $message_type = "error";
    }
}

// Check if logged in as Admin
$is_admin = !empty($_SESSION['admin_logged_in']) && (($_SESSION['admin_email'] ?? '') === 'admin@bagaicha.com');

// 2. Handle Admin Actions (only if logged in as admin)
if ($is_admin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ACTION A: Add New Product
    if (isset($_POST['add_product']) && $_POST['add_product'] == '1') {
        $name = trim($_POST['name']);
        $price = (float)$_POST['price'];
        $discount_price = !empty($_POST['discount_price']) ? (float)$_POST['discount_price'] : null;
        $description = trim($_POST['description']);
        $image_url = trim($_POST['image_url']);

        // Handle Image File Upload if provided
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['image_file']['tmp_name'];
            $file_name = basename($_FILES['image_file']['name']);
            // Standardize file name
            $clean_file_name = time() . '_' . preg_replace("/[^a-zA-Z0-9._-]/", "_", $file_name);
            $upload_dir = PUBLIC_PATH . '/assets/img/products/';
            
            // Create upload dir if not present
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $target_path = $upload_dir . $clean_file_name;
            if (move_uploaded_file($file_tmp, $target_path)) {
                $image_url = asset('img/products/' . $clean_file_name);
            }
        }

        if (empty($name) || empty($price) || empty($image_url)) {
            $message = "Please fill in all mandatory product fields!";
            $message_type = "error";
        } else {
            $stmt = $db->prepare("INSERT INTO products (name, price, discount_price, image_url, description) VALUES (:name, :price, :discount, :img, :desc)");
            $stmt->bindValue(':name', $name, SQLITE3_TEXT);
            $stmt->bindValue(':price', $price, SQLITE3_FLOAT);
            $stmt->bindValue(':discount', $discount_price, SQLITE3_FLOAT);
            $stmt->bindValue(':img', $image_url, SQLITE3_TEXT);
            $stmt->bindValue(':desc', $description, SQLITE3_TEXT);
            
            if ($stmt->execute()) {
                $message = "Product <strong>" . htmlspecialchars($name) . "</strong> created successfully!";
                $message_type = "success";
            } else {
                $message = "Failed to add product. Please try again.";
                $message_type = "error";
            }
        }
    }

    // ACTION E: Update Product
    if (isset($_POST['update_product']) && $_POST['update_product'] == '1') {
        $product_id = (int)$_POST['product_id'];
        $name = trim($_POST['name']);
        $price = (float)$_POST['price'];
        $discount_price = !empty($_POST['discount_price']) ? (float)$_POST['discount_price'] : null;
        $description = trim($_POST['description']);
        $image_url = trim($_POST['image_url']);

        // Handle Image File Upload if provided
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['image_file']['tmp_name'];
            $file_name = basename($_FILES['image_file']['name']);
            // Standardize file name
            $clean_file_name = time() . '_' . preg_replace("/[^a-zA-Z0-9._-]/", "_", $file_name);
            $upload_dir = PUBLIC_PATH . '/assets/img/products/';
            
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $target_path = $upload_dir . $clean_file_name;
            if (move_uploaded_file($file_tmp, $target_path)) {
                $image_url = asset('img/products/' . $clean_file_name);
            }
        }

        if (empty($name) || empty($price)) {
            $message = "Please fill in all mandatory product fields!";
            $message_type = "error";
        } else {
            if (!empty($image_url)) {
                $stmt = $db->prepare("UPDATE products SET name = :name, price = :price, discount_price = :discount, image_url = :img, description = :desc WHERE id = :id");
                $stmt->bindValue(':img', $image_url, SQLITE3_TEXT);
            } else {
                $stmt = $db->prepare("UPDATE products SET name = :name, price = :price, discount_price = :discount, description = :desc WHERE id = :id");
            }
            
            $stmt->bindValue(':name', $name, SQLITE3_TEXT);
            $stmt->bindValue(':price', $price, SQLITE3_FLOAT);
            $stmt->bindValue(':discount', $discount_price, SQLITE3_FLOAT);
            $stmt->bindValue(':desc', $description, SQLITE3_TEXT);
            $stmt->bindValue(':id', $product_id, SQLITE3_INTEGER);
            
            if ($stmt->execute()) {
                $message = "Product <strong>" . htmlspecialchars($name) . "</strong> updated successfully!";
                $message_type = "success";
            } else {
                $message = "Failed to update product details.";
                $message_type = "error";
            }
        }
    }

    // ACTION B: Delete Product
    if (isset($_POST['delete_product'])) {
        $product_id = (int)$_POST['product_id'];
        $stmt = $db->prepare("DELETE FROM products WHERE id = :id");
        $stmt->bindValue(':id', $product_id, SQLITE3_INTEGER);
        
        if ($stmt->execute()) {
            $message = "Product deleted successfully.";
            $message_type = "success";
        } else {
            $message = "Failed to delete product.";
            $message_type = "error";
        }
    }

    // ACTION C: Update Order Status
    if (isset($_POST['update_order_status'])) {
        $order_id = (int)$_POST['order_id'];
        $new_status = trim($_POST['status']);
        
        // 1. Fetch current status/payment metadata of this order
        $chk_stmt = $db->prepare("SELECT status, payment_method, payment_status FROM orders WHERE id = :id");
        $chk_stmt->bindValue(':id', $order_id, SQLITE3_INTEGER);
        $chk_res = $chk_stmt->execute();
        $chk_row = $chk_res ? $chk_res->fetchArray(SQLITE3_ASSOC) : null;
        
        if ($chk_row) {
            $current_status = $chk_row['status'];
            $is_valid = false;
            
            if ($current_status === $new_status) {
                $is_valid = true;
            } elseif ($current_status === 'pending') {
                // Pending can transition to processing, completed, failed, or cancelled
                $allowed = ['processing', 'completed', 'failed', 'cancelled'];
                if (in_array($new_status, $allowed)) {
                    $is_valid = true;
                }
            } elseif ($current_status === 'processing') {
                // Processing can transition to completed, failed, or cancelled. Backtracking is blocked.
                $allowed = ['completed', 'failed', 'cancelled'];
                if (in_array($new_status, $allowed)) {
                    $is_valid = true;
                }
            }
            
            if ($is_valid) {
                $stmt = $db->prepare("
                    UPDATE orders
                    SET status = :status,
                        payment_status = CASE
                            WHEN :status = 'failed' THEN 'failed'
                            WHEN :status = 'cancelled' AND payment_status != 'paid' THEN 'cancelled'
                            WHEN :status = 'completed' AND payment_method = 'cod' AND payment_status = 'pending' THEN 'paid'
                            ELSE payment_status
                        END
                    WHERE id = :id
                ");
                $stmt->bindValue(':status', $new_status, SQLITE3_TEXT);
                $stmt->bindValue(':id', $order_id, SQLITE3_INTEGER);
                
                if ($stmt->execute()) {
                    $message = "Order status updated to <strong>" . htmlspecialchars($new_status) . "</strong>.";
                    $message_type = "success";
                } else {
                    $message = "Failed to update order status.";
                    $message_type = "error";
                }
            } else {
                $message = "Error: Invalid status transition from <strong>" . htmlspecialchars($current_status) . "</strong> to <strong>" . htmlspecialchars($new_status) . "</strong> is not allowed!";
                $message_type = "error";
            }
        } else {
            $message = "Error: Order not found.";
            $message_type = "error";
        }
    }
}

// Fetch dashboard statistics if logged in as Admin
$stats = [];
$products = [];
$orders = [];

if ($is_admin) {
    // 1. Calculate Stats
    $stats['total_products'] = $db->querySingle("SELECT COUNT(*) FROM products") ?: 0;
    $stats['total_orders'] = $db->querySingle("SELECT COUNT(*) FROM orders") ?: 0;
    $stats['revenue'] = $db->querySingle("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'paid'") ?: 0.00;
    $stats['revenue_cod'] = $db->querySingle("SELECT SUM(total_amount) FROM orders WHERE payment_method = 'cod' AND payment_status = 'paid'") ?: 0.00;
    $stats['revenue_esewa'] = $db->querySingle("SELECT SUM(total_amount) FROM orders WHERE payment_method = 'esewa' AND payment_status = 'paid'") ?: 0.00;
    
    // 2. Get Products List
    $prod_res = $db->query("SELECT * FROM products ORDER BY id DESC");
    if ($prod_res) {
        while ($row = $prod_res->fetchArray(SQLITE3_ASSOC)) {
            $products[] = $row;
        }
    }

    // 3. Get Orders List with Customer details
    $order_res = $db->query("SELECT * FROM orders ORDER BY created_at DESC");
    if ($order_res) {
        while ($row = $order_res->fetchArray(SQLITE3_ASSOC)) {
            // Get order items count
            $order_id = $row['id'];
            $items_count = $db->querySingle("SELECT SUM(quantity) FROM order_items WHERE order_id = $order_id") ?: 0;
            $row['items_count'] = $items_count;
            
            // Get order items details
            $items = [];
            $items_stmt = $db->prepare("
                SELECT oi.*, p.name as product_name, p.image_url 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = :order_id
            ");
            $items_stmt->bindValue(':order_id', $order_id, SQLITE3_INTEGER);
            $items_res = $items_stmt->execute();
            if ($items_res) {
                while ($item_row = $items_res->fetchArray(SQLITE3_ASSOC)) {
                    $items[] = $item_row;
                }
            }
            $row['items'] = $items;
            $orders[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php 
    $page_title = "Admin Dashboard | Bagaicha";
    $page_description = "Manage Bagaicha inventory, view real-time revenue stats, and verify customer payment transaction logs.";
    require INCLUDES_PATH . '/partials/head.php'; 
    ?>
</head>

<body class="bg-gray-100 text-gray-800 antialiased font-sans">

    <?php if (!$is_admin): ?>
        <!-- ADMIN LOGIN FORM SECTION -->
        <div class="min-h-screen flex items-center justify-center bg-gradient-to-tr from-gray-50 via-gray-100 to-gray-200 p-6">
            <div class="w-full max-w-md bg-white rounded-3xl border border-gray-100 shadow-xl p-8 md:p-10 animate-fade-in">
                <h2 class="text-2xl font-extrabold text-gray-800 text-center mb-1">Admin Portal Login</h2>
                <p class="text-xs text-gray-400 text-center mb-6">Enter administrative credentials to access control settings.</p>
                
                <?php require INCLUDES_PATH . '/partials/alert.php'; ?>

                <form action="/admin.php" method="POST" class="space-y-4">
                    <input type="hidden" name="admin_login" value="1">
                    
                    <div>
                        <label for="email" class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Admin Email</label>
                        <input type="email" id="email" name="email" value="admin@bagaicha.com" required class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-sm text-gray-800 transition-colors">
                    </div>

                    <div>
                        <label for="password" class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter Admin Password" required class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-sm text-gray-800 transition-colors">
                    </div>

                    <button type="submit" class="w-full bg-primary hover:bg-primary-dark text-white font-bold rounded-xl py-3.5 text-sm transition-colors cursor-pointer shadow-sm hover:shadow-md mt-2">Login</button>
                </form>
                
                <p class="text-[10px] text-gray-400 text-center mt-6 leading-relaxed bg-gray-50 border border-gray-100 rounded-xl p-3">
                    Credentials Tip:<br>
                    Email: <code class="font-bold text-primary">admin@bagaicha.com</code><br>
                    Password: <code class="font-bold text-primary">admin123</code>
                </p>
                <div class="text-center mt-5">
                    <a href="/index.php" class="text-xs font-bold text-primary hover:underline">Back to Storefront</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- ADMIN DASHBOARD PANEL SECTION -->
        <div class="flex min-h-screen">
            
            <!-- Sidebar Panel Left -->
            <aside class="w-64 fixed h-screen bg-brand-dark text-gray-300 flex flex-col justify-between border-r border-gray-800 shadow-xl z-50">
                <div>
                    <div class="px-6 py-6 border-b border-gray-800/80 text-xl font-extrabold text-white tracking-tight uppercase">
                        <span class="text-primary">B</span>agaicha Admin
                    </div>
                    
                    <ul class="px-4 py-6 space-y-2 list-none">
                        <li>
                            <button class="sidebar-tab-btn w-full px-5 py-3 text-left text-sm font-bold text-white bg-primary rounded-xl flex items-center gap-3 shadow-md shadow-primary/20 transition-all duration-150 cursor-pointer active" onclick="switchTab('tab-dashboard')">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7m-9 2v8m0-8L5 10m14 0l-5-5"></path></svg>
                                <span>Overview</span>
                            </button>
                        </li>
                        <li>
                            <button class="sidebar-tab-btn w-full px-5 py-3 text-left text-sm font-semibold text-white/70 rounded-xl flex items-center gap-3 hover:bg-white/5 hover:text-white transition-all duration-150 cursor-pointer" onclick="switchTab('tab-products')">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V7a2 2 0 00-2-2h-3V3H9v2H6a2 2 0 00-2 2v6m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0H4"></path></svg>
                                <span>Inventory</span>
                            </button>
                        </li>
                        <li>
                            <button class="sidebar-tab-btn w-full px-5 py-3 text-left text-sm font-semibold text-white/70 rounded-xl flex items-center gap-3 hover:bg-white/5 hover:text-white transition-all duration-150 cursor-pointer" onclick="switchTab('tab-orders')">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5h6m-6 4h6m-7 4h8m-9 4h10M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"></path></svg>
                                <span>Orders</span>
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="px-4 py-6 border-t border-gray-800/50 space-y-3">
                    <a href="/logout.php" class="flex items-center justify-center gap-2 py-3 bg-red-500/10 hover:bg-red-500/25 border border-red-500/25 text-red-400 hover:text-white text-xs font-extrabold rounded-xl transition-all cursor-pointer" data-confirm-logout="true">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        Logout
                    </a>
                </div>
            </aside>

            <!-- Viewport Content Right -->
            <div class="flex-1 ml-64 p-8 bg-gray-50 min-h-screen overflow-y-auto">
                
                <!-- Welcome header bar -->
                <div class="flex justify-between items-center bg-white border border-gray-100 rounded-2xl px-8 py-6 mb-8 shadow-sm">
                    <div>
                        <h1 class="text-2xl font-extrabold text-gray-800">Admin Dashboard</h1>
                        <p class="text-xs text-gray-500 mt-1">Welcome back, <strong><?php echo htmlspecialchars(trim(($_SESSION['admin_fname'] ?? 'Admin') . ' ' . ($_SESSION['admin_lname'] ?? ''))); ?></strong>. Manage inventory, view logs, and update order statuses.</p>
                    </div>
                    <div class="bg-gradient-to-br from-white to-gray-50 border border-gray-100 rounded-2xl px-4 py-3 min-w-[290px] shadow-sm">
                        <div class="flex items-start gap-3">
                            <div class="w-11 h-11 rounded-xl bg-primary/12 border border-primary/20 text-primary font-extrabold text-sm flex items-center justify-center shrink-0">
                                <?php
                                    $admin_fname = $_SESSION['admin_fname'] ?? 'Admin';
                                    $admin_lname = $_SESSION['admin_lname'] ?? '';
                                    $admin_name = trim($admin_fname . ' ' . $admin_lname);
                                    $initials = strtoupper(substr($admin_fname, 0, 1) . substr($admin_lname, 0, 1));
                                    if ($initials === '') $initials = 'AD';
                                ?>
                                <?php echo htmlspecialchars($initials); ?>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-sm font-extrabold text-gray-800 truncate"><?php echo htmlspecialchars($admin_name); ?></p>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-100 shrink-0">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Admin
                                    </span>
                                </div>
                                <p class="text-[11px] text-gray-500 font-medium truncate"><?php echo htmlspecialchars($_SESSION['admin_email'] ?? 'admin@bagaicha.com'); ?></p>
                                <p class="text-[10px] text-gray-400 mt-1.5">Local time: <?php echo local_now('d M Y, h:i A'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <?php require INCLUDES_PATH . '/partials/alert.php'; ?>

                <!-- TAB 1: DASHBOARD STATS -->
                <div id="tab-dashboard" class="admin-tab-content bg-white border border-gray-100 rounded-3xl p-8 shadow-sm space-y-8">
                    <div>
                        <h3 class="text-base font-bold text-gray-800 border-b border-gray-100 pb-3">Overview</h3>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                            <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-3">Cataloged Bonsais</span>
                            <div class="text-3xl font-extrabold text-gray-850 leading-none mb-1.5"><?php echo $stats['total_products']; ?></div>
                            <span class="text-xs text-gray-400 font-medium">active varieties</span>
                        </div>
                        <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                            <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-3">Customer Orders</span>
                            <div class="text-3xl font-extrabold text-gray-850 leading-none mb-1.5"><?php echo $stats['total_orders']; ?></div>
                            <span class="text-xs text-gray-400 font-medium">total transactions</span>
                        </div>
                        <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                            <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-3">Cleared Revenue</span>
                            <div class="text-2xl font-extrabold text-gray-850 leading-none mb-1.5">Rs. <?php echo number_format($stats['revenue'], 2); ?></div>
                            <span class="text-xs text-gray-400 font-medium">completed orders only</span>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-base font-bold text-gray-800 border-b border-gray-100 pb-3 mb-6">Revenue Breakdown by Gateway</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 max-w-2xl">
                            <div class="bg-white border border-gray-100 rounded-2xl p-5 flex items-center gap-4 shadow-sm">
                                <div class="w-11 h-11 bg-emerald-50 border border-emerald-100 rounded-xl flex items-center justify-center shrink-0 px-1.5">
                                    <img src="/assets/img/misc/esewa_logo.png" alt="eSewa" class="h-6 w-auto object-contain">
                                </div>
                                <div>
                                    <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">eSewa Cleared</span>
                                    <strong class="text-base font-extrabold text-gray-850">Rs. <?php echo number_format($stats['revenue_esewa'], 2); ?></strong>
                                </div>
                            </div>
                            <div class="bg-white border border-gray-100 rounded-2xl p-5 flex items-center gap-4 shadow-sm">
                                <div class="w-11 h-11 bg-amber-50 border border-amber-100 rounded-xl flex items-center justify-center shrink-0">
                                    <span class="text-xs font-black text-amber-700 tracking-tight">COD</span>
                                </div>
                                <div>
                                    <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Cash on Delivery</span>
                                    <strong class="text-base font-extrabold text-gray-850">Rs. <?php echo number_format($stats['revenue_cod'], 2); ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: INVENTORY -->
                <div id="tab-products" class="admin-tab-content bg-white border border-gray-100 rounded-3xl p-8 shadow-sm hidden space-y-6">

                    <!-- Tab header row -->
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-gray-850">Product Inventory</h3>
                            <p class="text-xs text-gray-400 mt-0.5 font-medium"><?php echo count($products); ?> varieties cataloged</p>
                        </div>
                        <button onclick="openProductModal()" class="bg-primary hover:bg-primary-dark text-white text-xs font-bold rounded-xl px-5 py-2.5 transition-colors cursor-pointer shadow-sm hover:shadow-md">+ New Product</button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <input id="admin-product-search" type="text" placeholder="Search product name..." class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-xs text-gray-700">
                        <select id="admin-product-price-filter" class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-xs text-gray-700 cursor-pointer">
                            <option value="all">All price ranges</option>
                            <option value="0-3000">Up to Rs. 3,000</option>
                            <option value="3000-7000">Rs. 3,000 - 7,000</option>
                            <option value="7000+">Above Rs. 7,000</option>
                        </select>
                        <select id="admin-product-discount-filter" class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-xs text-gray-700 cursor-pointer">
                            <option value="all">All pricing types</option>
                            <option value="discounted">Discounted only</option>
                            <option value="regular">Regular price only</option>
                        </select>
                        <select id="admin-product-sort" class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-xs text-gray-700 cursor-pointer">
                            <option value="default">Sort: Default</option>
                            <option value="name-asc">Name A-Z</option>
                            <option value="price-asc">Price Low-High</option>
                            <option value="price-desc">Price High-Low</option>
                        </select>
                    </div>

                    <!-- Full-width inventory table -->
                    <div class="border border-gray-100 rounded-2xl overflow-hidden shadow-inner bg-gray-50/20">
                        <table class="w-full text-left text-xs md:text-sm border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100 text-[10px] font-bold uppercase tracking-wider text-gray-500">
                                    <th class="px-6 py-4 w-20">Image</th>
                                    <th class="px-6 py-4">Product Name</th>
                                    <th class="px-6 py-4">Selling Price</th>
                                    <th class="px-6 py-4">Original Price</th>
                                    <th class="px-6 py-4 w-60">Description</th>
                                    <th class="px-6 py-4 text-center w-24">Action</th>
                                </tr>
                            </thead>
                            <tbody id="admin-products-tbody" class="divide-y divide-gray-100 bg-white">
                                <?php if (empty($products)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-gray-400 py-16">
                                            <span>No products yet.</span>
                                            <span onclick="openProductModal()" class="text-primary hover:underline cursor-pointer font-bold ml-1.5">Add your first product</span>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($products as $p): ?>
                                        <tr class="admin-product-row hover:bg-gray-50/50 transition-colors"
                                            data-name="<?php echo htmlspecialchars(strtolower($p['name']), ENT_QUOTES, 'UTF-8'); ?>"
                                            data-price="<?php echo (float)$p['price']; ?>"
                                            data-discount="<?php echo (!empty($p['discount_price']) && (float)$p['discount_price'] > (float)$p['price']) ? '1' : '0'; ?>">
                                            <td class="px-6 py-4">
                                                <div class="w-12 h-12 rounded-lg border border-gray-100 overflow-hidden shrink-0">
                                                    <img src="<?php echo htmlspecialchars($p['image_url']); ?>" alt="product" class="w-full h-full object-cover">
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <strong class="text-gray-800 font-bold text-xs"><?php echo htmlspecialchars($p['name']); ?></strong>
                                            </td>
                                            <td class="px-6 py-4 font-bold text-primary text-xs">
                                                Rs. <?php echo number_format((float)$p['price'], 2); ?>
                                            </td>
                                            <td class="px-6 py-4 text-gray-400 line-through text-xs">
                                                <?php echo !empty($p['discount_price']) ? 'Rs. ' . number_format((float)$p['discount_price'], 2) : '—'; ?>
                                            </td>
                                            <td class="px-6 py-4 text-gray-500 text-xs max-w-xs">
                                                <span class="line-clamp-2 leading-relaxed" title="<?php echo htmlspecialchars($p['description']); ?>">
                                                    <?php echo htmlspecialchars($p['description'] ?: '—'); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <div class="flex items-center justify-center gap-2">
                                                    <button onclick="openEditProductModal(<?php echo htmlspecialchars(json_encode($p)); ?>)" class="bg-purple-50 hover:bg-primary border border-purple-100 hover:border-primary text-primary hover:text-white font-semibold rounded-lg px-3 py-1.5 text-xs transition-colors cursor-pointer" type="button">
                                                        Edit
                                                    </button>
                                                    <form action="/admin.php" method="POST" data-confirm-message="Delete '<?php echo addslashes(htmlspecialchars($p['name'])); ?>'? This cannot be undone." class="m-0 inline">
                                                        <input type="hidden" name="delete_product" value="1">
                                                        <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                                                        <button type="submit" class="bg-red-50 hover:bg-red-500 border border-red-100 hover:border-red-500 text-red-600 hover:text-white font-semibold rounded-lg px-3 py-1.5 text-xs transition-colors cursor-pointer">
                                                            Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ADD PRODUCT MODAL -->
                <div id="add-product-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[99999] hidden items-center justify-center p-4">
                    <div class="bg-white rounded-3xl w-full max-w-lg overflow-hidden box-shadow-2xl flex flex-col animate-fade-in">

                        <!-- Modal Header -->
                        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 bg-gray-50/50">
                            <div>
                                <h2 class="text-base font-bold text-gray-800" id="modal-title-text">New Product</h2>
                                <p class="text-[10px] text-gray-400 font-medium mt-0.5" id="modal-subtitle-text">Add a new bonsai to the catalog</p>
                            </div>
                            <button onclick="closeProductModal()" class="p-1.5 hover:bg-gray-200 text-gray-400 hover:text-gray-600 rounded-lg transition-colors cursor-pointer" type="button">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        <!-- Modal Form -->
                        <form action="/admin.php" method="POST" enctype="multipart/form-data" class="px-6 py-6 space-y-4 max-h-[80vh] overflow-y-auto pr-2" id="product-modal-form">
                            <input type="hidden" name="add_product" id="modal_action_add" value="1">
                            <input type="hidden" name="update_product" id="modal_action_update" value="0">
                            <input type="hidden" name="product_id" id="modal_product_id" value="">

                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Product Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" placeholder="e.g. Cherry Blossom Bonsai" required class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-sm text-gray-800 transition-colors">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Selling Price (Rs.) <span class="text-red-500">*</span></label>
                                    <input type="number" step="0.01" name="price" placeholder="4500" required class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-sm text-gray-800 transition-colors">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Original Price <span class="text-gray-400 font-normal">(optional)</span></label>
                                    <input type="number" step="0.01" name="discount_price" placeholder="5200" class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-sm text-gray-800 transition-colors">
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Image URL <span class="text-gray-400 font-normal">(paste a link)</span></label>
                                <input type="text" name="image_url" id="modal_image_url" placeholder="https://..." class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-sm text-gray-800 transition-colors" oninput="previewModalImage(this.value)">
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="flex-1 h-[1px] bg-gray-100"></div>
                                <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">or upload a file</span>
                                <div class="flex-1 h-[1px] bg-gray-100"></div>
                            </div>

                            <div>
                                <input type="file" name="image_file" accept="image/*" id="modal_image_file" class="w-full text-xs text-gray-500 cursor-pointer" onchange="previewModalImageFile(this)">
                            </div>

                            <!-- Image preview -->
                            <div id="modal-img-preview" class="hidden border border-gray-100 rounded-xl overflow-hidden h-36 bg-gray-50/50">
                                <img id="modal-preview-img" src="" alt="Preview" class="w-full h-full object-cover">
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Description &amp; Care Notes</label>
                                <textarea name="description" placeholder="Handcrafted premium bonsai — describe species, size, light requirements..." class="w-full h-24 bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-sm text-gray-800 resize-none leading-relaxed"></textarea>
                            </div>

                            <button type="submit" id="modal-submit-btn" class="w-full bg-primary hover:bg-primary-dark text-white font-bold rounded-xl py-3.5 text-sm transition-colors cursor-pointer shadow-sm hover:shadow-md">Save Product to Catalog</button>
                        </form>
                    </div>
                </div>

                <!-- TAB 3: MANAGE ORDERS -->
                <div id="tab-orders" class="admin-tab-content bg-white border border-gray-100 rounded-3xl p-8 shadow-sm hidden space-y-6">
                    <div>
                        <h3 class="text-base font-bold text-gray-850 border-b border-gray-100 pb-3">Orders</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-3">
                        <input id="admin-order-search" type="text" placeholder="Search customer, email, ref..." class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-xs text-gray-700 lg:col-span-2">
                        <select id="admin-order-method-filter" class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-xs text-gray-700 cursor-pointer">
                            <option value="all">All methods</option>
                            <option value="esewa">eSewa</option>
                            <option value="cod">COD</option>
                        </select>
                        <select id="admin-order-payment-filter" class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-xs text-gray-700 cursor-pointer">
                            <option value="all">All payment</option>
                            <option value="paid">Paid</option>
                            <option value="pending">Pending</option>
                            <option value="failed">Failed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <select id="admin-order-status-filter" class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-xs text-gray-700 cursor-pointer">
                            <option value="all">All order statuses</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="completed">Completed</option>
                            <option value="failed">Failed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <select id="admin-order-sort" class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-xs text-gray-700 cursor-pointer">
                            <option value="latest">Sort: Latest first</option>
                            <option value="oldest">Sort: Oldest first</option>
                            <option value="amount-desc">Amount high-low</option>
                            <option value="amount-asc">Amount low-high</option>
                            <option value="customer-asc">Customer A-Z</option>
                        </select>
                    </div>
                    
                    <div class="border border-gray-100 rounded-2xl overflow-hidden shadow-inner bg-gray-50/20">
                        <table class="w-full text-left text-xs md:text-sm border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100 text-[10px] font-bold uppercase tracking-wider text-gray-500">
                                    <th class="px-6 py-4">Customer Details</th>
                                    <th class="px-6 py-4">Delivery Address</th>
                                    <th class="px-6 py-4">Reference ID</th>
                                    <th class="px-6 py-4">Total Payable</th>
                                    <th class="px-6 py-4">Method</th>
                                    <th class="px-6 py-4">Payment</th>
                                    <th class="px-6 py-4">Status</th>
                                </tr>
                            </thead>
                            <tbody id="admin-orders-tbody" class="divide-y divide-gray-100 bg-white">
                                <?php if (empty($orders)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-gray-400 py-16">No customer transactions logged in SQLite.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($orders as $o): ?>
                                        <tr class="admin-order-row hover:bg-gray-50/50 transition-colors cursor-pointer"
                                            data-customer="<?php echo htmlspecialchars(strtolower($o['fname'] . ' ' . $o['lname']), ENT_QUOTES, 'UTF-8'); ?>"
                                            data-email="<?php echo htmlspecialchars(strtolower($o['email']), ENT_QUOTES, 'UTF-8'); ?>"
                                            data-reference="<?php echo htmlspecialchars(strtolower($o['transaction_uuid']), ENT_QUOTES, 'UTF-8'); ?>"
                                            data-method="<?php echo htmlspecialchars(strtolower($o['payment_method']), ENT_QUOTES, 'UTF-8'); ?>"
                                            data-payment="<?php echo htmlspecialchars(strtolower($o['payment_status'] ?? 'pending'), ENT_QUOTES, 'UTF-8'); ?>"
                                            data-status="<?php echo htmlspecialchars(strtolower($o['status']), ENT_QUOTES, 'UTF-8'); ?>"
                                            data-created="<?php echo htmlspecialchars((string)strtotime($o['created_at']), ENT_QUOTES, 'UTF-8'); ?>"
                                            data-total="<?php echo htmlspecialchars((string)((float)$o['total_amount']), ENT_QUOTES, 'UTF-8'); ?>"
                                            onclick="showAdminOrderDetails(<?php echo $o['id']; ?>)">
                                            <td class="px-6 py-4">
                                                <strong class="text-gray-800 font-bold block"><?php echo htmlspecialchars($o['fname'] . ' ' . $o['lname']); ?></strong>
                                                <span class="text-gray-400 text-[10px] block mt-0.5"><?php echo htmlspecialchars($o['email']); ?></span>
                                                <span class="text-gray-400 text-[10px] block"><?php echo htmlspecialchars($o['phone']); ?></span>
                                            </td>
                                            <td class="px-6 py-4 text-xs text-gray-600">
                                                <span><?php echo htmlspecialchars($o['address']); ?></span><br>
                                                <span class="text-[10px] text-gray-400 mt-1 block font-medium"><?php echo format_utc_datetime($o['created_at'], 'M d, Y - h:i A'); ?></span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="font-mono text-[10px] text-gray-500 block truncate w-24" title="<?php echo htmlspecialchars($o['transaction_uuid']); ?>"><?php echo htmlspecialchars($o['transaction_uuid']); ?></span>
                                                <span class="text-[10px] text-primary font-semibold mt-1 inline-block">Click row to view</span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <strong class="text-primary font-bold text-xs">Rs. <?php echo htmlspecialchars($o['total_amount']); ?></strong><br>
                                                <span class="text-[10px] text-gray-400">(<?php echo $o['items_count']; ?> items)</span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <?php $is_cod = ($o['payment_method'] === 'cod'); ?>
                                                <span class="inline-block px-2.5 py-0.5 border text-[9px] font-bold rounded-md uppercase tracking-wider <?php echo $is_cod ? 'bg-amber-50 text-amber-700 border-amber-100' : 'bg-emerald-50 text-emerald-700 border-emerald-100'; ?>">
                                                    <?php echo htmlspecialchars($is_cod ? 'COD' : 'eSewa'); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <?php
                                                    $payment_status = $o['payment_status'] ?? 'pending';
                                                    $payment_badge = 'bg-amber-50 text-amber-700 border-amber-100';
                                                    if ($payment_status === 'paid') $payment_badge = 'bg-emerald-50 text-emerald-700 border-emerald-100';
                                                    elseif ($payment_status === 'failed') $payment_badge = 'bg-rose-50 text-rose-700 border-rose-100';
                                                    elseif ($payment_status === 'cancelled') $payment_badge = 'bg-slate-100 text-slate-600 border-slate-200';
                                                ?>
                                                <span class="inline-block px-2.5 py-0.5 border text-[9px] font-bold rounded-md uppercase tracking-wider <?php echo $payment_badge; ?>">
                                                    <?php echo htmlspecialchars($payment_status); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <?php 
                                                    $status = $o['status'];
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
                                                <span class="inline-block px-2.5 py-0.5 border text-[9px] font-bold rounded-md uppercase tracking-wider <?php echo $badge_class; ?>">
                                                    <?php echo htmlspecialchars($status); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 4: ADMIN ORDER DETAILS VIEW (dynamic) -->
                <div id="tab-order-details" class="admin-tab-content bg-white border border-gray-100 rounded-3xl p-8 shadow-sm hidden space-y-6 animate-fade-in">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                        <div>
                            <button onclick="switchTab('tab-orders')" class="text-xs font-bold text-primary hover:text-primary-dark flex items-center gap-1.5 transition-colors cursor-pointer bg-transparent border-none p-0">
                                &larr; Back to Order List
                            </button>
                            <h3 class="text-lg font-bold text-gray-850 mt-3" id="admin-detail-title">Order Details</h3>
                        </div>
                        <div class="flex items-center gap-2.5" id="admin-detail-actions">
                            <a href="#" id="admin-detail-invoice-link" target="_blank" class="bg-primary hover:bg-primary-dark text-white text-xs font-bold rounded-xl px-4 py-2 transition-colors flex items-center gap-1.5 shadow-sm hover:shadow-md hover:-translate-y-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                Print Invoice
                            </a>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Left side details -->
                        <div class="bg-gray-50 border border-gray-100 rounded-2xl p-6 space-y-4 text-xs md:text-sm">
                            <div>
                                <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Reference UUID</span>
                                <strong id="admin-detail-uuid" class="text-xs font-mono text-gray-700 block truncate"></strong>
                            </div>
                            <div>
                                <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Order Date</span>
                                <strong id="admin-detail-date" class="text-xs text-gray-700 block"></strong>
                            </div>
                            <div>
                                <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Customer Name</span>
                                <strong id="admin-detail-name" class="text-xs text-gray-700 block"></strong>
                            </div>
                            <div>
                                <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Contact Details</span>
                                <span id="admin-detail-contact" class="text-xs text-gray-500 block leading-relaxed"></span>
                            </div>
                            <div>
                                <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Delivery Address</span>
                                <strong id="admin-detail-address" class="text-xs text-gray-700 block leading-relaxed"></strong>
                            </div>
                            <div>
                                <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Payment Method</span>
                                <span id="admin-detail-method" class="inline-block px-2 py-0.5 border text-[9px] font-bold rounded-md uppercase tracking-wider"></span>
                            </div>
                            <div>
                                <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Payment Status</span>
                                <span id="admin-detail-payment-badge" class="inline-block px-2.5 py-0.5 border text-[9px] font-bold rounded-md uppercase tracking-wider"></span>
                            </div>
                            <div>
                                <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-2">Order Status</span>
                                <span id="admin-detail-status-badge" class="inline-block px-2.5 py-0.5 border text-[9px] font-bold rounded-md uppercase tracking-wider"></span>
                            </div>
                            
                            <div class="border-t border-gray-200 pt-4 text-[11px] text-gray-500" id="admin-detail-status-form-container">
                                Update order status from the action controls beside Print Invoice.
                            </div>
                        </div>
                        
                        <!-- Right side ordered items specs -->
                        <div class="lg:col-span-2 space-y-6">
                            <div class="border border-gray-100 rounded-2xl overflow-hidden shadow-inner bg-gray-50/20">
                                <table class="w-full text-left text-xs md:text-sm border-collapse">
                                    <thead>
                                        <tr class="bg-gray-50 border-b border-gray-100 text-[10px] font-bold uppercase tracking-wider text-gray-500">
                                            <th class="px-6 py-3 w-16">Item</th>
                                            <th class="px-6 py-3">Product Name</th>
                                            <th class="px-6 py-3 text-right">Unit Price</th>
                                            <th class="px-6 py-3 text-center w-20">Qty</th>
                                            <th class="px-6 py-3 text-right w-32">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody id="admin-detail-items-body" class="divide-y divide-gray-100 bg-white">
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="flex justify-end border-t border-gray-100 pt-4">
                                <div class="w-72 space-y-3.5 text-xs md:text-sm text-gray-500 font-semibold">
                                    <div class="flex justify-between">
                                        <span>Subtotal</span>
                                        <span id="admin-detail-subtotal" class="text-gray-700 font-bold font-mono"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Shipping Logistics</span>
                                        <span class="text-emerald-600 font-bold uppercase">Free</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Government VAT (13%)</span>
                                        <span class="text-emerald-600 font-bold uppercase">Included</span>
                                    </div>
                                    <div class="flex justify-between items-center border-t border-gray-200 pt-4 text-gray-850 font-black text-sm md:text-base">
                                        <span>Total Payable</span>
                                        <span id="admin-detail-total" class="text-primary text-base font-mono"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    <?php endif; ?>
    <script>
        // Tab toggler scripts
        const ADMIN_TAB_STORAGE_KEY = 'bagaicha_admin_active_tab';
        const PERSISTENT_TABS = new Set(['tab-dashboard', 'tab-products', 'tab-orders']);

        function switchTab(tabId) {
            // Remove active classes
            document.querySelectorAll('.sidebar-tab-btn').forEach(btn => {
                btn.className = "sidebar-tab-btn w-full px-5 py-3 text-left text-sm font-semibold text-white/70 rounded-xl flex items-center gap-3 hover:bg-white/5 hover:text-white transition-all duration-150 cursor-pointer";
            });
            document.querySelectorAll('.admin-tab-content').forEach(content => content.classList.add('hidden'));
            
            // Add active to selected button matching onclick attribute
            const targetBtn = document.querySelector(`[onclick="switchTab('${tabId}')"]`);
            if (targetBtn) {
                targetBtn.className = "sidebar-tab-btn w-full px-5 py-3 text-left text-sm font-bold text-white bg-primary rounded-xl flex items-center gap-3 shadow-md shadow-primary/20 transition-all duration-150 cursor-pointer active";
            }
            
            const targetContent = document.getElementById(tabId);
            if (targetContent) {
                targetContent.classList.remove('hidden');
            }

            // Persist only primary sidebar tabs.
            if (PERSISTENT_TABS.has(tabId)) {
                localStorage.setItem(ADMIN_TAB_STORAGE_KEY, tabId);
            } else if (tabId === 'tab-order-details') {
                // Details view is contextual; restore Orders tab on reload.
                localStorage.setItem(ADMIN_TAB_STORAGE_KEY, 'tab-orders');
            }
        }

        window.addEventListener('DOMContentLoaded', () => {
            const savedTab = localStorage.getItem(ADMIN_TAB_STORAGE_KEY);
            if (savedTab && PERSISTENT_TABS.has(savedTab) && document.getElementById(savedTab)) {
                switchTab(savedTab);
            } else {
                switchTab('tab-dashboard');
            }
        });

        function filterAdminProducts() {
            const search = (document.getElementById('admin-product-search')?.value || '').trim().toLowerCase();
            const priceBand = document.getElementById('admin-product-price-filter')?.value || 'all';
            const discountType = document.getElementById('admin-product-discount-filter')?.value || 'all';
            const sort = document.getElementById('admin-product-sort')?.value || 'default';
            const tbody = document.getElementById('admin-products-tbody');
            if (!tbody) return;

            const rows = Array.from(tbody.querySelectorAll('.admin-product-row'));
            let visible = 0;

            rows.sort((a, b) => {
                if (sort === 'name-asc') return (a.dataset.name || '').localeCompare(b.dataset.name || '');
                if (sort === 'price-asc') return parseFloat(a.dataset.price || '0') - parseFloat(b.dataset.price || '0');
                if (sort === 'price-desc') return parseFloat(b.dataset.price || '0') - parseFloat(a.dataset.price || '0');
                return 0;
            }).forEach(row => tbody.appendChild(row));

            rows.forEach(row => {
                const name = row.dataset.name || '';
                const price = parseFloat(row.dataset.price || '0');
                const matchesSearch = !search || name.includes(search);

                let matchesPrice = true;
                if (priceBand === '0-3000') matchesPrice = price <= 3000;
                else if (priceBand === '3000-7000') matchesPrice = price > 3000 && price <= 7000;
                else if (priceBand === '7000+') matchesPrice = price > 7000;

                const matchesDiscount = discountType === 'all'
                    || (discountType === 'discounted' && (row.dataset.discount || '0') === '1')
                    || (discountType === 'regular' && (row.dataset.discount || '0') === '0');

                const show = matchesSearch && matchesPrice && matchesDiscount;
                row.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            let emptyRow = document.getElementById('admin-products-empty-filter-row');
            if (!emptyRow) {
                emptyRow = document.createElement('tr');
                emptyRow.id = 'admin-products-empty-filter-row';
                emptyRow.innerHTML = '<td colspan="6" class="text-center text-gray-400 py-10 text-xs">No products match current filters.</td>';
                emptyRow.style.display = 'none';
                tbody.appendChild(emptyRow);
            }
            emptyRow.style.display = visible === 0 ? '' : 'none';
        }

        function filterAdminOrders() {
            const search = (document.getElementById('admin-order-search')?.value || '').trim().toLowerCase();
            const method = document.getElementById('admin-order-method-filter')?.value || 'all';
            const payment = document.getElementById('admin-order-payment-filter')?.value || 'all';
            const status = document.getElementById('admin-order-status-filter')?.value || 'all';
            const sort = document.getElementById('admin-order-sort')?.value || 'latest';
            const tbody = document.getElementById('admin-orders-tbody');
            if (!tbody) return;

            const rows = Array.from(tbody.querySelectorAll('.admin-order-row'));
            let visible = 0;

            rows.sort((a, b) => {
                if (sort === 'oldest') return Number(a.dataset.created || 0) - Number(b.dataset.created || 0);
                if (sort === 'amount-desc') return Number(b.dataset.total || 0) - Number(a.dataset.total || 0);
                if (sort === 'amount-asc') return Number(a.dataset.total || 0) - Number(b.dataset.total || 0);
                if (sort === 'customer-asc') return (a.dataset.customer || '').localeCompare(b.dataset.customer || '');
                return Number(b.dataset.created || 0) - Number(a.dataset.created || 0);
            }).forEach(row => tbody.appendChild(row));

            rows.forEach(row => {
                const searchable = `${row.dataset.customer || ''} ${row.dataset.email || ''} ${row.dataset.reference || ''}`;
                const matchesSearch = !search || searchable.includes(search);
                const matchesMethod = method === 'all' || (row.dataset.method || '') === method;
                const matchesPayment = payment === 'all' || (row.dataset.payment || '') === payment;
                const matchesStatus = status === 'all' || (row.dataset.status || '') === status;
                const show = matchesSearch && matchesMethod && matchesPayment && matchesStatus;
                row.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            let emptyRow = document.getElementById('admin-orders-empty-filter-row');
            if (!emptyRow) {
                emptyRow = document.createElement('tr');
                emptyRow.id = 'admin-orders-empty-filter-row';
                emptyRow.innerHTML = '<td colspan="7" class="text-center text-gray-400 py-10 text-xs">No orders match current filters.</td>';
                emptyRow.style.display = 'none';
                tbody.appendChild(emptyRow);
            }
            emptyRow.style.display = visible === 0 ? '' : 'none';
        }

        window.addEventListener('DOMContentLoaded', () => {
            const productSearch = document.getElementById('admin-product-search');
            const productPrice = document.getElementById('admin-product-price-filter');
            const productDiscount = document.getElementById('admin-product-discount-filter');
            const productSort = document.getElementById('admin-product-sort');
            const orderSearch = document.getElementById('admin-order-search');
            const orderMethod = document.getElementById('admin-order-method-filter');
            const orderPayment = document.getElementById('admin-order-payment-filter');
            const orderStatus = document.getElementById('admin-order-status-filter');
            const orderSort = document.getElementById('admin-order-sort');

            [productSearch, productPrice, productDiscount, productSort].forEach(el => {
                if (!el) return;
                el.addEventListener('input', filterAdminProducts);
                el.addEventListener('change', filterAdminProducts);
            });

            [orderSearch, orderMethod, orderPayment, orderStatus, orderSort].forEach(el => {
                if (!el) return;
                el.addEventListener('input', filterAdminOrders);
                el.addEventListener('change', filterAdminOrders);
            });

            filterAdminProducts();
            filterAdminOrders();
        });

        // ── Product Modal ─────────────────────────────────────────────────
        function openProductModal() {
            // Reset the form so it is set to "Add" mode
            const form = document.getElementById('product-modal-form');
            if (form) {
                form.reset();
            }
            
            // Set action values for adding
            document.getElementById('modal_action_add').value = '1';
            document.getElementById('modal_action_update').value = '0';
            document.getElementById('modal_product_id').value = '';
            
            // Update modal text elements
            document.getElementById('modal-title-text').textContent = 'New Product';
            document.getElementById('modal-subtitle-text').textContent = 'Add a new bonsai to the catalog';
            document.getElementById('modal-submit-btn').textContent = 'Save Product to Catalog';
            
            // Clear preview
            document.getElementById('modal-img-preview').classList.add('hidden');
            document.getElementById('modal-preview-img').src = '';
            
            const modal = document.getElementById('add-product-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            // Prevent body scroll while modal is open
            document.body.style.overflow = 'hidden';
        }

        function openEditProductModal(product) {
            // Reset form first
            const form = document.getElementById('product-modal-form');
            if (form) {
                form.reset();
            }
            
            // Set action values for updating
            document.getElementById('modal_action_add').value = '0';
            document.getElementById('modal_action_update').value = '1';
            document.getElementById('modal_product_id').value = product.id;
            
            // Fill form inputs
            form.elements['name'].value = product.name || '';
            form.elements['price'].value = product.price || '';
            form.elements['discount_price'].value = product.discount_price || '';
            form.elements['image_url'].value = product.image_url || '';
            form.elements['description'].value = product.description || '';
            
            // Update modal text elements
            document.getElementById('modal-title-text').textContent = 'Edit Product';
            document.getElementById('modal-subtitle-text').textContent = 'Update catalog details for this item';
            document.getElementById('modal-submit-btn').textContent = 'Save Changes';
            
            // Show image preview if we have an image
            if (product.image_url) {
                previewModalImage(product.image_url);
            } else {
                document.getElementById('modal-img-preview').classList.add('hidden');
                document.getElementById('modal-preview-img').src = '';
            }
            
            const modal = document.getElementById('add-product-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            // Prevent body scroll while modal is open
            document.body.style.overflow = 'hidden';
        }

        function closeProductModal() {
            const modal = document.getElementById('add-product-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
            // Clear preview
            document.getElementById('modal-img-preview').classList.add('hidden');
            document.getElementById('modal-preview-img').src = '';
        }

        // Close when clicking the dark backdrop
        document.getElementById('add-product-modal').addEventListener('click', function(e) {
            if (e.target === this) closeProductModal();
        });

        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeProductModal();
        });

        // Live image preview from URL input
        function previewModalImage(url) {
            const preview = document.getElementById('modal-img-preview');
            const img     = document.getElementById('modal-preview-img');
            if (url && url.length > 8) {
                img.src = url;
                img.onerror = () => { preview.classList.add('hidden'); };
                img.onload  = () => { preview.classList.remove('hidden'); };
            } else {
                preview.classList.add('hidden');
            }
        }

        // Live image preview from file upload
        function previewModalImageFile(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('modal-img-preview');
                    const img     = document.getElementById('modal-preview-img');
                    img.src = e.target.result;
                    preview.classList.remove('hidden');
                    // Clear the URL field since file takes priority
                    document.getElementById('modal_image_url').value = '';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Global orders array to power in-panel dynamic order details view
        const adminOrdersData = <?php echo json_encode($orders); ?>;

        function showAdminOrderDetails(orderId) {
            const order = adminOrdersData.find(o => parseInt(o.id) === parseInt(orderId));
            if (!order) return;

            document.getElementById('admin-detail-title').textContent = `Order Details #${order.transaction_uuid.substring(0, 8).toUpperCase()}`;
            document.getElementById('admin-detail-uuid').textContent = order.transaction_uuid;
            document.getElementById('admin-detail-uuid').title = order.transaction_uuid;
            
            const utcIso = (order.created_at || '').replace(' ', 'T') + 'Z';
            const dateObj = new Date(utcIso);
            document.getElementById('admin-detail-date').textContent = new Intl.DateTimeFormat('en-US', {
                timeZone: 'Asia/Kathmandu',
                month: 'short', day: '2-digit', year: 'numeric',
                hour: '2-digit', minute: '2-digit', hour12: true
            }).format(dateObj);

            document.getElementById('admin-detail-name').textContent = `${order.fname} ${order.lname}`;
            document.getElementById('admin-detail-contact').innerHTML = `Email: ${order.email}<br>Phone: ${order.phone}`;
            document.getElementById('admin-detail-address').textContent = order.address;
            
            const isCod = (order.payment_method === 'cod');
            const methodEl = document.getElementById('admin-detail-method');
            methodEl.textContent = isCod ? 'COD' : 'eSewa';
            methodEl.className = isCod 
                ? 'inline-block px-2 py-0.5 border text-[9px] font-bold rounded-md uppercase tracking-wider bg-amber-50 text-amber-700 border-amber-100'
                : 'inline-block px-2 py-0.5 border text-[9px] font-bold rounded-md uppercase tracking-wider bg-emerald-50 text-emerald-700 border-emerald-100';

            const paymentBadge = document.getElementById('admin-detail-payment-badge');
            const paymentStatus = (order.payment_status || 'pending').toLowerCase();
            paymentBadge.textContent = paymentStatus;
            let paymentBadgeClass = 'bg-amber-50 text-amber-700 border-amber-100';
            if (paymentStatus === 'paid') paymentBadgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-100';
            else if (paymentStatus === 'failed') paymentBadgeClass = 'bg-rose-50 text-rose-700 border-rose-100';
            else if (paymentStatus === 'cancelled') paymentBadgeClass = 'bg-slate-100 text-slate-600 border-slate-200';
            paymentBadge.className = `inline-block px-2.5 py-0.5 border text-[9px] font-bold rounded-md uppercase tracking-wider ${paymentBadgeClass}`;

            const statusBadge = document.getElementById('admin-detail-status-badge');
            statusBadge.textContent = order.status;
            let badgeClass = 'bg-amber-50 text-amber-700 border-amber-100';
            if (order.status === 'completed') badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-100';
            else if (order.status === 'failed') badgeClass = 'bg-rose-50 text-rose-700 border-rose-100';
            else if (order.status === 'cancelled') badgeClass = 'bg-slate-100 text-slate-600 border-slate-200';
            statusBadge.className = `inline-block px-2.5 py-0.5 border text-[9px] font-bold rounded-md uppercase tracking-wider ${badgeClass}`;

            document.getElementById('admin-detail-invoice-link').href = `/invoice.php?uuid=${encodeURIComponent(order.transaction_uuid)}`;

            const itemsBody = document.getElementById('admin-detail-items-body');
            itemsBody.innerHTML = '';
            
            order.items.forEach(item => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-gray-50/50 transition-colors';
                tr.innerHTML = `
                    <td class="px-6 py-3">
                        <div class="w-10 h-10 rounded-lg border border-gray-100 overflow-hidden shrink-0">
                            <img src="${item.image_url}" alt="thumbnail" class="w-full h-full object-cover">
                        </div>
                    </td>
                    <td class="px-6 py-3">
                        <strong class="text-gray-800 font-bold block text-xs md:text-sm">${item.product_name}</strong>
                    </td>
                    <td class="px-6 py-3 text-right font-mono text-xs">
                        Rs. ${parseFloat(item.price).toFixed(2)}
                    </td>
                    <td class="px-6 py-3 text-center font-mono font-bold text-gray-650 text-xs">
                        ${item.quantity}
                    </td>
                    <td class="px-6 py-3 text-right font-mono font-bold text-gray-800 text-xs">
                        Rs. ${(parseFloat(item.price) * parseInt(item.quantity)).toFixed(2)}
                    </td>
                `;
                itemsBody.appendChild(tr);
            });

            const totalVal = `Rs. ${parseFloat(order.total_amount).toFixed(2)}`;
            document.getElementById('admin-detail-subtotal').textContent = totalVal;
            document.getElementById('admin-detail-total').textContent = totalVal;

            const formContainer = document.getElementById('admin-detail-status-form-container');
            const actionsContainer = document.getElementById('admin-detail-actions');

            if (!actionsContainer) return;

            const existingStatusActions = document.getElementById('admin-detail-status-actions');
            if (existingStatusActions) existingStatusActions.remove();

            if (order.status === 'completed' || order.status === 'failed' || order.status === 'cancelled') {
                formContainer.innerHTML = `
                    <span class="text-xs text-gray-400 font-semibold italic flex items-center gap-1 select-none">
                        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        Finalized state. No further transitions allowed.
                    </span>
                `;
            } else {
                formContainer.innerHTML = `
                    <span class="text-[11px] text-gray-500">Choose a new order status from the dropdown beside Print Invoice.</span>
                `;

                const statusActions = document.createElement('div');
                statusActions.id = 'admin-detail-status-actions';
                statusActions.className = 'flex items-center gap-2';

                const transitionTargets = order.status === 'pending'
                    ? ['processing', 'completed', 'failed']
                    : ['completed', 'failed'];

                const optionsHtml = transitionTargets
                    .map(s => `<option value="${s}">${s.charAt(0).toUpperCase() + s.slice(1)}</option>`)
                    .join('');

                statusActions.innerHTML = `
                    <form action="/admin.php" method="POST" class="m-0 flex gap-2 items-center" data-confirm-message="Apply this status change to the order?">
                        <input type="hidden" name="update_order_status" value="1">
                        <input type="hidden" name="order_id" value="${order.id}">
                        <div class="relative">
                            <select
                                name="status"
                                class="appearance-none bg-white border border-gray-200 hover:border-primary/50 focus:border-primary focus:ring-2 focus:ring-primary/15 focus:outline-none rounded-xl pl-3 pr-8 py-2 text-xs font-semibold text-gray-700 cursor-pointer min-w-[9rem] shadow-sm transition-all"
                                onchange="if(this.value){ this.form.requestSubmit(); }"
                            >
                            <option value="" selected disabled>Update status</option>
                            ${optionsHtml}
                            </select>
                            <span class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </span>
                        </div>
                    </form>
                `;

                if (order.status === 'pending' || order.status === 'processing') {
                    const cancelForm = document.createElement('form');
                    cancelForm.action = '/admin.php';
                    cancelForm.method = 'POST';
                    cancelForm.className = 'm-0';
                    cancelForm.setAttribute('data-confirm-message', 'Cancel this order? This action cannot be undone.');
                    cancelForm.innerHTML = `
                        <input type="hidden" name="update_order_status" value="1">
                        <input type="hidden" name="order_id" value="${order.id}">
                        <input type="hidden" name="status" value="cancelled">
                        <button type="submit" class="bg-rose-50 hover:bg-rose-600 border border-rose-200 hover:border-rose-600 text-rose-600 hover:text-white font-bold rounded-xl px-3 py-2 text-xs transition-colors cursor-pointer">Cancel Order</button>
                    `;
                    statusActions.appendChild(cancelForm);
                }

                actionsContainer.appendChild(statusActions);
            }

            switchTab('tab-order-details');
        }

        <?php if (!empty($message) && $message_type === 'error'): ?>
            <?php if (isset($_POST['add_product']) && $_POST['add_product'] == '1'): ?>
                window.addEventListener('DOMContentLoaded', () => {
                    openProductModal();
                    // Prepopulate with submitted values
                    const form = document.getElementById('product-modal-form');
                    form.elements['name'].value = <?php echo json_encode($_POST['name'] ?? ''); ?>;
                    form.elements['price'].value = <?php echo json_encode($_POST['price'] ?? ''); ?>;
                    form.elements['discount_price'].value = <?php echo json_encode($_POST['discount_price'] ?? ''); ?>;
                    form.elements['image_url'].value = <?php echo json_encode($_POST['image_url'] ?? ''); ?>;
                    form.elements['description'].value = <?php echo json_encode($_POST['description'] ?? ''); ?>;
                    <?php if (!empty($_POST['image_url'])): ?>
                    previewModalImage(<?php echo json_encode($_POST['image_url']); ?>);
                    <?php endif; ?>
                });
            <?php elseif (isset($_POST['update_product']) && $_POST['update_product'] == '1'): ?>
                window.addEventListener('DOMContentLoaded', () => {
                    const productObj = {
                        id: <?php echo json_encode($_POST['product_id'] ?? ''); ?>,
                        name: <?php echo json_encode($_POST['name'] ?? ''); ?>,
                        price: <?php echo json_encode($_POST['price'] ?? ''); ?>,
                        discount_price: <?php echo json_encode($_POST['discount_price'] ?? ''); ?>,
                        image_url: <?php echo json_encode($_POST['image_url'] ?? ''); ?>,
                        description: <?php echo json_encode($_POST['description'] ?? ''); ?>
                    };
                    openEditProductModal(productObj);
                });
            <?php endif; ?>
        <?php endif; ?>
    </script>
</body>

</html>

