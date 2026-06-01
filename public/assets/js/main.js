/**
 * Bagaicha Shared JS Script
 * Dynamic LocalStorage Shopping Cart & Form Validation
 */

document.addEventListener("DOMContentLoaded", () => {
  // Shared Cart Popup Toggling Logic
  const cartModal = document.getElementById("cart");
  const cartOpenBtn = document.getElementById("showcart-btn");
  const cartCloseBtn = document.getElementById("cart-close");
  const userMenu = document.getElementById("user-menu");
  const userMenuChevron = document.getElementById("user-menu-chevron");

  const openCartModal = () => {
    if (!cartModal) return;
    cartModal.classList.add("cart-open");
    cartModal.setAttribute("aria-hidden", "false");
    updateCartUI();
  };

  const closeCartModal = () => {
    if (!cartModal) return;
    cartModal.classList.remove("cart-open");
    cartModal.setAttribute("aria-hidden", "true");
  };

  if (cartModal && cartOpenBtn && cartCloseBtn) {
    cartOpenBtn.addEventListener("click", () => {
      openCartModal();
    });

    cartCloseBtn.addEventListener("click", () => {
      closeCartModal();
    });

    cartModal.addEventListener("click", (e) => {
      if (e.target === cartModal) {
        closeCartModal();
      }
    });
  }

  // Account button: dropdown for logged-in users, redirect for guests
  const userBtn = document.getElementById("user-btn");
  if (userBtn) {
    userBtn.addEventListener("click", (e) => {
      if (userBtn.dataset.dropdown === "true" && userMenu) {
        e.preventDefault();
        const isHidden = userMenu.classList.contains("hidden");
        userMenu.classList.toggle("hidden");
        if (userMenuChevron) {
          userMenuChevron.classList.toggle("rotate-180", isHidden);
        }
      } else {
        window.location.href = userBtn.dataset.target || "/login.php";
      }
    });
  }

  const userMenuCart = document.getElementById("user-menu-cart");
  if (userMenuCart) {
    userMenuCart.addEventListener("click", () => {
      if (userMenu) userMenu.classList.add("hidden");
      if (userMenuChevron) userMenuChevron.classList.remove("rotate-180");
      openCartModal();
    });
  }

  document.addEventListener("click", (e) => {
    if (!userMenu || userMenu.classList.contains("hidden")) return;
    if (!userMenu.contains(e.target) && !userBtn?.contains(e.target)) {
      userMenu.classList.add("hidden");
      if (userMenuChevron) userMenuChevron.classList.remove("rotate-180");
    }
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && userMenu && !userMenu.classList.contains("hidden")) {
      userMenu.classList.add("hidden");
      if (userMenuChevron) userMenuChevron.classList.remove("rotate-180");
    }
  });

  // Update Cart UI on page load to set correct counts
  updateCartUI();

  document.body.addEventListener("click", (e) => {
    const btn = e.target.closest(".btn-buy, .btn-overlay-cart, .btn-overlay-buy");
    if (!btn) return;

    const product = getProductFromButton(btn);
    if (!product || !product.name || Number.isNaN(product.price)) return;

    e.preventDefault();

    const isAddCart =
      btn.classList.contains("btn-overlay-cart") ||
      btn.id === "addCart" ||
      btn.textContent.trim().includes("Add to Cart");

    if (isAddCart) {
      addToCart(product.id, product.name, product.price, product.img, product.quantity);
      showToast("Added to Cart", `<strong>${product.name}</strong> has been added to your cart!`, "success");
    } else {
      addToCart(product.id, product.name, product.price, product.img, product.quantity);
      window.location.href = "/checkout.php";
    }
  });

  // Load More Button Alert Check
  const loadmore = document.getElementById("more-products");
  if (loadmore) {
    loadmore.addEventListener("click", () => {
      showModal("Coming Soon!", "We do not have any more products cataloged yet, but our team of arborists is working on growing new bonsai variations. Stay tuned!", "info");
    });
  }
});

