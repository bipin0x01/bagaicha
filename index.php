<?php
/**
 * Bagaicha - Dynamic Home Page
 * Fetches latest and most selling products dynamically from SQLite database.
 */
require_once 'db.php';
session_start();

// Fetch Latest Products (4 newest items)
$latest_products = [];
$latest_query = $db->query("SELECT * FROM products ORDER BY id DESC LIMIT 4");
if ($latest_query) {
    while ($row = $latest_query->fetchArray(SQLITE3_ASSOC)) {
        $latest_products[] = $row;
    }
}

// Fetch Most Selling Products (first 4 items)
$most_selling = [];
$selling_query = $db->query("SELECT * FROM products ORDER BY id ASC LIMIT 4");
if ($selling_query) {
    while ($row = $selling_query->fetchArray(SQLITE3_ASSOC)) {
        $most_selling[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php 
    $page_title = "Bagaicha — Premium Bonsai from Nepal";
    $page_description = "Bagaicha — Premium handcrafted bonsai trees from Nepal. Shop our curated collection of living art, grown by expert arborists and delivered to your doorstep.";
    require_once 'assets/partials/_head.php'; 
    ?>
</head>

<body class="bg-gray-50/30 text-gray-800 antialiased font-sans">
    <!-- Header -->
    <?php require_once 'header.php'; ?>

    <!-- Hero Banner -->
    <div class="relative bg-cover bg-center min-h-[85vh] flex items-center" style="background-image: url('./assets/img/misc/hero-image.jpg');">
        <!-- Overlay -->
        <div class="absolute inset-0 bg-gradient-to-r from-black/85 via-black/50 to-transparent"></div>
        
        <div class="relative max-w-7xl mx-auto px-6 md:px-12 w-full py-20 flex justify-start z-10">
            <div class="max-w-xl text-white animate-fade-in">
                <span class="inline-block text-[10px] font-extrabold uppercase tracking-widest text-primary-light bg-primary/30 border border-primary-light/20 rounded-full px-3.5 py-1 mb-6">Handcrafted in Nepal</span>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight mb-4">Where Nature<br>Meets Living Art</h1>
                <p class="text-sm md:text-base text-gray-300 leading-relaxed mb-8">Bringing the serenity of ancient forests into your home — premium bonsai, grown with care, shipped across Nepal.</p>
                <a href="./shop.php" class="inline-block bg-primary hover:bg-primary-dark text-white font-bold rounded-xl px-7 py-3.5 transition-all duration-200 shadow-lg hover:shadow-primary/30 hover:-translate-y-0.5">Explore Collection</a>
            </div>
        </div>
    </div>

    <!-- Value Strip -->
    <div class="grid grid-cols-2 md:grid-cols-4 divide-y md:divide-y-0 md:divide-x divide-white/10 bg-primary py-8 px-6 md:px-12 border-b border-white/5 shadow-sm">
        <div class="py-4 md:py-2 px-4 flex flex-col justify-center items-center text-center border-b border-white/10 md:border-b-0">
            <div class="font-bold text-sm text-white mb-0.5 tracking-wide">Free Delivery</div>
            <div class="text-xs text-purple-200">On orders above Rs. 1,000</div>
        </div>
        <div class="py-4 md:py-2 px-4 flex flex-col justify-center items-center text-center border-b border-white/10 md:border-b-0">
            <div class="font-bold text-sm text-white mb-0.5 tracking-wide">Expert Grown</div>
            <div class="text-xs text-purple-200">By certified arborists</div>
        </div>
        <div class="py-4 md:py-2 px-4 flex flex-col justify-center items-center text-center border-b border-white/10 md:border-b-0">
            <div class="font-bold text-sm text-white mb-0.5 tracking-wide">7-Day Returns</div>
            <div class="text-xs text-purple-200">Hassle-free guarantee</div>
        </div>
        <div class="py-4 md:py-2 px-4 flex flex-col justify-center items-center text-center">
            <div class="font-bold text-sm text-white mb-0.5 tracking-wide">Secure Payment</div>
            <div class="text-xs text-purple-200">eSewa &amp; Cash on Delivery</div>
        </div>
    </div>

    <!-- Latest Products Section -->
    <section id="latest-products" class="py-20 md:py-24 px-6 md:px-12 bg-white">
        <div class="max-w-7xl mx-auto">
            <div class="text-center md:text-left mb-12">
                <span class="text-[10px] font-bold uppercase tracking-widest text-primary bg-primary-light px-3.5 py-1.5 rounded-full inline-block mb-3">Fresh Arrivals</span>
                <h2 class="text-3xl md:text-4xl font-extrabold text-brand-dark">Latest Bonsais</h2>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 md:gap-8">
                <?php foreach ($latest_products as $p): ?>
                <div class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col hover:-translate-y-1">
                    <!-- Image Area -->
                    <div class="relative aspect-square overflow-hidden bg-gray-50">
                        <a href="product.php?id=<?php echo $p['id']; ?>" class="block w-full h-full">
                            <img src="<?php echo htmlspecialchars($p['image_url']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        </a>
                        <?php if (!empty($p['discount_price'])): ?>
                        <span class="absolute top-3 left-3 bg-red-500 text-white text-[9px] font-extrabold px-2 py-1 rounded-md uppercase tracking-wider">Sale</span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Info Area -->
                    <div class="p-5 flex-1 flex flex-col">
                        <h3 class="text-sm font-bold text-gray-800 hover:text-primary transition-colors line-clamp-1 mb-1.5">
                            <a href="product.php?id=<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?></a>
                        </h3>
                        <div class="flex items-baseline gap-2 mt-auto">
                            <span class="text-sm font-extrabold text-primary">Rs. <?php echo htmlspecialchars($p['price']); ?></span>
                            <?php if (!empty($p['discount_price'])): ?>
                            <span class="text-[11px] text-gray-400 line-through">Rs. <?php echo htmlspecialchars($p['discount_price']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="grid grid-cols-2 border-t border-gray-100 bg-gray-50/50 divide-x divide-gray-100">
                        <button class="btn-buy py-3 text-center text-xs font-semibold text-gray-600 hover:text-primary hover:bg-purple-50/50 transition-colors cursor-pointer" type="button" data-id="<?php echo $p['id']; ?>" data-name="<?php echo htmlspecialchars($p['name']); ?>" data-price="<?php echo htmlspecialchars($p['price']); ?>" data-img="<?php echo htmlspecialchars($p['image_url']); ?>">Add to Cart</button>
                        <button class="btn-buy py-3 text-center text-xs font-semibold text-gray-700 hover:text-white hover:bg-primary transition-colors cursor-pointer" type="button" data-id="<?php echo $p['id']; ?>" data-name="<?php echo htmlspecialchars($p['name']); ?>" data-price="<?php echo htmlspecialchars($p['price']); ?>" data-img="<?php echo htmlspecialchars($p['image_url']); ?>">Buy Now</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Most Selling Products Section -->
    <section id="most-selling" class="py-20 md:py-24 px-6 md:px-12 bg-gray-50/50">
        <div class="max-w-7xl mx-auto">
            <div class="text-center md:text-left mb-12">
                <span class="text-[10px] font-bold uppercase tracking-widest text-primary bg-primary-light px-3.5 py-1.5 rounded-full inline-block mb-3">Staff Picks</span>
                <h2 class="text-3xl md:text-4xl font-extrabold text-brand-dark">Most Popular</h2>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 md:gap-8">
                <?php foreach ($most_selling as $p): ?>
                <div class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col hover:-translate-y-1">
                    <!-- Image Area -->
                    <div class="relative aspect-square overflow-hidden bg-gray-50">
                        <a href="product.php?id=<?php echo $p['id']; ?>" class="block w-full h-full">
                            <img src="<?php echo htmlspecialchars($p['image_url']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        </a>
                        <?php if (!empty($p['discount_price'])): ?>
                        <span class="absolute top-3 left-3 bg-red-500 text-white text-[9px] font-extrabold px-2 py-1 rounded-md uppercase tracking-wider">Sale</span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Info Area -->
                    <div class="p-5 flex-1 flex flex-col">
                        <h3 class="text-sm font-bold text-gray-800 hover:text-primary transition-colors line-clamp-1 mb-1.5">
                            <a href="product.php?id=<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?></a>
                        </h3>
                        <div class="flex items-baseline gap-2 mt-auto">
                            <span class="text-sm font-extrabold text-primary">Rs. <?php echo htmlspecialchars($p['price']); ?></span>
                            <?php if (!empty($p['discount_price'])): ?>
                            <span class="text-[11px] text-gray-400 line-through">Rs. <?php echo htmlspecialchars($p['discount_price']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="grid grid-cols-2 border-t border-gray-100 bg-gray-50/50 divide-x divide-gray-100">
                        <button class="btn-buy py-3 text-center text-xs font-semibold text-gray-600 hover:text-primary hover:bg-purple-50/50 transition-colors cursor-pointer" type="button" data-id="<?php echo $p['id']; ?>" data-name="<?php echo htmlspecialchars($p['name']); ?>" data-price="<?php echo htmlspecialchars($p['price']); ?>" data-img="<?php echo htmlspecialchars($p['image_url']); ?>">Add to Cart</button>
                        <button class="btn-buy py-3 text-center text-xs font-semibold text-gray-700 hover:text-white hover:bg-primary transition-colors cursor-pointer" type="button" data-id="<?php echo $p['id']; ?>" data-name="<?php echo htmlspecialchars($p['name']); ?>" data-price="<?php echo htmlspecialchars($p['price']); ?>" data-img="<?php echo htmlspecialchars($p['image_url']); ?>">Buy Now</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="flex justify-center mt-14">
                <a href="./shop.php" class="inline-block bg-white hover:bg-primary text-gray-700 hover:text-white font-bold rounded-xl px-8 py-4 border border-gray-200 hover:border-primary transition-all duration-200 shadow-sm hover:shadow-md">View All Bonsais</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php require_once 'footer.php'; ?>
    <script src="./assets/js/main.js"></script>
</body>

</html>
