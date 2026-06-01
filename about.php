<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php 
    $page_title = "About Us | Bagaicha";
    $page_description = "Learn about Bagaicha — a team of passionate bonsai enthusiasts from Nepal bringing the art of bonsai cultivation to your home.";
    require_once 'assets/partials/_head.php'; 
    ?>
</head>

<body class="bg-gray-50/30 text-gray-800 antialiased font-sans">
    <!-- Header -->
    <?php require_once 'header.php'; ?>

    <!-- About Hero -->
    <div class="bg-brand-dark px-6 md:px-12 py-16 border-b border-white/5">
        <div class="max-w-5xl mx-auto text-white">
            <span class="text-[10px] font-extrabold uppercase tracking-widest text-primary-light bg-primary/30 border border-primary-light/20 rounded-full px-3 py-1.5 inline-block mb-3.5">Our Story</span>
            <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight mb-2.5">About Bagaicha</h1>
            <p class="text-sm text-gray-300 leading-relaxed max-w-lg">A small team of bonsai enthusiasts from Kathmandu, Nepal, sharing the ancient art of miniature trees with the world.</p>
        </div>
    </div>

    <!-- Story Section -->
    <div class="max-w-5xl mx-auto px-6 md:px-12 py-16 grid grid-cols-1 md:grid-cols-2 gap-10 md:gap-14 items-center animate-fade-in">
        <div class="aspect-[4/3] md:aspect-square overflow-hidden bg-gray-50 rounded-2xl border border-gray-100 shadow-sm group shrink-0">
            <img src="./assets/img/misc/about.jpg" alt="About Bagaicha — handcrafted bonsai from Nepal" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
        </div>

        <div class="flex flex-col justify-center">
            <h2 class="text-2xl md:text-3xl font-extrabold text-gray-800 leading-tight mb-4">Growing bonsai, one tree at a time</h2>
            <p class="text-sm font-semibold text-primary leading-relaxed mb-4">We are a group of bonsai enthusiasts who have been cultivating these living sculptures for years — driven by a simple belief: nature belongs in every home.</p>
            <p class="text-xs md:text-sm text-gray-600 leading-relaxed mb-4">Our online store offers a wide variety of bonsai trees, including traditional favourites like juniper and pine, as well as more exotic varieties such as ficus and elm. Every tree is carefully selected, shaped, and cared for before it reaches you.</p>
            <p class="text-xs md:text-sm text-gray-600 leading-relaxed mb-4">We also carry a curated range of bonsai tools and accessories — pruners, wire, specialist potting mix — everything you need to continue the craft at home. All products are chosen for quality and longevity, not just aesthetics.</p>
            <p class="text-xs md:text-sm text-gray-600 leading-relaxed mb-4">Beyond selling, we aim to be a resource. From beginner care guides to advanced pruning techniques, our goal is to help every bonsai owner succeed with their tree, long after the purchase.</p>
            <p class="text-xs md:text-sm text-gray-600 leading-relaxed mb-6">Thank you for visiting Bagaicha. We hope you find a tree that speaks to you.</p>

            <a href="./shop.php" class="inline-block bg-primary hover:bg-primary-dark text-white font-bold rounded-xl px-6 py-3 transition-colors w-fit shadow-sm hover:shadow-md cursor-pointer">Browse Collection</a>
        </div>
    </div>

    <!-- Values Section -->
    <div class="bg-gray-50/50 py-16 border-t border-b border-gray-100">
        <div class="max-w-5xl mx-auto px-6 md:px-12">
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
    <?php require_once 'footer.php'; ?>
    <script src="./assets/js/main.js"></script>
</body>

</html>