function getProductFromButton(btn) {
  let product = null;

  if (btn.dataset.name) {
    product = {
      id: btn.dataset.id || btn.dataset.name.trim().toLowerCase().replace(/[^a-z0-9]/g, "-"),
      name: btn.dataset.name.trim(),
      price: parseFloat(btn.dataset.price),
      img: btn.dataset.img || "",
      quantity: 1,
    };
  } else {
    const gridItem = btn.closest(".product-grid-item");
    if (gridItem) {
      const titleEl = gridItem.querySelector(".product-grid-item-info h2");
      const priceEl = gridItem.querySelector(".product-grid-item-info .price h4");
      const imgEl = gridItem.querySelector(".product-grid-item-img img");
      const qtyInput = gridItem.querySelector(".product-qty-input");
      if (!titleEl || !priceEl || !imgEl) return null;

      const name = titleEl.textContent.trim();
      product = {
        id: name.toLowerCase().replace(/[^a-z0-9]/g, "-"),
        name,
        price: parseFloat(priceEl.textContent.replace(/Rs\./i, "").replace(/[^\d.]/g, "")),
        img: imgEl.getAttribute("src") || "",
        quantity: qtyInput ? parseInt(qtyInput.value, 10) || 1 : 1,
      };
    } else {
      const shopCard = btn.closest(".product-card");
      if (shopCard) {
        const nameEl = shopCard.querySelector(".card-name");
        const priceEl = shopCard.querySelector(".card-price");
        const imgEl = shopCard.querySelector(".card-img-wrap img, img");
        if (!nameEl || !priceEl || !imgEl) return null;

        const name = nameEl.textContent.trim();
        product = {
          id: shopCard.dataset.id || name.toLowerCase().replace(/[^a-z0-9]/g, "-"),
          name,
          price: parseFloat(priceEl.textContent.replace(/Rs\./i, "").replace(/[^\d.]/g, "")),
          img: imgEl.getAttribute("src") || "",
          quantity: 1,
        };
      }
    }
  }

  const gridItem = btn.closest(".product-grid-item");
  if (gridItem && product) {
    const qtyInput = gridItem.querySelector(".product-qty-input");
    if (qtyInput) {
      product.quantity = parseInt(qtyInput.value, 10) || 1;
    }
  }

  return product;
}

// Cart helper functions
function getCart() {
  try {
    return JSON.parse(localStorage.getItem("bagaicha_cart")) || [];
  } catch (e) {
    return [];
  }
}

function saveCart(cart) {
  localStorage.setItem("bagaicha_cart", JSON.stringify(cart));
  updateCartUI();
}

function addToCart(id, name, price, img, quantity = 1) {
  let cart = getCart();
  const existingItem = cart.find(item => item.id === id);

  if (existingItem) {
    existingItem.quantity += quantity;
  } else {
    cart.push({ id, name, price, img, quantity });
  }

  saveCart(cart);
}

function removeFromCart(id) {
  let cart = getCart();
  cart = cart.filter(item => item.id !== id);
  saveCart(cart);
}

