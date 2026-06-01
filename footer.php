<?php
/**
 * Shared Premium Footer Component
 */
?>
<?php
/**
 * Shared Premium Footer Component
 */
?>
<footer class="bg-brand-dark text-white/80 border-t border-white/5">
    <div class="max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-12 px-6 md:px-12 py-16">
        <!-- Brand / About Column -->
        <div class="flex flex-col gap-5">
            <a class="text-2xl font-extrabold text-white tracking-tight" href="./index.php"><span class="text-primary">B</span>agaicha</a>
            <p class="text-sm text-gray-400 leading-relaxed">Bringing the serenity of nature to your living spaces. We specialize in handcrafted premium Bonsai trees, specialized gardening equipment, and expert plant cultivation knowledge.</p>
            <div class="flex items-center gap-3 mt-2">
                <a href="#" title="Facebook" class="w-8 h-8 rounded-lg bg-white/5 hover:bg-primary flex items-center justify-center text-xs font-semibold text-white transition-all duration-150">FB</a>
                <a href="#" title="Instagram" class="w-8 h-8 rounded-lg bg-white/5 hover:bg-primary flex items-center justify-center text-xs font-semibold text-white transition-all duration-150">IG</a>
                <a href="#" title="Twitter" class="w-8 h-8 rounded-lg bg-white/5 hover:bg-primary flex items-center justify-center text-xs font-semibold text-white transition-all duration-150">TW</a>
                <a href="#" title="Pinterest" class="w-8 h-8 rounded-lg bg-white/5 hover:bg-primary flex items-center justify-center text-xs font-semibold text-white transition-all duration-150">PIN</a>
            </div>
        </div>

        <!-- Links Column -->
        <div class="flex flex-col gap-4">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider">Quick Links</h3>
            <ul class="flex flex-col gap-2.5 text-sm">
                <li><a href="./index.php" class="hover:text-primary-light transition-colors">Home</a></li>
                <li><a href="./shop.php" class="hover:text-primary-light transition-colors">Shop Bonsais</a></li>
                <li><a href="./about.php" class="hover:text-primary-light transition-colors">About Us</a></li>
                <li><a href="./contact.php" class="hover:text-primary-light transition-colors">Contact</a></li>
            </ul>
        </div>

        <!-- Support / Info Column -->
        <div class="flex flex-col gap-4">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider">Support & Policies</h3>
            <ul class="flex flex-col gap-2.5 text-sm">
                <li><a href="#" onclick="showModal('FAQs', 'Frequently Asked Questions page coming soon!', 'info'); return false;" class="hover:text-primary-light transition-colors">FAQs</a></li>
                <li><a href="#" onclick="showModal('Shipping', 'Free nationwide shipping within Nepal on all orders above Rs. 1000!', 'info'); return false;" class="hover:text-primary-light transition-colors">Shipping Info</a></li>
                <li><a href="#" onclick="showModal('Returns', 'Easy 7-day return policy for healthy plants and tools!', 'info'); return false;" class="hover:text-primary-light transition-colors">Returns & Refunds</a></li>
                <li><a href="#" onclick="showModal('Payment Methods', 'We accept secure eSewa payments with integrated digital signature verification.', 'info'); return false;" class="hover:text-primary-light transition-colors">Payment Options</a></li>
                <li><a href="./profile.php" class="hover:text-primary-light transition-colors">My Account Portal</a></li>
            </ul>
        </div>

        <!-- Contact & Newsletter Column -->
        <div class="flex flex-col gap-4">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider">Contact Us</h3>
            <ul class="flex flex-col gap-2 text-sm text-gray-400">
                <li class="flex items-center gap-2">
                    <span class="text-xs uppercase text-primary-light font-semibold tracking-wider w-16">Address</span>
                    <span>Kathmandu, Nepal</span>
                </li>
                <li class="flex items-center gap-2">
                    <span class="text-xs uppercase text-primary-light font-semibold tracking-wider w-16">Phone</span>
                    <span>+977 9819284721</span>
                </li>
                <li class="flex items-center gap-2">
                    <span class="text-xs uppercase text-primary-light font-semibold tracking-wider w-16">Email</span>
                    <span>contact@bagaicha.com</span>
                </li>
            </ul>
            <div class="mt-4">
                <h4 class="text-xs font-bold text-white uppercase tracking-wider mb-2.5">Subscribe to Newsletter</h4>
                <form class="flex items-center w-full max-w-xs" onsubmit="showToast('Subscribed!', 'Thank you for subscribing to our Bonsai newsletter!', 'success'); this.reset(); return false;">
                    <input type="email" placeholder="Your email address" required class="flex-1 bg-white/5 border border-white/10 rounded-l-xl px-3 py-2 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-primary transition-colors">
                    <button type="submit" class="bg-primary hover:bg-primary-dark text-white text-sm font-semibold rounded-r-xl px-4 py-2 border border-primary hover:border-primary-dark transition-all duration-150 cursor-pointer">Go</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bottom Bar -->
    <div class="border-t border-white/5 bg-black/20">
        <div class="max-w-7xl mx-auto px-6 md:px-12 py-6 flex flex-col sm:flex-row justify-between items-center gap-4 text-xs text-gray-500">
            <div>
                <p>Copyright &copy; Bagaicha 2026. All rights reserved.</p>
            </div>
            <div class="flex items-center gap-6">
                <a href="#" onclick="showModal('Privacy Policy', 'Our Privacy Policy ensures your shopping details are secured with encryption and never shared.', 'info'); return false;" class="hover:text-white transition-colors">Privacy Policy</a>
                <a href="#" onclick="showModal('Terms of Service', 'Terms of Service cover our sales guidelines, shipping terms, and secure SQLite/eSewa billing.', 'info'); return false;" class="hover:text-white transition-colors">Terms of Service</a>
            </div>
            <a href="#top" class="inline-block">
                <button class="px-4 py-2 bg-white/5 hover:bg-primary hover:text-white border border-white/10 hover:border-primary text-gray-400 text-xs font-semibold rounded-xl transition-all duration-150 cursor-pointer" type="button">
                    Go To Top
                </button>
            </a>
        </div>
    </div>
</footer>

