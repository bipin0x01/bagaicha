<?php
require_once dirname(__DIR__) . '/config/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php 
    $page_title = "About Us | Bagaicha";
    $page_description = "Learn about Bagaicha — a team of passionate bonsai enthusiasts from Nepal bringing the art of bonsai cultivation to your home.";
    require INCLUDES_PATH . '/partials/head.php'; 
    ?>
</head>

<body class="bg-gray-50/30 text-gray-800 antialiased font-sans">
    <!-- Header -->
    <?php require INCLUDES_PATH . '/header.php'; ?>

    <!-- About Hero -->
    <div class="bg-brand-dark px-6 md:px-12 py-14 border-b border-white/5">
        <div class="max-w-7xl mx-auto text-white animate-fade-in">
            <span class="text-[10px] font-extrabold uppercase tracking-widest text-primary-light bg-primary/30 border border-primary-light/20 rounded-full px-3 py-1.5 inline-block mb-3.5">Our Story</span>
            <h1 class="text-3xl md:text-4xl font-extrabold leading-tight tracking-tight mb-2.5">About Bagaicha</h1>
            <p class="text-sm text-gray-300 leading-relaxed max-w-2xl">A small team of bonsai enthusiasts from Kathmandu, Nepal, sharing the ancient art of miniature trees with homes across the country.</p>
        </div>
    </div>

    <!-- Story Section -->
    <main class="max-w-7xl mx-auto py-12 md:py-16 px-6 md:px-12 bg-gray-50/30">
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 md:gap-10 items-start animate-fade-in">
            <div class="lg:col-span-2">
                <div class="aspect-[4/3] rounded-3xl overflow-hidden bg-gray-50 border border-gray-100 shadow-sm group">
                    <img src="/assets/img/misc/about.jpg" alt="About Bagaicha — handcrafted bonsai from Nepal" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                </div>
            </div>

            <div class="lg:col-span-3 bg-white border border-gray-100 rounded-3xl p-6 md:p-8 shadow-sm">
                <h2 class="text-2xl md:text-3xl font-extrabold text-gray-800 leading-tight mb-4">Growing bonsai, one tree at a time</h2>
                <p class="text-sm font-semibold text-primary leading-relaxed mb-4">We cultivate living sculptures with one belief at heart: nature belongs in every home.</p>
                <div class="space-y-4 text-sm text-gray-600 leading-relaxed">
                    <p>Our store offers a wide variety of bonsai trees, from timeless classics like juniper and pine to expressive varieties like ficus and elm. Each tree is selected, shaped, and cared for before it reaches you.</p>
                    <p>We also carry a curated range of bonsai tools and essentials - pruners, wire, and specialist potting mix - to help you continue the craft with confidence.</p>
                    <p>Beyond products, we share practical guidance. From beginner care to advanced pruning, our goal is to support your bonsai journey long after checkout.</p>
                </div>
                <div class="mt-6">
                    <a href="/shop.php" class="inline-block bg-primary hover:bg-primary-dark text-white font-bold rounded-xl px-6 py-3 transition-colors shadow-sm hover:shadow-md cursor-pointer">Browse Collection</a>
                </div>
            </div>
        </div>
    </main>

    <!-- Values Section -->
    <div class="bg-gray-50/50 py-16 border-t border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-6 md:px-12">
            <span class="text-[10px] font-bold uppercase tracking-wider text-primary mb-2 block">Why Choose Us</span>
            <h2 class="text-2xl font-extrabold text-gray-800 mb-8">What we stand for</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm border-t-4 border-t-primary hover:shadow-md transition-shadow">
                    <h3 class="font-bold text-sm text-gray-800 mb-2">Expert Grown</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">Every tree is shaped by certified arborists with years of dedicated cultivation experience.</p>
                </div>
                <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm border-t-4 border-t-primary hover:shadow-md transition-shadow">
                    <h3 class="font-bold text-sm text-gray-800 mb-2">Quality First</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">We carry only products we would trust in our own homes — no compromise on plant health.</p>
                </div>
                <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm border-t-4 border-t-primary hover:shadow-md transition-shadow">
                    <h3 class="font-bold text-sm text-gray-800 mb-2">Local & Rooted</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">Based in Kathmandu, we ship across Nepal with care, packed to protect every branch.</p>
                </div>
                <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm border-t-4 border-t-primary hover:shadow-md transition-shadow">
                    <h3 class="font-bold text-sm text-gray-800 mb-2">Always Learning</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">We share guides, care notes, and techniques so your bonsai thrives long after delivery.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php require INCLUDES_PATH . '/footer.php'; ?>
</body>

</html>

