<?php
/**
 * Shared Dynamic Header Component
 */
$current_page = basename($_SERVER['PHP_SELF']);
$fname = $_SESSION['fname'] ?? '';
$email = $_SESSION['email'] ?? '';
?>
<section id="top"></section>

<section id="top-navbar" class="bg-brand-dark text-white/95 text-xs py-2.5 px-6 md:px-12 flex justify-between items-center transition-all duration-200">
    <div class="nav-menu flex items-center justify-between w-full">
        <ul class="flex items-center gap-6">
            <li><a href="#" class="hover:text-primary-light transition-colors duration-150 font-medium">Call us: +977 9819284721</a></li>
        </ul>
        <ul class="flex items-center gap-6">
            <?php if (!empty($email)): ?>
                <li><a href="<?php echo url('profile.php'); ?>" class="text-primary-light font-semibold hover:text-white transition-colors">Welcome, <?php echo htmlspecialchars($fname); ?></a></li>
                <?php if ($email === 'admin@bagaicha.com'): ?>
                    <li><a href="<?php echo url('admin.php'); ?>" class="text-purple-300 font-semibold hover:text-white transition-colors">Admin Portal</a></li>
                <?php endif; ?>
                <li><a href="<?php echo url('logout.php'); ?>" class="hover:text-red-400 transition-colors">Logout</a></li>
            <?php else: ?>
                <li><a href="<?php echo url('login.php'); ?>" class="hover:text-primary-light transition-colors">Login</a></li>
                <li><a href="<?php echo url('register.php'); ?>" class="hover:text-primary-light transition-colors">Register</a></li>
            <?php endif; ?>
        </ul>
    </div>
</section>

<nav class="navbar sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-gray-100 shadow-sm flex items-center justify-between px-6 md:px-12 h-20 transition-all duration-200">
    <a class="navbar-brand text-2xl font-extrabold text-brand-dark tracking-tight hover:opacity-90 transition-opacity" href="<?php echo url('index.php'); ?>"><span class="text-primary">B</span>agaicha</a>

    <div class="nav-content hidden md:flex items-center gap-8">
        <ul class="navbar-nav flex items-center gap-1.5">
            <li class="nav-item animate-fade-in">
                <a class="nav-link text-sm font-medium transition-all px-4 py-2 rounded-full <?php echo ($current_page === 'index.php') ? 'text-primary bg-primary-light font-semibold' : 'text-gray-600 hover:text-primary hover:bg-purple-50'; ?>" href="<?php echo url('index.php'); ?>">Home</a>
            </li>
            <li class="nav-item animate-fade-in">
                <a class="nav-link text-sm font-medium transition-all px-4 py-2 rounded-full <?php echo ($current_page === 'shop.php' || $current_page === 'product.php') ? 'text-primary bg-primary-light font-semibold' : 'text-gray-600 hover:text-primary hover:bg-purple-50'; ?>" href="<?php echo url('shop.php'); ?>">Shop</a>
            </li>
            <li class="nav-item animate-fade-in">
                <a class="nav-link text-sm font-medium transition-all px-4 py-2 rounded-full <?php echo ($current_page === 'about.php') ? 'text-primary bg-primary-light font-semibold' : 'text-gray-600 hover:text-primary hover:bg-purple-50'; ?>" href="<?php echo url('about.php'); ?>">About</a>
            </li>
            <li class="nav-item animate-fade-in">
                <a class="nav-link text-sm font-medium transition-all px-4 py-2 rounded-full <?php echo ($current_page === 'contact.php') ? 'text-primary bg-primary-light font-semibold' : 'text-gray-600 hover:text-primary hover:bg-purple-50'; ?>" href="<?php echo url('contact.php'); ?>">Contact</a>
            </li>
        </ul>
    </div>

    <div class="user-btn flex items-center gap-3">
        <button type="button" class="cart-btn p-2.5 bg-gray-50 hover:bg-purple-50 text-gray-700 hover:text-primary rounded-xl transition-all border border-gray-100 flex items-center justify-center relative cursor-pointer" id="showcart-btn">
            <img class="icon w-5 h-5" src="<?php echo asset('img/misc/cart.svg'); ?>" alt="Cart">
        </button>
        <button type="button" class="user-btn p-2.5 bg-gray-50 hover:bg-purple-50 text-gray-700 hover:text-primary rounded-xl transition-all border border-gray-100 flex items-center justify-center relative cursor-pointer" id="user-btn">
            <img class="icon w-5 h-5" src="<?php echo asset('img/misc/user.png'); ?>" alt="Account">
        </button>
    </div>
</nav>

<div id="cart" class="cart-popup hidden fixed inset-0 z-[9999] bg-black/60 backdrop-blur-sm items-center justify-center p-4" aria-hidden="true" role="dialog" aria-label="Shopping cart">
    <div class="cart-popup-content bg-white w-full max-w-md rounded-2xl shadow-2xl flex flex-col overflow-hidden transform scale-100 transition-all duration-300">
        <div class="cart-popup-header flex justify-between items-center px-6 py-5 border-b border-gray-100 bg-gray-50/50">
            <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                Shopping Cart
            </h3>
            <button class="p-1.5 hover:bg-gray-100 text-gray-400 hover:text-gray-600 rounded-lg transition-colors cursor-pointer" id="cart-close" type="button">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="cart-popup-body overflow-y-auto max-h-[60vh] p-6"></div>
    </div>
</div>
