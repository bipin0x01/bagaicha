<?php
session_start();
// Pre-fill fields if user is logged in
$fname = "";
$lname = "";
$email = "";
$phone = "";
$address = "";
if (isset($_SESSION['email'])) {
    $fname = isset($_SESSION['fname']) ? $_SESSION['fname'] : "";
    $lname = isset($_SESSION['lname']) ? $_SESSION['lname'] : "";
    $email = $_SESSION['email'];
    $phone = isset($_SESSION['phone']) ? $_SESSION['phone'] : "";
    $address = isset($_SESSION['address']) ? $_SESSION['address'] : "";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php 
    $page_title = "Checkout | Bagaicha";
    $page_description = "Securely complete your Bagaicha purchase using eSewa Sandbox payments or Cash on Delivery (COD).";
    require_once 'assets/partials/_head.php'; 
    ?>
</head>

<body class="bg-gray-50/30 text-gray-800 antialiased font-sans">
    <!-- Header -->
    <?php require_once 'header.php'; ?>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-12 md:py-16 px-6 md:px-12 bg-gray-50/30">
        <!-- Page Title -->
        <div class="mb-10 text-center md:text-left animate-fade-in">
            <span class="text-[10px] font-bold uppercase tracking-widest text-primary bg-primary-light px-3.5 py-1.5 rounded-full inline-block mb-3">Place Order</span>
            <h1 class="text-3xl md:text-4xl font-extrabold text-brand-dark">Secure Checkout</h1>
        </div>

        <div class="flex flex-col lg:flex-row gap-10 items-start animate-fade-in">
            <!-- Billing Details Form -->
            <div class="w-full lg:flex-[3] bg-white border border-gray-100 rounded-3xl p-8 shadow-sm">
                <h3 class="text-lg font-bold text-gray-800 border-b border-gray-100 pb-4 mb-6">Billing Details</h3>
                
                <form id="checkout-form" action="esewa_redirect.php" method="POST" onsubmit="return validateCheckoutForm()" class="space-y-5">
                    <!-- Hidden field to pass cart data JSON -->
                    <input type="hidden" name="cart_data" id="cart-data-input">

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="fname" class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">First Name *</label>
                            <input type="text" id="fname" name="fname" placeholder="Ram" value="<?php echo htmlspecialchars($fname); ?>" required class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 transition-colors">
                        </div>
                        <div>
                            <label for="lname" class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Last Name *</label>
                            <input type="text" id="lname" name="lname" placeholder="Sharma" value="<?php echo htmlspecialchars($lname); ?>" required class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 transition-colors">
                        </div>
                    </div>

                    <div>
                        <label for="email" class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Email Address *</label>
                        <input type="email" id="email" name="email" placeholder="example@gmail.com" value="<?php echo htmlspecialchars($email); ?>" required class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 transition-colors">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="phone" class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Phone Number *</label>
                            <input type="text" id="phone" name="phone" placeholder="98XXXXXXXX" value="<?php echo htmlspecialchars($phone); ?>" required class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 transition-colors">
                        </div>
                        <div>
                            <label for="address" class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Delivery Address *</label>
                            <input type="text" id="address" name="address" placeholder="New Baneshwor, Kathmandu" value="<?php echo htmlspecialchars($address); ?>" required class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 transition-colors">
                        </div>
                    </div>

                    <div class="border border-gray-100 rounded-2xl p-6 bg-gray-50/50 mt-8">
                        <h4 class="font-bold text-gray-800 text-center mb-5 text-sm uppercase tracking-wider">Select Payment Method</h4>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-lg mx-auto">
                            <!-- eSewa option -->
                            <label class="payment-card relative flex-1 min-w-[140px] border-2 border-primary bg-purple-50/20 rounded-xl p-5 cursor-pointer flex flex-col items-center justify-center gap-3 transition-all select-none">
                                <input type="radio" id="payment_esewa" name="payment_method" value="esewa" checked onchange="updateFormAction()" class="absolute top-4 right-4 w-4 h-4 text-primary focus:ring-primary border-gray-300">
                                <img src="./assets/img/misc/esewa_logo.png" alt="eSewa" onerror="this.src='https://developer.esewa.com.np/assets/images/esewa_logo.png'" class="h-8 object-contain">
                                <span class="font-bold text-gray-700 text-xs tracking-wide">Pay via eSewa</span>
                            </label>
                            
                            <!-- COD option -->
                            <label class="payment-card relative flex-1 min-w-[140px] border border-gray-200 bg-white rounded-xl p-5 cursor-pointer flex flex-col items-center justify-center gap-3 transition-all select-none">
                                <input type="radio" id="payment_cod" name="payment_method" value="cod" onchange="updateFormAction()" class="absolute top-4 right-4 w-4 h-4 text-primary focus:ring-primary border-gray-300">
                                <span class="text-3xl">💵</span>
                                <span class="font-bold text-gray-700 text-xs tracking-wide">Cash on Delivery</span>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-primary hover:bg-primary-dark text-white font-bold rounded-xl py-4 text-sm transition-colors cursor-pointer shadow-sm hover:shadow-md mt-6">Proceed to Payment</button>
                </form>
            </div>

            <!-- Order Summary Block -->
            <div class="w-full lg:flex-[2] bg-white border border-gray-100 rounded-3xl p-8 shadow-sm h-fit">
                <h3 class="text-lg font-bold text-gray-800 border-b border-gray-100 pb-4 mb-6">Order Summary</h3>
                <div id="checkout-items-list" class="space-y-4 mb-6 max-h-[300px] overflow-y-auto pr-1">
                    <!-- Dynamically populated by JS -->
                </div>
                
                <div class="border-t border-gray-100 pt-5 space-y-3 text-sm text-gray-500 font-medium">
                    <div class="flex justify-between">
                        <span>Cart Subtotal</span>
                        <span id="summary-subtotal" class="text-gray-700 font-semibold">Rs. 0</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Delivery Charge</span>
                        <span class="color-emerald text-emerald-600 font-bold">FREE</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Government Tax (13%)</span>
                        <span class="color-emerald text-emerald-600 font-bold">INCLUDED</span>
                    </div>
                    <div class="flex justify-between items-center pt-5 border-t border-gray-800/80 text-gray-800 font-extrabold text-base">
                        <span>Total Payable</span>
                        <span id="summary-total" class="text-primary text-lg">Rs. 0</span>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php require_once 'footer.php'; ?>

    <script>
        // Load the cart summary dynamically from localStorage
        document.addEventListener("DOMContentLoaded", () => {
            renderCheckoutSummary();
        });

        function getCart() {
            try {
                return JSON.parse(localStorage.getItem("bagaicha_cart")) || [];
            } catch (e) {
                return [];
            }
        }

        function renderCheckoutSummary() {
            const listDiv = document.getElementById("checkout-items-list");
            const subtotalSpan = document.getElementById("summary-subtotal");
            const totalSpan = document.getElementById("summary-total");
            const cartInput = document.getElementById("cart-data-input");

            if (!listDiv) return;

            const cart = getCart();

            // Redirect back to shop if cart is empty
            if (cart.length === 0) {
                showModal("Cart Empty", "Your shopping cart is empty! Let's take you back to the Shop page so you can select some items.", "error");
                setTimeout(() => {
                    window.location.href = "shop.php";
                }, 3500);
                return;
            }

            // Populate hidden input with stringified cart data
            cartInput.value = JSON.stringify(cart);

            listDiv.innerHTML = "";
            let total = 0;

            cart.forEach(item => {
                const itemTotal = item.price * item.quantity;
                total += itemTotal;

                const itemRow = document.createElement("div");
                itemRow.className = "flex justify-between items-center py-2.5 border-b border-gray-50 last:border-0";
                itemRow.innerHTML = `
                    <div class="flex items-center gap-3">
                        <img src="${item.img}" alt="${item.name}" class="w-11 h-11 rounded-lg border border-gray-100 object-cover shrink-0">
                        <div>
                            <h4 class="text-xs font-bold text-gray-800 truncate w-36" title="${item.name}">${item.name}</h4>
                            <p class="text-[10px] text-gray-400 mt-0.5">Quantity: ${item.quantity} x Rs. ${item.price}</p>
                        </div>
                    </div>
                    <span class="font-semibold text-xs text-gray-700">Rs. ${itemTotal}</span>
                `;
                listDiv.appendChild(itemRow);
            });

            subtotalSpan.textContent = `Rs. ${total}`;
            totalSpan.textContent = `Rs. ${total}`;
        }

        function validateCheckoutForm() {
            const cart = getCart();
            if (cart.length === 0) {
                showToast("Cart Empty", "Your shopping cart is empty!", "error");
                return false;
            }

            const phone = document.getElementById("phone").value.trim();
            // Validate Nepali phone formats (98XXXXXXXX or 97XXXXXXXX)
            const phonePattern = /^(98|97)\d{8}$/;
            if (!phonePattern.test(phone)) {
                showToast("Phone Number Error", "Please enter a valid Nepalese phone number (10 digits starting with 98 or 97)!", "error");
                return false;
            }

            return true;
        }

        function updateFormAction() {
            const form = document.getElementById("checkout-form");
            const esewaRadio = document.getElementById("payment_esewa");
            const esewaLabel = esewaRadio.closest("label");
            const codRadio = document.getElementById("payment_cod");
            const codLabel = codRadio.closest("label");
            
            if (esewaRadio.checked) {
                form.action = "esewa_redirect.php";
                esewaLabel.className = "payment-card relative flex-1 min-w-[140px] border-2 border-primary bg-purple-50/20 rounded-xl p-5 cursor-pointer flex flex-col items-center justify-center gap-3 transition-all select-none";
                codLabel.className = "payment-card relative flex-1 min-w-[140px] border border-gray-200 bg-white rounded-xl p-5 cursor-pointer flex flex-col items-center justify-center gap-3 transition-all select-none";
            } else {
                form.action = "cod_handler.php";
                codLabel.className = "payment-card relative flex-1 min-w-[140px] border-2 border-primary bg-purple-50/20 rounded-xl p-5 cursor-pointer flex flex-col items-center justify-center gap-3 transition-all select-none";
                esewaLabel.className = "payment-card relative flex-1 min-w-[140px] border border-gray-200 bg-white rounded-xl p-5 cursor-pointer flex flex-col items-center justify-center gap-3 transition-all select-none";
            }
        }
    </script>
</body>

</html>