function updateCartUI() {
  const cartBody = document.querySelector(".cart-popup-body");
  if (!cartBody) return;

  const cart = getCart();
  
  // Update Cart badge count if badge exists, else append it next to cart icon
  const cartOpenBtn = document.getElementById("showcart-btn");
  if (cartOpenBtn) {
    let badge = cartOpenBtn.querySelector(".cart-badge");
    const totalQty = cart.reduce((sum, item) => sum + item.quantity, 0);
    
    if (totalQty > 0) {
      if (!badge) {
        badge = document.createElement("span");
        badge.className = "cart-badge";
        cartOpenBtn.appendChild(badge);
      }
      badge.textContent = totalQty > 99 ? "99+" : String(totalQty);
    } else if (badge) {
      badge.remove();
    }
  }

  // Clear previous body content
  cartBody.innerHTML = "";

  if (cart.length === 0) {
    cartBody.innerHTML = `
      <div class="py-12 px-4 text-center">
        <p class="text-gray-500 text-sm mb-4">Your shopping cart is empty</p>
        <a href="/shop.php" class="inline-block bg-primary hover:bg-primary-dark text-white text-xs font-semibold px-4 py-2 rounded-xl transition-colors">Shop Bonsais</a>
      </div>
    `;
    return;
  }

  // Create list container
  const listContainer = document.createElement("div");
  listContainer.className = "cart-popup-body-content flex flex-col gap-4 w-full max-h-[350px] overflow-y-auto pr-1";

  let subtotal = 0;

  cart.forEach(item => {
    const itemTotal = item.price * item.quantity;
    subtotal += itemTotal;

    const itemRow = document.createElement("div");
    itemRow.className = "cart-popup-body-content-item flex items-center justify-between py-3 border-b border-gray-100 last:border-0";

    itemRow.innerHTML = `
      <div class="cart-popup-body-content-item-img w-14 h-14 rounded-xl overflow-hidden border border-gray-100 shrink-0 mr-3">
        <img src="${item.img}" alt="${item.name}" class="w-full h-full object-cover">
      </div>
      <div class="cart-popup-body-content-item-info flex-1 flex flex-col min-w-0">
        <h4 class="text-sm font-bold text-gray-800 truncate mb-0.5">${item.name}</h4>
        <div class="text-xs font-medium text-primary">Rs. ${item.price} <span class="text-gray-400 font-normal">x ${item.quantity}</span></div>
      </div>
      <div class="cart-popup-body-content-item-delete ml-2">
        <button class="btn delete-cart-item-btn p-1.5 bg-white hover:bg-red-50 text-gray-400 hover:text-red-500 rounded-lg border border-transparent hover:border-red-100 transition-colors cursor-pointer" data-id="${item.id}" type="button">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
          </svg>
        </button>
      </div>
    `;

    listContainer.appendChild(itemRow);
  });

  cartBody.appendChild(listContainer);

  // Add subtotal and checkout buttons
  const summaryBlock = document.createElement("div");
  summaryBlock.className = "w-full pt-4 border-t border-gray-100 mt-4";
  summaryBlock.innerHTML = `
    <div class="flex justify-between items-center font-bold text-sm text-gray-800 mb-4">
      <span>Subtotal:</span>
      <span class="text-primary text-base">Rs. ${subtotal}</span>
    </div>
    <div class="flex gap-3">
      <button class="flex-1 bg-white hover:bg-gray-50 text-gray-700 text-xs font-semibold px-4 py-2.5 rounded-xl border border-gray-200 transition-colors cursor-pointer" id="clear-cart-btn" type="button">Clear Cart</button>
      <a href="/checkout.php" class="flex-[2] bg-primary hover:bg-primary-dark text-white text-xs font-semibold px-4 py-2.5 rounded-xl transition-colors flex items-center justify-center">Proceed to Checkout</a>
    </div>
  `;

  cartBody.appendChild(summaryBlock);

  // Bind deletion handlers dynamically
  const deleteBtns = cartBody.querySelectorAll(".delete-cart-item-btn");
  deleteBtns.forEach(btn => {
    btn.addEventListener("click", () => {
      const id = btn.getAttribute("data-id");
      removeFromCart(id);
    });
  });

  // Bind clear cart handler safely
  const clearBtn = cartBody.querySelector("#clear-cart-btn");
  if (clearBtn) {
    clearBtn.addEventListener("click", () => {
      saveCart([]);
    });
  }
}

/**
 * Validates the contact form fields.
 * Runs on submit of contact form.
 */
