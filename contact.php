<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php 
    $page_title = "Contact Us | Bagaicha";
    $page_description = "Send a message or get in touch with Bagaicha for expert bonsai tips, order help, or arborist support.";
    require_once 'assets/partials/_head.php'; 
    ?>
</head>

<body class="bg-gray-50/30 text-gray-800 antialiased font-sans">
    <!-- Header -->
    <?php require_once 'header.php'; ?>

    <!-- Main -->
    <main class="py-16 px-6 md:px-12 bg-gray-50/30">
        <!-- Page Title -->
        <div class="text-center mb-12 animate-fade-in">
            <span class="text-[10px] font-bold uppercase tracking-widest text-primary bg-primary-light px-3.5 py-1.5 rounded-full inline-block mb-3">Get In Touch</span>
            <h1 class="text-3xl md:text-4xl font-extrabold text-brand-dark">Contact Us</h1>
        </div>
        
        <div class="max-w-5xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10 md:gap-14 items-center animate-fade-in">
                <!-- Image Panel -->
                <div class="aspect-[4/3] rounded-2xl overflow-hidden border border-gray-100 shadow-sm group">
                    <img src="./assets/img/misc/contact.jpg" alt="Contact Bagaicha Bonsai experts" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                </div>
                
                <!-- Form Panel -->
                <div class="bg-white rounded-3xl p-6 md:p-8 border border-gray-100 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-800 mb-6">Send us a message</h2>
                    <form action="#" onsubmit="return sendContact()">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="fname" class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">First Name</label>
                                <input type="text" id="fname" name="fname" placeholder="Ram" class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 transition-colors mb-4">
                            </div>
                            <div>
                                <label for="lname" class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Last Name</label>
                                <input type="text" id="lname" name="lname" placeholder="Sharma" class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 transition-colors mb-4">
                            </div>
                        </div>
                        
                        <label for="email" class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="example@gmail.com" required class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 transition-colors mb-4">

                        <label for="subject" class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Subject</label>
                        <input type="text" id="subject" name="subject" placeholder="What is this about?" required class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 transition-colors mb-4">

                        <label for="message" class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Message</label>
                        <textarea id="message" name="message" placeholder="Write something.." required class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 transition-colors h-32 resize-none mb-6"></textarea>
                        
                        <input type="submit" value="Submit Message" class="w-full bg-primary hover:bg-primary-dark text-white font-bold rounded-xl py-3.5 text-sm transition-colors cursor-pointer shadow-sm hover:shadow-md">
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php require_once 'footer.php'; ?>
    <script src="./assets/js/main.js"></script>
</body>

</html>
