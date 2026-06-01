<?php
/**
 * Bagaicha - Dynamic Shop Catalog
 * Fetches all products from SQLite database and grids them dynamically.
 */
require_once dirname(__DIR__) . '/config/bootstrap.php';

$products = [];
$query = $db->query("SELECT * FROM products ORDER BY id ASC");
if ($query) {
    while ($row = $query->fetchArray(SQLITE3_ASSOC)) {
        $products[] = $row;
    }
}

$total = count($products);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php 
    $page_title = "Shop All Bonsais | Bagaicha";
    $page_description = "Shop our full collection of premium handcrafted bonsai trees from Nepal. Every tree is grown by certified arborists and ready for your home.";
    require INCLUDES_PATH . '/partials/head.php'; 
    ?>
</head>

<body class="bg-gray-50/30 text-gray-800 antialiased font-sans">
    <!-- Header -->
    <?php require INCLUDES_PATH . '/header.php'; ?>

    <!-- Shop Hero Header -->
    <div class="bg-brand-dark px-6 md:px-12 py-16 border-b border-white/5">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row md:items-end md:justify-between gap-6">
            <div class="text-white max-w-xl">
                <span class="text-[10px] font-extrabold uppercase tracking-widest text-primary-light bg-primary/30 border border-primary-light/20 rounded-full px-3 py-1.5 inline-block mb-3.5">Our Collection</span>
                <h1 class="text-3xl md:text-4xl font-extrabold leading-tight tracking-tight mb-2.5">All Bonsais</h1>
                <p class="text-sm text-gray-300 leading-relaxed">Handcrafted in Nepal &mdash; each tree shaped with patience, care, and years of arborist expertise.</p>
            </div>
            <div class="bg-white/5 border border-white/10 rounded-2xl py-4 px-6 text-center shrink-0">
                <span class="text-3xl font-extrabold text-white block leading-none"><?php echo $total; ?></span>
                <span class="text-[10px] font-semibold text-gray-400 tracking-wider uppercase mt-1.5 block">Varieties</span>
            </div>
        </div>
    </div>

    <!-- Filter / Sort Bar -->
    <div class="sticky top-20 z-40 bg-white/95 backdrop-blur-md border-b border-gray-100 flex justify-between items-center px-6 md:px-12 h-14 shadow-sm">
        <div class="flex items-center gap-2.5">
            <span class="filter-tag active inline-flex items-center px-4 py-1.5 border border-primary text-xs font-bold rounded-full text-primary bg-primary-light transition-colors cursor-pointer" onclick="filterProducts('all', this)">All</span>
            <span class="filter-tag inline-flex items-center px-4 py-1.5 border border-gray-200 text-xs font-semibold text-gray-600 rounded-full hover:bg-purple-50 hover:text-primary hover:border-primary transition-colors cursor-pointer" onclick="filterProducts('sale', this)">On Sale</span>
        </div>
        <div>
            <select class="sort-select text-xs font-medium text-gray-700 bg-white border border-gray-200 rounded-xl px-3 py-1.5 focus:outline-none focus:border-primary cursor-pointer" id="sort-select" onchange="sortProducts(this.value)">
                <option value="default">Sort: Default</option>
                <option value="price-asc">Price: Low to High</option>
                <option value="price-desc">Price: High to Low</option>
                <option value="name-asc">Name: A–Z</option>
            </select>
        </div>
    </div>

    <!-- Product Grid -->
    <main class="max-w-7xl mx-auto px-6 md:px-12 py-14">
        <?php if (empty($products)): ?>
        <div class="text-center py-20 px-4">
            <h3 class="text-lg font-bold text-gray-800 mb-2">No bonsais yet</h3>
            <p class="text-sm text-gray-500 max-w-xs mx-auto">Our arborists are still growing the catalog. Check back soon.</p>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 md:gap-8" id="product-grid">
            <?php foreach ($products as $p):
                $has_discount = !empty($p['discount_price']);
                $savings_pct = '';
                if ($has_discount && $p['discount_price'] > $p['price']) {
                    $savings_pct = round((($p['discount_price'] - $p['price']) / $p['discount_price']) * 100) . '% off';
                }
            ?>
            <div class="product-card group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col overflow-hidden"
                 data-id="<?php echo $p['id']; ?>"
                 data-price="<?php echo $p['price']; ?>"
                 data-name="<?php echo htmlspecialchars($p['name']); ?>"
                 data-discount="<?php echo $has_discount ? '1' : '0'; ?>">

                <div class="card-img-wrap relative aspect-square overflow-hidden bg-gray-50">
                    <?php if ($savings_pct): ?>
                    <span class="discount-badge absolute top-3 left-3 bg-red-500 text-white text-[9px] font-extrabold px-2 py-1 rounded-md uppercase tracking-wider z-10"><?php echo $savings_pct; ?></span>
                    <?php endif; ?>

                    <a href="/product.php?id=<?php echo $p['id']; ?>" class="block w-full h-full">
                        <img src="<?php echo htmlspecialchars($p['image_url']); ?>"
                             alt="<?php echo htmlspecialchars($p['name']); ?>"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                             loading="lazy">
                    </a>
                </div>

                <div class="p-5 flex-1 flex flex-col justify-between">
                    <div>
                        <a href="/product.php?id=<?php echo $p['id']; ?>" class="card-name text-sm font-bold text-gray-800 hover:text-primary transition-colors line-clamp-1 mb-1.5 block">
                            <?php echo htmlspecialchars($p['name']); ?>
                        </a>
                    </div>
                    <div class="flex items-baseline gap-2 mt-auto">
                        <span class="card-price text-sm font-extrabold text-primary">Rs. <?php echo htmlspecialchars($p['price']); ?></span>
                        <?php if ($has_discount): ?>
                        <span class="card-original-price text-[11px] text-gray-400 line-through">Rs. <?php echo htmlspecialchars($p['discount_price']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="grid grid-cols-2 border-t border-gray-100 bg-gray-50/50 divide-x divide-gray-100">
                    <button class="btn-buy py-3 text-center text-xs font-semibold text-gray-600 hover:text-primary hover:bg-purple-50/50 transition-colors cursor-pointer" type="button"
                        data-id="<?php echo $p['id']; ?>"
                        data-name="<?php echo htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-price="<?php echo htmlspecialchars($p['price']); ?>"
                        data-img="<?php echo htmlspecialchars($p['image_url'], ENT_QUOTES, 'UTF-8'); ?>">Add to Cart</button>
                    <button class="btn-buy py-3 text-center text-xs font-semibold text-gray-700 hover:text-white hover:bg-primary transition-colors cursor-pointer" type="button"
                        data-id="<?php echo $p['id']; ?>"
                        data-name="<?php echo htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-price="<?php echo htmlspecialchars($p['price']); ?>"
                        data-img="<?php echo htmlspecialchars($p['image_url'], ENT_QUOTES, 'UTF-8'); ?>">Buy Now</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <?php require INCLUDES_PATH . '/footer.php'; ?>
    <script>
        // Filter products
        function filterProducts(type, el) {
            // Update active tag styled with Tailwind classes
            document.querySelectorAll('.filter-tag').forEach(t => {
                t.className = "filter-tag inline-flex items-center px-4 py-1.5 border border-gray-200 text-xs font-semibold text-gray-600 rounded-full hover:bg-purple-50 hover:text-primary hover:border-primary transition-colors cursor-pointer";
            });
            el.className = "filter-tag inline-flex items-center px-4 py-1.5 border border-primary text-xs font-bold rounded-full text-primary bg-primary-light transition-colors cursor-pointer active";
 
            const cards = document.querySelectorAll('.product-card');
            cards.forEach(card => {
                if (type === 'all') {
                    card.style.display = '';
                } else if (type === 'sale') {
                    card.style.display = card.dataset.discount === '1' ? '' : 'none';
                }
            });
        }

        // Sort products
        function sortProducts(value) {
            const grid = document.getElementById('product-grid');
            if (!grid) return;

            const cards = Array.from(grid.querySelectorAll('.product-card'));

            cards.sort((a, b) => {
                if (value === 'price-asc')  return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
                if (value === 'price-desc') return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
                if (value === 'name-asc')   return a.dataset.name.localeCompare(b.dataset.name);
                return 0;
            });

            cards.forEach(card => grid.appendChild(card));
        }
    </script>
</body>

</html>