function sendContact() {
  const fname = document.getElementById("fname");
  const lname = document.getElementById("lname");
  const email = document.getElementById("email");
  const subject = document.getElementById("subject");
  const message = document.getElementById("message");

  // Validate presence
  if (
    !fname || !fname.value.trim() ||
    !lname || !lname.value.trim() ||
    !email || !email.value.trim() ||
    !subject || !subject.value.trim() ||
    !message || !message.value.trim()
  ) {
    showToast("Validation Error", "Please fill in all fields!", "error");
    return false;
  }

  // Validate Email regex
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailPattern.test(email.value.trim())) {
    showToast("Validation Error", "Please enter a valid email address!", "error");
    return false;
  }

  showModal("Message Sent!", "Thank you! Your inquiry has been transmitted successfully. Our team will review and reply within 24 hours.", "success");
  return true;
}

// -------------------------------------------------------------
// Premium Custom Modal & Toast Notification Helpers
// -------------------------------------------------------------

function showToast(title, message, type = 'success') {
  // Create toast container if it doesn't exist
  let container = document.querySelector(".toast-container");
  if (!container) {
    container = document.createElement("div");
    container.className = "toast-container";
    document.body.appendChild(container);
  }

  // Create Toast
  const toast = document.createElement("div");
  toast.className = `custom-toast ${type}`;
  
  let iconHtml = "✓";
  if (type === "error") iconHtml = "✕";

  toast.innerHTML = `
    <div style="width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 15px; background: ${type === 'success' ? '#ebfbeb' : '#fdf2f2'}; color: ${type === 'success' ? '#5cb85c' : '#d9534f'}; border: 1.5px solid ${type === 'success' ? '#5cb85c' : '#d9534f'};">${iconHtml}</div>
    <div class="custom-toast-content">
      <div class="custom-toast-title">${title}</div>
      <div class="custom-toast-message">${message}</div>
    </div>
    <button class="custom-toast-close" type="button">&times;</button>
  `;

  container.appendChild(toast);

  // Close event listener
  const closeBtn = toast.querySelector(".custom-toast-close");
  closeBtn.addEventListener("click", () => {
    toast.style.animation = "toastSlideOut 0.3s ease forwards";
    setTimeout(() => toast.remove(), 300);
  });

  // Self destruct after 3.5 seconds
  setTimeout(() => {
    if (toast.parentNode) {
      toast.style.animation = "toastSlideOut 0.3s ease forwards";
      setTimeout(() => toast.remove(), 300);
    }
  }, 3500);
}

function showModal(title, message, type = 'info') {
  // Create modal overlay if it doesn't exist
  let overlay = document.querySelector(".custom-modal-overlay");
  if (!overlay) {
    overlay = document.createElement("div");
    overlay.className = "custom-modal-overlay";
    document.body.appendChild(overlay);
  }

  let iconText = "ℹ";
  if (type === "success") iconText = "✓";
  if (type === "error") iconText = "✕";

  overlay.innerHTML = `
    <div class="custom-modal-card" style="font-family: 'Poppins', sans-serif;">
      <div class="custom-modal-icon ${type}">${iconText}</div>
      <div class="custom-modal-title" style="font-size: 20px; font-weight: bold; color: #333; margin-bottom: 12px; font-family: 'Poppins', sans-serif;">${title}</div>
      <div class="custom-modal-message" style="font-size: 14px; color: #666; line-height: 1.6; margin-bottom: 25px; font-family: 'Poppins', sans-serif;">${message}</div>
      <button class="btn-hero custom-modal-close-btn" type="button" style="padding: 10px 24px; font-size: 14px; border: none; cursor: pointer; border-radius: 5px; font-family: 'Poppins', sans-serif; font-weight: 600;">Okay</button>
    </div>
  `;

  // Show modal
  setTimeout(() => overlay.classList.add("show"), 50);

  // Close event listeners
  const closeBtn = overlay.querySelector(".custom-modal-close-btn");
  closeBtn.addEventListener("click", () => {
    overlay.classList.remove("show");
  });

  overlay.addEventListener("click", (e) => {
    if (e.target === overlay) {
      overlay.classList.remove("show");
    }
  });
}

// Bind to window to allow page-specific scripts to access them
window.showToast = showToast;
window.showModal = showModal;
