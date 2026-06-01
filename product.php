<?php
/**
 * Bagaicha - Dynamic Product Details Page
 * Displays detailed information about a single Bonsai product and related items.
 */
require_once 'db.php';
session_start();

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id <= 0) {
    header("Location: shop.php");
    exit;
}

$stmt = $db->prepare("SELECT * FROM products WHERE id = :id");
$stmt->bindValue(':id', $product_id, SQLITE3_INTEGER);
$res = $stmt->execute();
$product = $res->fetchArray(SQLITE3_ASSOC);

if (!$product) {
    header("Location: shop.php");
    exit;
}

// Fetch related products (4 random products excluding current one)
$related_products = [];
$related_stmt = $db->prepare("SELECT * FROM products WHERE id != :id ORDER BY RANDOM() LIMIT 4");
$related_stmt->bindValue(':id', $product_id, SQLITE3_INTEGER);
$related_res = $related_stmt->execute();
if ($related_res) {
    while ($row = $related_res->fetchArray(SQLITE3_ASSOC)) {
        $related_products[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php 
    $page_title = htmlspecialchars($product['name']) . " | Bagaicha";
    $page_description = "View details and arborist cultivation notes for " . htmlspecialchars($product['name']) . ". Complete your purchase or add to your collection.";
    require_once 'assets/partials/_head.php'; 
    ?>
</head>

<body class="bg-gray-50/30 text-gray-800 antialiased font-sans">
    <!-- Header -->
    <?php require_once 'header.php'; ?>

    <!-- Main Content -->
    <main class="py-12 md:py-16 px-6 md:px-12 bg-gray-50/30">
        
        <!-- Product Details Container -->
        <div class="product-grid-item max-w-5xl mx-auto bg-white rounded-3xl p-6 md:p-10 border border-gray-100 shadow-sm grid grid-cols-1 md:grid-cols-2 gap-10 md:gap-14">
            
            <!-- Left Panel: Image -->
            <div class="product-grid-item-img aspect-square overflow-hidden bg-gray-50 rounded-2xl border border-gray-100 hover:shadow-md transition-shadow group">
                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
            </div>

            <!-- Right Panel: Info and Actions -->
            <div class="product-grid-item-info flex flex-col justify-between py-2">
                <div>
                    <span class="inline-block text-[10px] font-extrabold uppercase tracking-widest text-primary bg-primary-light px-3.5 py-1.5 rounded-full mb-4">Premium Bonsai</span>
                    <h2 class="text-2xl md:text-3xl font-extrabold text-gray-800 leading-tight mb-2.5"><?php echo htmlspecialchars($product['name']); ?></h2>
                    
                    <div class="price flex items-baseline gap-3.5 mb-6">
                        <h4 class="text-xl md:text-2xl font-extrabold text-primary">Rs. <?php echo htmlspecialchars($product['price']); ?></h4>
                        <?php if (!empty($product['discount_price'])): ?>
                            <span class="discount text-sm text-gray-400 line-through">Rs. <?php echo htmlspecialchars($product['discount_price']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="bg-gray-50 border border-gray-100 rounded-2xl p-5 mb-8 text-sm leading-relaxed text-gray-600">
                        <strong class="block text-gray-800 font-bold mb-1.5 text-xs uppercase tracking-wider">Plant Description & Care Notes:</strong>
                        <p>
                            <?php echo nl2br(htmlspecialchars($product['description'] ?: 'This handcrafted Bonsai is grown with precision by Bagaicha\'s expert arborists. It thrives under bright, indirect light and moderate watering. Rotate monthly for even foliage growth.')); ?>
                        </p>
                    </div>
                </div>

                <div>
                    <!-- Quantity Selector Block -->
                    <div class="mb-6 flex items-center gap-4">
                        <span class="text-sm font-semibold text-gray-600">Quantity:</span>
                        <div class="flex items-center border border-gray-200 rounded-xl overflow-hidden bg-white shadow-sm w-fit">
                            <button type="button" class="qty-btn px-4 py-2 text-gray-500 hover:text-primary hover:bg-gray-50 font-bold transition-colors cursor-pointer text-sm" onclick="decrementQty()">-</button>
                            <input type="number" id="detail-qty" class="product-qty-input w-12 text-center border-none font-bold text-gray-800 outline-none select-none text-sm pointer-events-none" value="1" min="1" max="99" readonly>
                            <button type="button" class="qty-btn px-4 py-2 text-gray-500 hover:text-primary hover:bg-gray-50 font-bold transition-colors cursor-pointer text-sm" onclick="incrementQty()">+</button>
                        </div>
                    </div>

                    <!-- Action buttons mapped to main.js -->
                    <div class="product-grid-item-add flex gap-4 w-full">
                        <button class="btn-buy flex-1 py-3.5 text-center text-xs font-bold border-2 border-primary hover:bg-primary-light/35 text-primary rounded-xl transition-all cursor-pointer" id="addCart" type="button">Add to Cart</button>
                        <button class="btn-buy flex-1 py-3.5 text-center text-xs font-bold bg-primary hover:bg-primary-dark text-white rounded-xl transition-all shadow-md hover:shadow-primary/30 cursor-pointer" type="button">Buy Now</button>
                    </div>
                </div>
            </div>

        </div>

        <!-- Related Products Section -->
        <?php if (!empty($related_products)): ?>
            <div class="related-section max-w-5xl mx-auto my-20">
                <h3 class="text-lg font-bold text-gray-800 border-b-2 border-primary pb-3 mb-8 w-fit">You May Also Like</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <?php foreach ($related_products as $p): 
                        $has_discount = !empty($p['discount_price']);
                        $savings_pct = '';
                        if ($has_discount && $p['discount_price'] > $p['price']) {
                            $savings_pct = round((($p['discount_price'] - $p['price']) / $p['discount_price']) * 100) . '% off';
                        }
                    ?>
                        <div class="product-card group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col overflow-hidden">
                            <!-- Image Wrap -->
                            <div class="card-img-wrap relative aspect-square overflow-hidden bg-gray-50">
                                <?php if ($savings_pct): ?>
                                <span class="discount-badge absolute top-3 left-3 bg-red-500 text-white text-[9px] font-extrabold px-2 py-1 rounded-md uppercase tracking-wider z-10"><?php echo $savings_pct; ?></span>
                                <?php endif; ?>

                                <a href="product.php?id=<?php echo $p['id']; ?>" class="block w-full h-full">
                                    <img src="<?php echo htmlspecialchars($p['image_url']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                                </a>

                                <!-- Quick action overlay -->
                                <div class="card-overlay absolute inset-x-0 bottom-0 translate-y-full group-hover:translate-y-0 transition-transform duration-200 bg-gradient-to-t from-black/75 to-transparent p-3 flex gap-2 z-20">
                                    <button class="btn-overlay btn-overlay-cart flex-1 py-2 text-center text-[10px] font-semibold rounded-lg bg-white/10 hover:bg-white/20 text-white border border-white/20 hover:border-white/30 transition-colors cursor-pointer" type="button">Add to Cart</button>
                                    <button class="btn-overlay btn-overlay-buy flex-1 py-2 text-center text-[10px] font-semibold rounded-lg bg-primary hover:bg-primary-dark text-white transition-colors cursor-pointer" type="button">Buy Now</button>
                                </div>
                            </div>
                            <!-- Info Area -->
                            <div class="p-4 flex-1 flex flex-col justify-between">
                                <div>
                                    <a href="product.php?id=<?php echo $p['id']; ?>" class="card-name text-xs font-bold text-gray-800 hover:text-primary transition-colors line-clamp-1 mb-1.5 block">
                                        <?php echo htmlspecialchars($p['name']); ?>
                                    </a>
                                </div>
                                <div class="flex items-baseline gap-2 mt-auto">
                                    <span class="card-price text-xs font-extrabold text-primary">Rs. <?php echo htmlspecialchars($p['price']); ?></span>
                                    <?php if ($has_discount): ?>
                                    <span class="card-original-price text-[10px] text-gray-400 line-through">Rs. <?php echo htmlspecialchars($p['discount_price']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </main>

    <!-- Footer -->
    <?php require_once 'footer.php'; ?>

    <script src="./assets/js/main.js"></script>
    <script>
        function incrementQty() {
            const input = document.getElementById("detail-qty");
            let val = parseInt(input.value) || 1;
            if (val < 99) {
                input.value = val + 1;
            }
        }
        function decrementQty() {
            const input = document.getElementById("detail-qty");
            let val = parseInt(input.value) || 1;
            if (val > 1) {
                input.value = val - 1;
            }
        }
    </script>
</body>

</html>

