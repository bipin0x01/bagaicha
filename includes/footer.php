<footer class="bg-brand-dark text-white/80 border-t border-white/5">
    <div class="max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-12 px-6 md:px-12 py-16">
        <div class="flex flex-col gap-5">
            <a class="text-2xl font-extrabold text-white tracking-tight" href="<?php echo url('index.php'); ?>"><span class="text-primary">B</span>agaicha</a>
            <p class="text-sm text-gray-400 leading-relaxed">Premium bonsai trees and essentials, grown and curated in Nepal.</p>
        </div>

        <div class="flex flex-col gap-4">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider">Quick Links</h3>
            <ul class="flex flex-col gap-2.5 text-sm">
                <li><a href="<?php echo url('index.php'); ?>" class="hover:text-primary-light transition-colors">Home</a></li>
                <li><a href="<?php echo url('shop.php'); ?>" class="hover:text-primary-light transition-colors">Shop</a></li>
                <li><a href="<?php echo url('about.php'); ?>" class="hover:text-primary-light transition-colors">About Us</a></li>
                <li><a href="<?php echo url('contact.php'); ?>" class="hover:text-primary-light transition-colors">Contact</a></li>
            </ul>
        </div>

        <div class="flex flex-col gap-4">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider">Support & Policies</h3>
            <ul class="flex flex-col gap-2.5 text-sm">
                <li><a href="#" onclick="showModal('FAQs', 'Frequently Asked Questions page coming soon!', 'info'); return false;" class="hover:text-primary-light transition-colors">FAQs</a></li>
                <li><a href="#" onclick="showModal('Shipping', 'Free shipping within Nepal on orders above Rs. 1000.', 'info'); return false;" class="hover:text-primary-light transition-colors">Shipping Info</a></li>
                <li><a href="#" onclick="showModal('Returns', 'Easy 7-day return policy for healthy plants and tools!', 'info'); return false;" class="hover:text-primary-light transition-colors">Returns & Refunds</a></li>
                <li><a href="#" onclick="showModal('Payment Methods', 'We currently support eSewa and Cash on Delivery.', 'info'); return false;" class="hover:text-primary-light transition-colors">Payment Options</a></li>
                <li><a href="<?php echo url('profile.php'); ?>" class="hover:text-primary-light transition-colors">My Account Portal</a></li>
            </ul>
        </div>

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
            <p class="text-xs text-gray-500 mt-2">Response time: within 24 business hours</p>
        </div>
    </div>

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
