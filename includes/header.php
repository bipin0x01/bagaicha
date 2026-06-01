<?php
/**
 * Shared Dynamic Header Component
 */
$current_page = basename($_SERVER['PHP_SELF']);
$fname = $_SESSION['user_fname'] ?? '';
$email = $_SESSION['user_email'] ?? '';
$account_label = !empty($fname) ? $fname : 'Log In';
$account_target = !empty($email) ? url('profile.php') : url('login.php');
?>
<section id="top"></section>

<nav class="navbar sticky top-0 z-50 bg-white border-b border-gray-100 flex items-center justify-between px-6 md:px-12 h-20">
    <a class="navbar-brand text-2xl font-extrabold text-brand-dark tracking-tight hover:opacity-90 transition-opacity" href="<?php echo url('index.php'); ?>"><span class="text-primary">B</span>agaicha</a>

    <div class="nav-content hidden md:flex items-center gap-8">
        <ul class="navbar-nav flex items-center gap-6">
            <li class="nav-item">
                <a class="nav-link text-sm font-medium transition-colors pb-1 border-b-2 <?php echo ($current_page === 'index.php') ? 'text-primary border-primary' : 'text-gray-600 border-transparent hover:text-primary'; ?>" href="<?php echo url('index.php'); ?>">Home</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-sm font-medium transition-colors pb-1 border-b-2 <?php echo ($current_page === 'shop.php' || $current_page === 'product.php') ? 'text-primary border-primary' : 'text-gray-600 border-transparent hover:text-primary'; ?>" href="<?php echo url('shop.php'); ?>">Shop</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-sm font-medium transition-colors pb-1 border-b-2 <?php echo ($current_page === 'about.php') ? 'text-primary border-primary' : 'text-gray-600 border-transparent hover:text-primary'; ?>" href="<?php echo url('about.php'); ?>">About</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-sm font-medium transition-colors pb-1 border-b-2 <?php echo ($current_page === 'contact.php') ? 'text-primary border-primary' : 'text-gray-600 border-transparent hover:text-primary'; ?>" href="<?php echo url('contact.php'); ?>">Contact</a>
            </li>
        </ul>
    </div>

    <div class="user-btn relative flex items-center gap-3">
        <button type="button" class="cart-btn p-2.5 bg-white hover:bg-gray-50 text-gray-700 hover:text-primary rounded-lg transition-colors border border-gray-200 flex items-center justify-center relative cursor-pointer" id="showcart-btn">
            <img class="icon w-5 h-5" src="<?php echo asset('img/misc/cart.svg'); ?>" alt="Cart">
        </button>
        <?php if (!empty($email)): ?>
            <button type="button" class="user-btn px-3 py-2 bg-white hover:bg-gray-50 text-gray-700 hover:text-primary rounded-lg transition-colors border border-gray-200 flex items-center gap-2 cursor-pointer text-xs font-semibold max-w-[200px]" id="user-btn" data-dropdown="true">
                <img class="w-5 h-5 rounded-full border border-gray-200 object-cover" src="<?php echo asset('img/misc/user.png'); ?>" alt="Account">
                <span class="truncate"><?php echo htmlspecialchars($account_label); ?></span>
                <svg id="user-menu-chevron" class="w-3.5 h-3.5 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>

            <div id="user-menu" class="hidden absolute right-0 top-[calc(100%+8px)] w-64 bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden z-50">
                <div class="px-4 py-4 border-b border-gray-100 bg-white">
                    <p class="text-[11px] text-gray-400 font-medium">Signed in as</p>
                    <p class="text-sm font-bold text-gray-800 truncate"><?php echo htmlspecialchars($account_label); ?></p>
                    <p class="text-xs text-gray-500 truncate mt-1"><?php echo htmlspecialchars($email); ?></p>
                </div>
                <div class="py-1.5">
                    <a href="<?php echo url('profile.php'); ?>" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">My Account</a>
                    <a href="<?php echo url('profile.php'); ?>#orders" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">Orders</a>
                    <button type="button" id="user-menu-cart" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">Cart</button>
                </div>
                <div class="border-t border-gray-100">
                    <a href="<?php echo url('logout.php'); ?>" class="block px-4 py-2.5 text-sm text-rose-600 hover:bg-rose-50" data-confirm-logout="true">Log out</a>
                </div>
            </div>
        <?php else: ?>
            <button type="button" class="user-btn px-4 py-2.5 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors border border-primary flex items-center justify-center relative cursor-pointer text-xs font-bold" id="user-btn" data-target="<?php echo htmlspecialchars($account_target); ?>">
                <?php echo htmlspecialchars($account_label); ?>
            </button>
        <?php endif; ?>
    </div>
</nav>

<div id="cart" class="cart-popup hidden fixed inset-0 z-[9999] bg-black/50 items-center justify-center p-4" aria-hidden="true" role="dialog" aria-label="Shopping cart">
    <div class="cart-popup-content bg-white w-full max-w-md rounded-xl flex flex-col overflow-hidden">
        <div class="cart-popup-header flex justify-between items-center px-6 py-4 border-b border-gray-100 bg-white">
            <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                Shopping Cart
            </h3>
            <button class="p-1.5 hover:bg-gray-100 text-gray-400 hover:text-gray-600 rounded-lg transition-colors cursor-pointer" id="cart-close" type="button">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="cart-popup-body overflow-y-auto max-h-[60vh] p-6"></div>
    </div>
</div>
