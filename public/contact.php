<?php
require_once dirname(__DIR__) . '/config/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php 
    $page_title = "Contact Us | Bagaicha";
    $page_description = "Send a message or get in touch with Bagaicha for expert bonsai tips, order help, or arborist support.";
    require INCLUDES_PATH . '/partials/head.php'; 
    ?>
</head>

<body class="bg-gray-50/30 text-gray-800 antialiased font-sans">
    <!-- Header -->
    <?php require INCLUDES_PATH . '/header.php'; ?>

    <!-- Hero Header -->
    <div class="bg-brand-dark px-6 md:px-12 py-14 border-b border-white/5">
        <div class="max-w-7xl mx-auto text-white animate-fade-in">
            <span class="text-[10px] font-extrabold uppercase tracking-widest text-primary-light bg-primary/30 border border-primary-light/20 rounded-full px-3 py-1.5 inline-block mb-3.5">Get In Touch</span>
            <h1 class="text-3xl md:text-4xl font-extrabold leading-tight tracking-tight mb-2.5">Contact Bagaicha</h1>
            <p class="text-sm text-gray-300 leading-relaxed max-w-2xl">Need help with an order, plant care, or payment? Send us a message and our team will get back to you quickly.</p>
        </div>
    </div>

    <!-- Main -->
    <main class="max-w-7xl mx-auto py-12 md:py-16 px-6 md:px-12 bg-gray-50/30">
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 md:gap-10 items-start animate-fade-in">
            <!-- Left Info Panel -->
            <div class="lg:col-span-2 space-y-6">
                <div class="aspect-[4/3] rounded-3xl overflow-hidden border border-gray-100 shadow-sm group bg-white">
                    <img src="/assets/img/misc/contact.jpg" alt="Contact Bagaicha Bonsai experts" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                </div>

                <div class="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm space-y-4">
                    <h2 class="text-lg font-bold text-gray-800">Support Channels</h2>
                    <div class="text-sm text-gray-600 space-y-2">
                        <p><span class="font-semibold text-gray-800">Phone:</span> +977 9819284721</p>
                        <p><span class="font-semibold text-gray-800">Email:</span> contact@bagaicha.com</p>
                        <p><span class="font-semibold text-gray-800">Address:</span> Kathmandu, Nepal</p>
                    </div>
                    <p class="text-xs text-gray-500">Support hours: Sun–Fri, 9:00 AM to 6:00 PM</p>
                </div>
            </div>

            <!-- Form Panel -->
            <div class="lg:col-span-3 bg-white rounded-3xl p-6 md:p-8 border border-gray-100 shadow-sm">
                <h2 class="text-xl font-extrabold text-gray-800 mb-2">Send us a message</h2>
                <p class="text-sm text-gray-500 mb-6">Fill in your details and we will respond as soon as possible.</p>

                <form action="#" onsubmit="return sendContact()" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="fname" class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">First Name</label>
                            <input type="text" id="fname" name="fname" placeholder="Ram" class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 transition-colors">
                        </div>
                        <div>
                            <label for="lname" class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Last Name</label>
                            <input type="text" id="lname" name="lname" placeholder="Sharma" class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 transition-colors">
                        </div>
                    </div>

                    <div>
                        <label for="email" class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="example@gmail.com" required class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 transition-colors">
                    </div>

                    <div>
                        <label for="subject" class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Subject</label>
                        <input type="text" id="subject" name="subject" placeholder="What is this about?" required class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 transition-colors">
                    </div>

                    <div>
                        <label for="message" class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Message</label>
                        <textarea id="message" name="message" placeholder="Write your message..." required class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 transition-colors h-32 resize-none"></textarea>
                    </div>

                    <button type="submit" class="w-full md:w-auto bg-primary hover:bg-primary-dark text-white font-bold rounded-xl px-8 py-3.5 text-sm transition-colors cursor-pointer shadow-sm hover:shadow-md">
                        Submit Message
                    </button>
                </form>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php require INCLUDES_PATH . '/footer.php'; ?>
</body>

</html>
