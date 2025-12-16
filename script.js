// ============= STATE MANAGEMENT =============
let cart = JSON.parse(localStorage.getItem('cart')) || [];
let currentCategory = 'all';
let currentSort = 'featured';
let searchQuery = '';
let csrfToken = '';

// ============= OFFER BANNER =============
const offerMessages = [
    "🔥 Special Offer: Get 20% OFF on Gypsum Decor! Use code: GYPSUM20 🔥",
    "✨ Flash Sale: 30% OFF all Scented Candles Today Only! ✨",
    "💎 Exclusive: Buy 2 Get 1 FREE on Jewelry Collection! 💎",
    "🧼 Limited Time: 25% OFF Organic Soap & Care Products! 🧼",
    "🎁 Free Shipping on Orders Over ৳2000! Shop Now! 🎁",
    "⭐ New Arrival: Premium Concrete Decor - 15% OFF! ⭐",
    "💫 Weekend Special: Extra 10% OFF with code WEEKEND10! 💫"
];

function initOfferBanner() {
    const offerText = document.getElementById('offerText');
    if (!offerText) return;

    // Set initial random offer
    offerText.textContent = offerMessages[Math.floor(Math.random() * offerMessages.length)];

    // Rotate offers every 5 seconds
    setInterval(() => {
        const randomOffer = offerMessages[Math.floor(Math.random() * offerMessages.length)];
        offerText.style.opacity = '0';
        setTimeout(() => {
            offerText.textContent = randomOffer;
            offerText.style.opacity = '1';
        }, 300);
    }, 5000);
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    initOfferBanner();
    initSecurity(); // Check auth status
});

// ============= DOM ELEMENTS =============
const cartBtn = document.getElementById('cartBtn');
const cartSidebar = document.getElementById('cartSidebar');
const cartOverlay = document.getElementById('cartOverlay');
const closeCart = document.getElementById('closeCart');
const cartCount = document.getElementById('cartCount');
const cartItems = document.getElementById('cartItems');
const totalAmount = document.getElementById('totalAmount');
const productGrid = document.getElementById('productGrid');
const filterBtns = document.querySelectorAll('.filter-btn');
const sortSelect = document.getElementById('sortSelect');
const searchInput = document.getElementById('searchInput');
const searchResults = document.getElementById('searchResults');
const quickViewModal = document.getElementById('quickViewModal');
const modalOverlay = document.getElementById('modalOverlay');
const modalClose = document.getElementById('modalClose');
const mobileMenuToggle = document.getElementById('mobileMenuToggle');
const navMenu = document.getElementById('navMenu');

// Price Filter
const priceSlider = document.getElementById('priceSlider');
const priceValue = document.getElementById('priceValue');
let maxPrice = 10000;

if (priceSlider) {
    priceSlider.addEventListener('input', (e) => {
        maxPrice = parseInt(e.target.value);
        if (priceValue) {
            priceValue.textContent = `৳${maxPrice.toLocaleString()}`;
        }
        renderProducts();
    });
}

// Recently Viewed (localStorage)
function addToRecentlyViewed(productId) {
    let recent = JSON.parse(localStorage.getItem('recentlyViewed') || '[]');
    recent = recent.filter(id => id !== productId);
    recent.unshift(productId);
    recent = recent.slice(0, 6); // Keep last 6
    localStorage.setItem('recentlyViewed', JSON.stringify(recent));
}

// =============  INITIALIZATION =============

// Fetch CSRF token and User Profile
async function initSecurity() {
    try {
        // CSRF
        const csrfResponse = await fetch('api/get_csrf_token.php');
        const csrfData = await csrfResponse.json();
        if (csrfData.success) {
            csrfToken = csrfData.csrf_token;
        }

        // User Profile
        const userResponse = await fetch('api/get_user_profile.php');
        const userData = await userResponse.json();
        if (userData.loggedIn) {
            window.currentUser = userData.user;

            // Update UI headers
            const authLinks = document.querySelector('.header-links');
            if (authLinks) {
                authLinks.innerHTML = `
                    <a href="dashboard.php">User Dashboard</a>
                    <a href="logout.php">Logout</a>
                `;
            }
        }
    } catch (e) {
        console.error('Failed to init security/user', e);
    }
}

// Load products from database instead of products.js
async function loadProducts() {
    initSecurity(); // Start security init
    try {
        const response = await fetch('api/get_products.php');

        const data = await response.json();

        if (data.success && data.products) {
            window.productsData = data.products;
            renderProducts();
        } else {
            throw new Error('Failed to load products from database');
        }
    } catch (error) {
        console.error('Error loading products:', error);
        // Fallback to products.js if database fails
        if (window.productsData && Array.isArray(window.productsData)) {
            console.log('Using fallback products from products.js');
            renderProducts();
        } else {
            if (productGrid) {
                productGrid.innerHTML = '<p style="grid-column: 1/-1; text-align: center; color: var(--text-secondary); padding: 2rem;">Failed to load products. Please refresh the page.</p>';
            }
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Show loading state
    if (productGrid) {
        productGrid.innerHTML = '<p style="grid-column: 1/-1; text-align: center; color: var(--text-secondary); padding: 2rem;">Loading products...</p>';
    }

    // Load products from database
    loadProducts().then(() => {
        updateCartUI();
        initHeroSlider();
        initEventListeners();
    });
});

// ============= HERO SLIDER =============
function initHeroSlider() {
    const slides = document.querySelectorAll('.hero-slide');

    // Return early if no slides exist
    if (!slides.length) {
        return;
    }

    let currentSlide = 0;
    let autoplayInterval;

    function goToSlide(n) {
        slides[currentSlide].classList.remove('active');
        currentSlide = (n + slides.length) % slides.length;
        slides[currentSlide].classList.add('active');

        // Restart video on the new active slide
        const video = slides[currentSlide].querySelector('.hero-video');
        if (video) {
            video.currentTime = 0;
            video.play().catch(() => { }); // Silently ignore autoplay errors
        }
    }

    function nextSlide() {
        goToSlide(currentSlide + 1);
    }

    function startAutoplay() {
        // 8 seconds to match video duration
        autoplayInterval = setInterval(nextSlide, 8000);
    }

    startAutoplay();
}

// ============= EVENT LISTENERS =============
function initEventListeners() {
    // Cart sidebar
    if (cartBtn) cartBtn.addEventListener('click', () => toggleCart(true));
    if (closeCart) closeCart.addEventListener('click', () => toggleCart(false));
    if (cartOverlay) cartOverlay.addEventListener('click', () => toggleCart(false));

    // Wishlist sidebar
    const wishlistBtn = document.querySelector('.wishlist-btn');
    const closeWishlist = document.getElementById('closeWishlist');
    const wishlistOverlay = document.getElementById('wishlistOverlay');

    if (wishlistBtn) wishlistBtn.addEventListener('click', () => toggleWishlist(true));
    if (closeWishlist) closeWishlist.addEventListener('click', () => toggleWishlist(false));
    if (wishlistOverlay) wishlistOverlay.addEventListener('click', () => toggleWishlist(false));

    // Mobile menu
    if (mobileMenuToggle && navMenu) {
        mobileMenuToggle.addEventListener('click', () => {
            const isActive = navMenu.classList.toggle('active');
            mobileMenuToggle.setAttribute('aria-expanded', isActive);
        });
    }

    // Filter buttons
    if (filterBtns.length > 0) {
        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                filterBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                currentCategory = btn.dataset.category;
                renderProducts();
            });
        });
    }

    // Sort select
    if (sortSelect) {
        sortSelect.addEventListener('change', (e) => {
            currentSort = e.target.value;
            renderProducts();
        });
    }

    // Search
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            searchQuery = e.target.value.toLowerCase();
            if (searchQuery.length > 0) {
                showSearchResults();
            } else {
                hideSearchResults();
            }
        });

        // Close search results when clicking outside
        document.addEventListener('click', (e) => {
            if (searchResults && !searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                hideSearchResults();
            }
        });

        // Handle Enter key in search
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (searchQuery.length > 0) {
                    renderProducts();
                    hideSearchResults();
                }
            } else if (e.key === 'Escape') {
                hideSearchResults();
            }
        });
    }

    // Search button click
    const searchBtn = document.querySelector('.search-btn');
    if (searchBtn) {
        searchBtn.addEventListener('click', () => {
            if (searchQuery.length > 0) {
                renderProducts();
                hideSearchResults();
            }
        });
    }

    // Category cards click
    const categoryCards = document.querySelectorAll('.category-card');
    if (categoryCards.length > 0) {
        categoryCards.forEach(card => {
            card.addEventListener('click', () => {
                const category = card.dataset.category;
                if (category) {
                    currentCategory = category;
                    if (filterBtns.length > 0) {
                        filterBtns.forEach(btn => {
                            if (btn.dataset.category === category) {
                                btn.classList.add('active');
                            } else {
                                btn.classList.remove('active');
                            }
                        });
                    }
                    renderProducts();
                    // Scroll to products section
                    const shopSection = document.getElementById('shop');
                    if (shopSection) {
                        shopSection.scrollIntoView({ behavior: 'smooth' });
                    }
                }
            });
        });
    }

    // Navigation dropdown links
    const navCategoryLinks = document.querySelectorAll('.nav-menu a[data-category]');
    if (navCategoryLinks.length > 0) {
        navCategoryLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const category = link.dataset.category;
                currentCategory = category;
                if (filterBtns.length > 0) {
                    filterBtns.forEach(btn => {
                        if (btn.dataset.category === category) {
                            btn.classList.add('active');
                        } else {
                            btn.classList.remove('active');
                        }
                    });
                }
                renderProducts();
                const shopSection = document.getElementById('shop');
                if (shopSection) {
                    shopSection.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    }

    // Modal close
    if (modalClose) modalClose.addEventListener('click', closeQuickView);
    if (modalOverlay) modalOverlay.addEventListener('click', closeQuickView);

    // Close modal with Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && quickViewModal && quickViewModal.classList.contains('active')) {
            closeQuickView();
        }
        // Close cart with Escape key
        if (e.key === 'Escape' && cartSidebar && cartSidebar.classList.contains('active')) {
            toggleCart(false);
        }
    });

    // Newsletter form
    const newsletterForm = document.querySelector('.newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', (e) => {
            e.preventDefault();
            alert('Thank you for subscribing!');
            e.target.reset();
        });
    }

    // Checkout button handled by initCheckoutModal

    // Hero "Shop Now" links - smooth scroll to products section
    document.querySelectorAll('.hero-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const href = btn.getAttribute('href');
            if (href && href.startsWith('#')) {
                e.preventDefault();
                const targetId = href.substring(1);
                const targetElement = document.getElementById(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView({ behavior: 'smooth' });
                }
            }
        });
    });

    // Product card wishlist buttons (event delegation for dynamically created elements)
    if (productGrid) {
        productGrid.addEventListener('click', (e) => {
            const wishlistBtn = e.target.closest('.product-action-btn[title="Add to Wishlist"]');
            if (wishlistBtn) {
                e.preventDefault();
                e.stopPropagation();
                const productCard = wishlistBtn.closest('.product-card');
                // Find product ID from the add to cart button in the same card
                const addToCartBtn = productCard.querySelector('.add-to-cart-btn');
                if (addToCartBtn) {
                    const onclickAttr = addToCartBtn.getAttribute('onclick');
                    const productId = parseInt(onclickAttr.match(/addToCart\((\d+)\)/)[1]);
                    toggleWishlistItem(productId);
                }
            }
        });
    }
}

// ============= WISHLIST FUNCTIONS =============
let wishlist = JSON.parse(localStorage.getItem('wishlist')) || [];

function toggleWishlist(show) {
    const sidebar = document.getElementById('wishlistSidebar');
    const overlay = document.getElementById('wishlistOverlay');
    if (!sidebar || !overlay) return;

    if (show) {
        sidebar.classList.add('active');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
        updateWishlistUI();
    } else {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }
}

let wishlistDebounce = false;

function toggleWishlistItem(productId, event) {
    // Prevent event bubbling
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    // Debounce to prevent double-clicks
    if (wishlistDebounce) return;
    wishlistDebounce = true;
    setTimeout(() => { wishlistDebounce = false; }, 300);

    if (!window.productsData || !Array.isArray(window.productsData)) return;

    const index = wishlist.indexOf(productId);
    const product = window.productsData.find(p => p.id === productId);

    if (index === -1) {
        wishlist.push(productId);
        showToast(`Added ${product ? product.name : 'item'} to wishlist`);
    } else {
        wishlist.splice(index, 1);
        showToast(`Removed ${product ? product.name : 'item'} from wishlist`);
    }

    saveWishlist();
    updateWishlistUI();

    // Update just the heart icons instead of full re-render
    document.querySelectorAll('.product-action-btn[title="Add to Wishlist"]').forEach(btn => {
        const onclick = btn.getAttribute('onclick');
        if (onclick) {
            const match = onclick.match(/toggleWishlistItem\((\d+)/);
            if (match) {
                const id = parseInt(match[1]);
                const isInWishlist = wishlist.includes(id);
                btn.classList.toggle('active', isInWishlist);
                const svg = btn.querySelector('svg');
                if (svg) {
                    svg.setAttribute('fill', isInWishlist ? 'white' : 'none');
                }
            }
        }
    });
}

function saveWishlist() {
    localStorage.setItem('wishlist', JSON.stringify(wishlist));
}

function updateWishlistUI() {
    const wishlistItems = document.getElementById('wishlistItems');
    const wishlistBadge = document.querySelector('.wishlist-btn .badge');

    // Update badge
    if (wishlistBadge) {
        wishlistBadge.textContent = wishlist.length;
    }

    if (!wishlistItems) return;

    if (wishlist.length === 0) {
        wishlistItems.innerHTML = '<div class="empty-cart"><p>Your wishlist is empty</p></div>';
        return;
    }

    const itemsHTML = wishlist.map(id => {
        const product = window.productsData.find(p => p.id === id);
        if (!product) return '';

        // Handle image
        let imageHTML;
        if (product.image && product.image !== 'placeholder') {
            imageHTML = `<img src="${product.image}" alt="${product.name}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 0.5rem;" loading="lazy">`;
        } else {
            const initials = product.name.split(' ').map(w => w[0]).join('').slice(0, 2);
            imageHTML = `<div style="width: 100%; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 1rem; font-weight: 600; border-radius: 0.5rem;">${initials}</div>`;
        }

        return `
            <div class="cart-item">
                <div class="cart-item-image">
                    ${imageHTML}
                </div>
                <div class="cart-item-info">
                    <div class="cart-item-name">${product.name}</div>
                    <div class="cart-item-price">৳${product.price.toLocaleString()}</div>
                    <button class="add-to-cart-btn" onclick="addToCart(${product.id}); toggleWishlist(false);" style="padding: 0.5rem; font-size: 0.875rem; margin-top: 0.5rem;">
                        Add to Cart
                    </button>
                </div>
                <button class="remove-item" onclick="toggleWishlistItem(${product.id})">×</button>
            </div>
        `;
    }).join('');

    wishlistItems.innerHTML = itemsHTML;

    // Add error handlers
    wishlistItems.querySelectorAll('img').forEach(img => {
        img.addEventListener('error', function () {
            const itemName = this.alt || 'Item';
            const initials = itemName.split(' ').map(w => w[0]).join('').slice(0, 2);
            this.outerHTML = `<div style="width: 100%; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 1rem; font-weight: 600; border-radius: 0.5rem;">${initials}</div>`;
        });
    });
}

// ============= CART FUNCTIONS =============
function toggleCart(show) {
    if (!cartSidebar || !cartOverlay) return;

    if (show) {
        cartSidebar.classList.add('active');
        cartOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    } else {
        cartSidebar.classList.remove('active');
        cartOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }
}

function addToCart(productId) {
    if (!window.productsData || !Array.isArray(window.productsData)) return;

    const product = window.productsData.find(p => p.id === productId);
    if (!product) return;

    // Check if product is in stock
    const isInStock = product.inStock !== false && product.inStock !== 0 && product.in_stock !== 0;
    if (!isInStock) {
        showToast(`Sorry, ${product.name} is out of stock`);
        return;
    }

    const existingItem = cart.find(item => item.id === productId);

    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({ ...product, quantity: 1 });
    }

    saveCart();
    updateCartUI();
    showToast(`Added ${product.name} to cart`);
}

function showToast(message) {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.innerHTML = `
        <div class="toast-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </div>
        <span>${message}</span>
    `;

    container.appendChild(toast);

    // Remove toast after 3 seconds
    setTimeout(() => {
        toast.classList.add('hiding');
        toast.addEventListener('animationend', () => {
            toast.remove();
            if (container.children.length === 0) {
                container.remove();
            }
        });
    }, 3000);
}

function removeFromCart(productId) {
    cart = cart.filter(item => item.id !== productId);
    saveCart();
    updateCartUI();
}

function updateQuantity(productId, change) {
    const item = cart.find(item => item.id === productId);
    if (!item) return;

    item.quantity += change;
    if (item.quantity <= 0) {
        removeFromCart(productId);
    } else {
        saveCart();
        updateCartUI();
    }
}

function saveCart() {
    localStorage.setItem('cart', JSON.stringify(cart));
}

function updateCartUI() {
    // Update cart count
    if (cartCount) {
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        cartCount.textContent = totalItems;
    }

    if (!cartItems) return;

    // Update cart items
    if (cart.length === 0) {
        cartItems.innerHTML = '<div class="empty-cart"><p>Your cart is empty</p></div>';
        if (totalAmount) {
            totalAmount.textContent = '৳0';
        }
        return;
    }

    const itemsHTML = cart.map(item => {
        // Handle cart item image
        let imageHTML;
        if (item.image && item.image !== 'placeholder') {
            imageHTML = `<img src="${item.image}" alt="${item.name}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 0.5rem;" loading="lazy">`;
        } else {
            const initials = item.name.split(' ').map(w => w[0]).join('').slice(0, 2);
            imageHTML = `<div style="width: 100%; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 1rem; font-weight: 600; border-radius: 0.5rem;">${initials}</div>`;
        }

        return `
            <div class="cart-item">
                <div class="cart-item-image">
                    ${imageHTML}
                </div>
                <div class="cart-item-info">
                    <div class="cart-item-name">${item.name}</div>
                    <div class="cart-item-price">৳${item.price.toLocaleString()}</div>
                    <div class="cart-item-quantity">
                        <button class="qty-btn" onclick="updateQuantity(${item.id}, -1)">-</button>
                        <span>${item.quantity}</span>
                        <button class="qty-btn" onclick="updateQuantity(${item.id}, 1)">+</button>
                    </div>
                </div>
                <button class="remove-item" onclick="removeFromCart(${item.id})">×</button>
            </div>
        `;
    }).join('');

    cartItems.innerHTML = itemsHTML;

    // Add error handlers for cart item images
    cartItems.querySelectorAll('img').forEach(img => {
        img.addEventListener('error', function () {
            const itemName = this.alt || 'Item';
            const initials = itemName.split(' ').map(w => w[0]).join('').slice(0, 2);
            this.outerHTML = `<div style="width: 100%; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 1rem; font-weight: 600; border-radius: 0.5rem;">${initials}</div>`;
        });
    });

    // Update total
    if (totalAmount) {
        const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        totalAmount.textContent = `৳${total.toLocaleString()}`;
    }
}

function showAddedToCartAnimation() {
    if (!cartBtn) return;

    cartBtn.style.animation = 'none';
    setTimeout(() => {
        if (cartBtn) {
            cartBtn.style.animation = 'pulse 0.5s ease';
        }
    }, 10);
}

// ============= PRODUCT FUNCTIONS =============
function renderProducts() {
    if (!window.productsData || !Array.isArray(window.productsData)) {
        if (productGrid) {
            productGrid.innerHTML = '<p style="grid-column: 1/-1; text-align: center; color: var(--text-secondary); padding: 2rem;">Products loading...</p>';
        }
        return;
    }

    let filteredProducts = [...window.productsData];

    // Apply category filter
    if (currentCategory !== 'all') {
        filteredProducts = filteredProducts.filter(p => p.category === currentCategory);
    }

    // Apply search filter
    if (searchQuery) {
        filteredProducts = filteredProducts.filter(p =>
            p.name.toLowerCase().includes(searchQuery) ||
            p.description.toLowerCase().includes(searchQuery) ||
            p.category.toLowerCase().includes(searchQuery)
        );
    }

    // Apply price filter
    if (typeof maxPrice !== 'undefined') {
        filteredProducts = filteredProducts.filter(p => p.price <= maxPrice);
    }

    // Apply sorting
    switch (currentSort) {
        case 'price-low':
            filteredProducts.sort((a, b) => a.price - b.price);
            break;
        case 'price-high':
            filteredProducts.sort((a, b) => b.price - a.price);
            break;
        case 'name':
            filteredProducts.sort((a, b) => a.name.localeCompare(b.name));
            break;
        case 'rating':
            filteredProducts.sort((a, b) => b.rating - a.rating);
            break;
    }

    // Render products
    if (productGrid) {
        const productsHTML = filteredProducts.map(product => createProductCard(product)).join('');
        productGrid.innerHTML = productsHTML || '<p style="grid-column: 1/-1; text-align: center; color: var(--text-secondary); padding: 2rem;">No products found</p>';

        // Add error handlers for all product images
        productGrid.querySelectorAll('img').forEach(img => {
            img.addEventListener('error', function () {
                const productName = this.alt || 'Product';
                const initials = productName.split(' ').map(w => w[0]).join('').slice(0, 2);
                this.outerHTML = `<div style="width: 100%; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; font-weight: 600;">${initials}</div>`;
            });
        });
    }
}

function createProductCard(product) {
    const stars = '★'.repeat(Math.floor(product.rating)) + '☆'.repeat(5 - Math.floor(product.rating));

    // Handle product image - use real image if available, otherwise use gradient placeholder
    let imageHTML;
    if (product.image && product.image !== 'placeholder') {
        imageHTML = `<img src="${product.image}" alt="${product.name}" style="width: 100%; height: 100%; object-fit: cover;" loading="lazy">`;
    } else {
        const initials = product.name.split(' ').map(w => w[0]).join('').slice(0, 2);
        imageHTML = `<div style="width: 100%; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; font-weight: 600;">${initials}</div>`;
    }

    const isInWishlist = wishlist.includes(product.id);

    // Check if product is in stock
    const isInStock = product.inStock !== false && product.inStock !== 0 && product.in_stock !== 0;

    // Badge - show "Out of Stock" if not in stock, otherwise show product badge
    let badgeHTML = '';
    if (!isInStock) {
        badgeHTML = `<div class="product-badge" style="background: #ef4444;">Out of Stock</div>`;
    } else if (product.badge) {
        badgeHTML = `<div class="product-badge">${product.badge}</div>`;
    }

    // Add to cart button - disabled if out of stock
    let cartButtonHTML;
    if (isInStock) {
        cartButtonHTML = `<button class="add-to-cart-btn" onclick="addToCart(${product.id})">Add to Cart</button>`;
    } else {
        cartButtonHTML = `<button class="add-to-cart-btn" disabled style="background: #9ca3af; cursor: not-allowed;">Out of Stock</button>`;
    }

    return `
        <div class="product-card ${!isInStock ? 'out-of-stock' : ''}">
            <div class="product-image" ${!isInStock ? 'style="opacity: 0.7;"' : ''}>
                ${imageHTML}
                ${badgeHTML}
                <div class="product-actions">
                    <button class="product-action-btn" onclick="openQuickView(${product.id})" title="Quick View">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                    <button class="product-action-btn ${isInWishlist ? 'active' : ''}" onclick="toggleWishlistItem(${product.id}, event)" title="Add to Wishlist">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="${isInWishlist ? 'white' : 'none'}" stroke="currentColor" stroke-width="2">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="product-info">
                <div class="product-category">${product.category.charAt(0).toUpperCase() + product.category.slice(1)}</div>
                <h3 class="product-name">${product.name}</h3>
                <div class="product-rating">
                    <span class="stars">${stars}</span>
                    <span class="rating-count">(${product.reviews})</span>
                </div>
                <div class="product-price">
                    <span class="current-price">৳${product.price.toLocaleString()}</span>
                    ${product.originalPrice ? `<span class="original-price">৳${product.originalPrice.toLocaleString()}</span>` : ''}
                </div>
                ${cartButtonHTML}
            </div>
        </div>
    `;
}


// ============= SEARCH FUNCTIONS =============
let searchTimeout;

function showSearchResults() {
    if (searchTimeout) clearTimeout(searchTimeout);

    searchTimeout = setTimeout(async () => {
        if (!searchResults) return;

        if (searchQuery.length < 2) {
            hideSearchResults();
            return;
        }

        try {
            const response = await fetch(`api/search_products.php?q=${encodeURIComponent(searchQuery)}`);
            const data = await response.json();

            if (data.success && data.products.length > 0) {
                const resultsHTML = data.products.map(product => {
                    const imageSrc = product.image_path || product.image || 'https://via.placeholder.com/50';
                    const isOutOfStock = !product.in_stock;
                    return `
                    <div class="search-result-item" onclick="scrollToProductAndHighlight(${product.id}); hideSearchResults();" style="${isOutOfStock ? 'opacity: 0.6;' : ''}">
                        <img src="${imageSrc}" alt="${product.name}" class="search-result-image" 
                             style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px;"
                             onerror="this.src='https://via.placeholder.com/50'">
                        <div class="search-result-info">
                            <div class="search-result-name">${product.name}</div>
                            <div class="search-result-price">
                                ৳${Number(product.price).toLocaleString()}
                                ${isOutOfStock ? '<span style="color: #ef4444; margin-left: 8px; font-size: 0.75rem;">Out of Stock</span>' : ''}
                            </div>
                        </div>
                    </div>
                `}).join('');

                searchResults.innerHTML = resultsHTML;
            } else {
                searchResults.innerHTML = '<div style="padding: 1rem; text-align: center; color: var(--text-secondary);">No products found</div>';
            }
            searchResults.classList.add('active');
        } catch (e) {
            console.error('Search error', e);
            // Fallback to local search
            if (window.productsData && Array.isArray(window.productsData)) {
                const results = window.productsData.filter(p =>
                    p.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
                    (p.description && p.description.toLowerCase().includes(searchQuery.toLowerCase())) ||
                    (p.category && p.category.toLowerCase().includes(searchQuery.toLowerCase()))
                ).slice(0, 8);

                if (results.length > 0) {
                    const resultsHTML = results.map(product => {
                        const imageSrc = product.image || 'https://via.placeholder.com/50';
                        const isOutOfStock = product.inStock === false || product.inStock === 0;
                        return `
                        <div class="search-result-item" onclick="scrollToProductAndHighlight(${product.id}); hideSearchResults();" style="${isOutOfStock ? 'opacity: 0.6;' : ''}">
                            <img src="${imageSrc}" alt="${product.name}" class="search-result-image" 
                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px;"
                                 onerror="this.src='https://via.placeholder.com/50'">
                            <div class="search-result-info">
                                <div class="search-result-name">${product.name}</div>
                                <div class="search-result-price">
                                    ৳${Number(product.price).toLocaleString()}
                                    ${isOutOfStock ? '<span style="color: #ef4444; margin-left: 8px; font-size: 0.75rem;">Out of Stock</span>' : ''}
                                </div>
                            </div>
                        </div>
                    `}).join('');
                    searchResults.innerHTML = resultsHTML;
                } else {
                    searchResults.innerHTML = '<div style="padding: 1rem; text-align: center; color: var(--text-secondary);">No products found</div>';
                }
                searchResults.classList.add('active');
            }
        }
    }, 300); // Debounce 300ms
}

// Helper function to scroll to product and show it
function scrollToProductAndHighlight(productId) {
    // First scroll to shop section
    const shopSection = document.getElementById('shop');
    if (shopSection) {
        shopSection.scrollIntoView({ behavior: 'smooth' });
    }

    // Filter to show the product category or open quick view
    setTimeout(() => {
        openQuickView(productId);
    }, 500);
}

function hideSearchResults() {
    if (searchResults) {
        searchResults.classList.remove('active');
    }
    if (searchInput) {
        searchInput.value = '';
    }
    searchQuery = '';
}

// ============= QUICK VIEW MODAL =============
function openQuickView(productId) {
    if (!window.productsData || !Array.isArray(window.productsData)) return;

    const product = window.productsData.find(p => p.id === productId);
    if (!product || !quickViewModal) return;

    const stars = '★'.repeat(Math.floor(product.rating)) + '☆'.repeat(5 - Math.floor(product.rating));
    const discount = Math.round(((product.originalPrice - product.price) / product.originalPrice) * 100);

    // Handle product image in modal
    let imageHTML;
    if (product.image && product.image !== 'placeholder') {
        imageHTML = `<img src="${product.image}" alt="${product.name}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 1rem;" loading="lazy">`;
    } else {
        const initials = product.name.split(' ').map(w => w[0]).join('').slice(0, 2);
        imageHTML = `<div style="width: 100%; height: 400px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 1rem; display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem; font-weight: 600;">${initials}</div>`;
    }

    const modalContent = `
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            <div>${imageHTML}</div>
            <div style="display: flex; flex-direction: column;">
                <div style="font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 0.5rem;">
                    ${product.category.charAt(0).toUpperCase() + product.category.slice(1)}
                </div>
                <h2 style="font-family: var(--font-heading); font-size: 2rem; margin-bottom: 1rem;">
                    ${product.name}
                </h2>
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                    <span style="color: #fbbf24; font-size: 1.25rem;">${stars}</span>
                    <span style="color: var(--text-secondary);">${product.rating} (${product.reviews} reviews)</span>
                </div>
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                    <span style="font-size: 2.5rem; font-weight: 700; color: var(--primary-color);">
                        ৳${product.price.toLocaleString()}
                    </span>
                    <span style="font-size: 1.5rem; color: var(--text-light); text-decoration: line-through;">
                        ৳${product.originalPrice.toLocaleString()}
                    </span>
                    <span style="background: var(--gradient-secondary); color: white; padding: 0.375rem 0.875rem; border-radius: 50px; font-size: 0.875rem; font-weight: 600;">
                        -${discount}%
                    </span>
                </div>
                <p style="color: var(--text-secondary); line-height: 1.8; margin-bottom: 2rem;">
                    ${product.description}
                </p>
                <div style="display: flex; gap: 1rem;">
                    <button onclick="addToCart(${product.id}); closeQuickView();" 
                            style="flex: 1; padding: 1rem; background: var(--gradient-primary); color: white; border: none; border-radius: 0.5rem; font-weight: 600; font-size: 1rem; cursor: pointer; transition: all 0.3s;">
                        Add to Cart
                    </button>
                    <button style="padding: 1rem; background: white; color: var(--primary-color); border: 2px solid var(--primary-color); border-radius: 0.5rem; font-weight: 600; cursor: pointer;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    `;

    const modalBody = document.getElementById('modalBody');
    if (modalBody) {
        modalBody.innerHTML = modalContent;

        // Add error handler for modal image
        const modalImg = modalBody.querySelector('img');
        if (modalImg) {
            modalImg.addEventListener('error', function () {
                const initials = product.name.split(' ').map(w => w[0]).join('').slice(0, 2);
                this.outerHTML = `<div style="width: 100%; height: 400px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 1rem; display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem; font-weight: 600;">${initials}</div>`;
            });
        }


        // Append Reviews Section
        const reviewsContainer = document.createElement('div');
        reviewsContainer.className = 'reviews-section';
        reviewsContainer.innerHTML = `
            <div class="reviews-header">
                <h3>Customer Reviews</h3>
                <span class="rating-summary">★ ${product.rating || 'New'}</span>
            </div>
            <div class="review-list" id="reviewList">Loading reviews...</div>
            <form id="reviewForm" class="review-form">
                ${window.currentUser ? `
                    <h4>Write a Review</h4>
                    <div class="rating-input">
                        <input type="radio" id="star5" name="rating" value="5"><label for="star5">★</label>
                        <input type="radio" id="star4" name="rating" value="4"><label for="star4">★</label>
                        <input type="radio" id="star3" name="rating" value="3"><label for="star3">★</label>
                        <input type="radio" id="star2" name="rating" value="2"><label for="star2">★</label>
                        <input type="radio" id="star1" name="rating" value="1"><label for="star1">★</label>
                    </div>
                    <input type="text" name="title" placeholder="Review Title" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem; margin-bottom: 0.5rem;">
                    <textarea name="comment" rows="3" placeholder="Share your thoughts..." required></textarea>
                    <button type="submit" class="save-btn" style="margin-top: 0.5rem;">Submit Review</button>
                ` : `<p><a href="login.php" style="color: var(--primary-color); text-decoration: underline;">Login</a> to write a review.</p>`}
            </form>
        `;
        modalBody.appendChild(reviewsContainer);

        // Fetch Reviews
        fetch(`api/reviews.php?product_id=${productId}`)
            .then(res => res.json())
            .then(data => {
                const list = document.getElementById('reviewList');
                if (data.reviews && data.reviews.length > 0) {
                    list.innerHTML = data.reviews.map(r => `
                        <div class="review-item">
                            <div class="review-header">
                                <span class="review-user">${r.user_name}</span>
                                <span class="review-date">${new Date(r.created_at).toLocaleDateString()}</span>
                            </div>
                            <div class="review-rating">${'★'.repeat(r.rating)}</div>
                            <div class="review-text"><strong>${r.title || ''}</strong><br>${r.comment || ''}</div>
                        </div>
                    `).join('');
                } else {
                    list.innerHTML = '<p style="color: var(--text-secondary);">No reviews yet. Be the first!</p>';
                }
            });

        // Handle Review Submission
        const reviewForm = document.getElementById('reviewForm');
        if (reviewForm && window.currentUser) {
            reviewForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(reviewForm);
                const rating = formData.get('rating');

                if (!rating) {
                    showToast('Please select a star rating', 'error');
                    return;
                }

                try {
                    const res = await fetch('api/reviews.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            product_id: productId,
                            rating: parseInt(rating),
                            title: formData.get('title'),
                            comment: formData.get('comment')
                        })
                    });
                    const result = await res.json();
                    if (result.success) {
                        showToast('Review submitted!');
                        openQuickView(productId); // Reload to show new review
                    } else {
                        showToast(result.error || 'Failed to submit', 'error');
                    }
                } catch (err) {
                    showToast('Error submitting review', 'error');
                }
            });
        }

        // Append Related Section Container
        const relatedContainer = document.createElement('div');
        relatedContainer.className = 'related-products-section';
        relatedContainer.innerHTML = '<h3>You May Also Like</h3><div class="related-grid" id="relatedGrid">Loading...</div>';
        modalBody.appendChild(relatedContainer);

        // Stock Alert Logic (Update the Add to Cart button if OOS)
        if (!product.inStock || product.in_stock === 0 || product.in_stock === false) {
            const btnContainer = modalBody.querySelector('button[onclick^="addToCart"]').parentNode;
            if (btnContainer) {
                btnContainer.innerHTML = `
                    <div style="width: 100%;">
                        <p style="color: #ef4444; font-weight: 600; margin-bottom: 0.5rem;">Out of Stock</p>
                        <form class="stock-alert-form" onsubmit="event.preventDefault(); showToast('We will notify you when back in stock!', 'success');">
                            <input type="email" placeholder="Enter your email" required style="flex: 1; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                            <button type="submit" class="btn-notify">Notify Me</button>
                        </form>
                    </div>
                 `;
            }
        }

        // Fetch Related
        fetch(`api/related_products.php?id=${productId}`)
            .then(res => res.json())
            .then(data => {
                const grid = document.getElementById('relatedGrid');
                if (data.success && data.products.length > 0) {
                    grid.innerHTML = data.products.map(p => `
                        <div class="related-card" onclick="openQuickView(${p.id})">
                            <img src="${p.image_path}" onerror="this.src='https://via.placeholder.com/100'">
                            <div class="related-info">
                                <h4>${p.name}</h4>
                                <span>৳${p.price.toLocaleString()}</span>
                            </div>
                        </div>
                    `).join('');
                } else {
                    relatedContainer.style.display = 'none';
                }
            });
    }

    quickViewModal.classList.add('active');
    quickViewModal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';

    // Focus the close button for keyboard navigation
    const closeBtn = document.getElementById('modalClose');
    if (closeBtn) {
        setTimeout(() => closeBtn.focus(), 100);
    }
}

function closeQuickView() {
    if (!quickViewModal) return;

    quickViewModal.classList.remove('active');
    quickViewModal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
}

// ============= ADD ANIMATION STYLES =============
const style = document.createElement('style');
style.textContent = `
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }
`;
document.head.appendChild(style);

// ============= USER PROFILE =============
const accountModal = document.getElementById('accountModal');
const accountModalOverlay = document.getElementById('accountModalOverlay');
const accountModalClose = document.getElementById('accountModalClose');
const accountForm = document.getElementById('accountForm');
const accountLink = document.querySelector('a[href="#account"]');

let userProfile = JSON.parse(localStorage.getItem('userProfile')) || {
    name: '',
    email: '',
    phone: '',
    address: ''
};

function initUserProfile() {
    if (accountLink) {
        accountLink.addEventListener('click', (e) => {
            e.preventDefault();
            openAccountModal();
        });
    }

    if (accountModalClose) {
        accountModalClose.addEventListener('click', closeAccountModal);
    }

    if (accountModalOverlay) {
        accountModalOverlay.addEventListener('click', closeAccountModal);
    }

    if (accountForm) {
        accountForm.addEventListener('submit', saveUserProfile);
    }
}

function openAccountModal() {
    if (!accountModal) return;

    // Populate form
    document.getElementById('userName').value = userProfile.name || '';
    document.getElementById('userEmail').value = userProfile.email || '';
    document.getElementById('userPhone').value = userProfile.phone || '';
    document.getElementById('userAddress').value = userProfile.address || '';

    accountModal.classList.add('active');
    accountModal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
}

function closeAccountModal() {
    if (!accountModal) return;
    accountModal.classList.remove('active');
    accountModal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
}

async function saveUserProfile(e) {
    e.preventDefault();

    userProfile = {
        name: document.getElementById('userName').value,
        email: document.getElementById('userEmail').value,
        phone: document.getElementById('userPhone').value,
        address: document.getElementById('userAddress').value
    };

    // Save to backend
    try {
        const response = await fetch('api/register_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(userProfile)
        });

        const result = await response.json();

        if (result.success) {
            // Save to localStorage
            localStorage.setItem('userProfile', JSON.stringify(userProfile));

            // Show success message
            if (typeof showToast === 'function') {
                showToast(result.message || 'Account created successfully!');
            } else {
                alert(result.message || 'Account created successfully!');
            }

            closeAccountModal();
        } else {
            throw new Error(result.message || 'Registration failed');
        }
    } catch (error) {
        console.error('Registration error:', error);
        if (typeof showToast === 'function') {
            showToast('Error: ' + error.message);
        } else {
            alert('Error: ' + error.message);
        }
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', initUserProfile);

// ============= CONTACT MODAL =============
const contactModal = document.getElementById('contactModal');
const contactModalOverlay = document.getElementById('contactModalOverlay');
const contactModalClose = document.getElementById('contactModalClose');
const contactLinks = document.querySelectorAll('a[href="#contact"]');

function initContactModal() {
    // Use event delegation for contact links
    document.addEventListener('click', (e) => {
        const link = e.target.closest('a[href="#contact"]');
        if (link) {
            e.preventDefault();
            openContactModal();
        }
    });

    if (contactModalClose) {
        contactModalClose.addEventListener('click', closeContactModal);
    }

    if (contactModalOverlay) {
        contactModalOverlay.addEventListener('click', closeContactModal);
    }
}

function openContactModal() {
    if (!contactModal) return;
    contactModal.classList.add('active');
    contactModal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
}

function closeContactModal() {
    if (!contactModal) return;
    contactModal.classList.remove('active');
    contactModal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
}

// Initialize
document.addEventListener('DOMContentLoaded', initContactModal);

// ============= ABOUT MODAL =============
const aboutModal = document.getElementById('aboutModal');
const aboutModalOverlay = document.getElementById('aboutModalOverlay');
const aboutModalClose = document.getElementById('aboutModalClose');
const aboutLinks = document.querySelectorAll('a[href="#about"]');

function initAboutModal() {
    // Use event delegation for about links
    document.addEventListener('click', (e) => {
        const link = e.target.closest('a[href="#about"]');
        if (link) {
            e.preventDefault();
            openAboutModal();
        }
    });

    if (aboutModalClose) {
        aboutModalClose.addEventListener('click', closeAboutModal);
    }

    if (aboutModalOverlay) {
        aboutModalOverlay.addEventListener('click', closeAboutModal);
    }
}

function openAboutModal() {
    if (!aboutModal) return;
    aboutModal.classList.add('active');
    aboutModal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
}

function closeAboutModal() {
    if (!aboutModal) return;
    aboutModal.classList.remove('active');
    aboutModal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
}

// Initialize
document.addEventListener('DOMContentLoaded', initAboutModal);

// ============= CHECKOUT MODAL =============
const checkoutModal = document.getElementById('checkoutModal');
const checkoutModalOverlay = document.getElementById('checkoutModalOverlay');
const checkoutModalClose = document.getElementById('checkoutModalClose');
const checkoutForm = document.getElementById('checkoutForm');
const checkoutBtn = document.querySelector('.checkout-btn');

function initCheckoutModal() {
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', () => {
            if (cart.length === 0) {
                showToast('Your cart is empty');
                return;
            }
            openCheckoutModal();
        });
    }

    if (checkoutModalClose) {
        checkoutModalClose.addEventListener('click', closeCheckoutModal);
    }

    if (checkoutModalOverlay) {
        checkoutModalOverlay.addEventListener('click', closeCheckoutModal);
    }

    if (checkoutForm) {
        checkoutForm.addEventListener('submit', handleCheckoutSubmit);
    }
}

function openCheckoutModal() {
    if (!checkoutModal) return;

    // Generate Order ID
    const orderId = 'ORD-' + Math.floor(Math.random() * 1000000).toString().padStart(6, '0');
    document.getElementById('checkoutOrderId').textContent = orderId;
    document.getElementById('bkashReference').textContent = orderId;

    // Calculate Total
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    window.checkoutSubtotal = subtotal;
    window.checkoutDiscount = 0;
    window.appliedCoupon = null;

    updateCheckoutTotal();

    // Pre-fill user data
    // If user is logged in, these values might be available globally or we fetch them
    if (window.currentUser) {
        document.getElementById('checkoutName').value = window.currentUser.name || '';
        document.getElementById('checkoutPhone').value = window.currentUser.phone || '';
        document.getElementById('checkoutEmail').value = window.currentUser.email || '';
        document.getElementById('checkoutAddress').value = window.currentUser.address || '';
    } else {
        document.getElementById('checkoutName').value = userProfile.name || '';
        document.getElementById('checkoutPhone').value = userProfile.phone || '';
        document.getElementById('checkoutEmail').value = userProfile.email || '';
        document.getElementById('checkoutAddress').value = userProfile.address || '';
    }

    checkoutModal.classList.add('active');
    checkoutModal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';

    // Close cart sidebar if open
    toggleCart(false);
}

function updateCheckoutTotal() {
    const total = window.checkoutSubtotal - (window.checkoutDiscount || 0);
    const totalText = `৳${total.toLocaleString()}`;

    // Update all total displays
    const checkoutTotal = document.getElementById('checkoutTotal');
    if (checkoutTotal) checkoutTotal.textContent = totalText;

    // Update payment method amounts
    const bkashAmount = document.getElementById('bkashAmount');
    const nagadAmount = document.getElementById('nagadAmount');
    const cardAmount = document.getElementById('cardAmount');
    const codAmount = document.getElementById('codAmount');

    if (bkashAmount) bkashAmount.textContent = totalText;
    if (nagadAmount) nagadAmount.textContent = totalText;
    if (cardAmount) cardAmount.textContent = totalText;
    if (codAmount) codAmount.textContent = totalText;

    // Show discount if applied
    const discountEl = document.getElementById('checkoutDiscount');
    if (discountEl) {
        if (window.checkoutDiscount > 0) {
            discountEl.style.display = 'flex';
            discountEl.querySelector('span:last-child').textContent = `-৳${window.checkoutDiscount.toLocaleString()}`;
        } else {
            discountEl.style.display = 'none';
        }
    }
}

async function applyCoupon() {
    const couponInput = document.getElementById('couponCode');
    const code = couponInput.value.trim();

    if (!code) {
        showToast('Please enter a coupon code', 'error');
        return;
    }

    try {
        const response = await fetch('api/validate_coupon.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ code, cart_total: window.checkoutSubtotal })
        });
        const data = await response.json();

        if (data.success) {
            window.checkoutDiscount = data.coupon.discount_amount;
            window.appliedCoupon = data.coupon.code;
            updateCheckoutTotal();
            showToast(`Coupon applied! ${data.coupon.discount_text}`, 'success');
            couponInput.disabled = true;
            document.querySelector('.apply-coupon-btn').textContent = '✓ Applied';
        } else {
            showToast(data.error || 'Invalid coupon', 'error');
        }
    } catch (e) {
        showToast('Error applying coupon', 'error');
    }
}

function closeCheckoutModal() {
    if (!checkoutModal) return;
    checkoutModal.classList.remove('active');
    checkoutModal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
}

async function handleCheckoutSubmit(e) {
    e.preventDefault();

    const orderId = document.getElementById('checkoutOrderId').textContent;

    // Prepare order data
    const orderData = {
        csrf_token: csrfToken, // Add CSRF token for security
        customer_name: document.getElementById('checkoutName').value,
        customer_email: document.getElementById('checkoutEmail').value,
        customer_phone: document.getElementById('checkoutPhone').value,
        customer_address: document.getElementById('checkoutAddress').value,
        payment_method: 'bKash',
        items: cart.map(item => ({
            id: item.id,
            name: item.name,
            price: item.price,
            quantity: item.quantity
        }))
    };

    try {
        // Show loading state
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Processing...';

        const response = await fetch('api/submit_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(orderData)
        });

        const result = await response.json();

        if (result.success) {
            // Clear cart
            cart = [];
            saveCart();
            updateCartUI();

            closeCheckoutModal();

            // Show success message with order ID
            alert(`Order ${result.order_id} placed successfully!\n\nTotal: ৳${result.total_amount.toLocaleString()}\n\nThank you for shopping with Flex & Bliss.\nYou will receive a confirmation via email.`);
        } else {
            throw new Error(result.message || 'Order submission failed');
        }

        // Restore button
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;

    } catch (error) {
        console.error('Checkout error:', error);

        // Restore button
        const submitBtn = e.target.querySelector('button[type="submit"]');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Confirm Order';

        alert('Error submitting order: ' + error.message + '\n\nPlease try again or contact support.');
    }
}

document.addEventListener('DOMContentLoaded', initCheckoutModal);

// ============= ADVANCED CHECKOUT FUNCTIONS =============

let currentCheckoutStep = 1;
let selectedPaymentMethod = null;

function goToCheckoutStep(step) {
    currentCheckoutStep = step;

    // Update step indicators
    for (let i = 1; i <= 4; i++) {
        const indicator = document.getElementById(`step${i}-indicator`);
        const content = document.getElementById(`checkout-step-${i}`);
        const lines = document.querySelectorAll('.step-line');

        if (indicator) {
            indicator.classList.remove('active', 'completed');
            if (i < step) {
                indicator.classList.add('completed');
            } else if (i === step) {
                indicator.classList.add('active');
            }
        }

        if (content) {
            content.classList.remove('active');
            if (i === step) {
                content.classList.add('active');
            }
        }
    }

    // Update step lines
    const lines = document.querySelectorAll('.step-line');
    lines.forEach((line, index) => {
        if (index < step - 1) {
            line.classList.add('completed');
        } else {
            line.classList.remove('completed');
        }
    });

    // If step 1, render cart items
    if (step === 1) {
        renderCheckoutCartItems();
    }

    // Hide payment UIs when going back
    if (step !== 3) {
        hideAllPaymentUIs();
    }
}

function renderCheckoutCartItems() {
    const container = document.getElementById('checkoutCartItems');
    if (!container) return;

    if (cart.length === 0) {
        container.innerHTML = '<p style="text-align: center; color: #64748b; padding: 2rem;">Your cart is empty</p>';
        return;
    }

    const html = cart.map(item => {
        let imageHTML;
        if (item.image && item.image !== 'placeholder') {
            imageHTML = `<img src="${item.image}" alt="${item.name}">`;
        } else {
            const initials = item.name.split(' ').map(w => w[0]).join('').slice(0, 2);
            imageHTML = `<div style="width: 60px; height: 60px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; border-radius: 8px;">${initials}</div>`;
        }

        return `
            <div class="cart-item">
                ${imageHTML}
                <div class="cart-item-info">
                    <div class="cart-item-name">${item.name}</div>
                    <div class="cart-item-qty">Qty: ${item.quantity}</div>
                </div>
                <div class="cart-item-price">৳${(item.price * item.quantity).toLocaleString()}</div>
            </div>
        `;
    }).join('');

    container.innerHTML = html;
}

function validateAndGoToPayment() {
    const name = document.getElementById('checkoutName').value.trim();
    const phone = document.getElementById('checkoutPhone').value.trim();
    const email = document.getElementById('checkoutEmail').value.trim();
    const address = document.getElementById('checkoutAddress').value.trim();

    if (!name) {
        showToast('Please enter your name');
        document.getElementById('checkoutName').focus();
        return;
    }

    if (!phone || phone.length < 11) {
        showToast('Please enter a valid phone number');
        document.getElementById('checkoutPhone').focus();
        return;
    }

    if (!email || !email.includes('@')) {
        showToast('Please enter a valid email');
        document.getElementById('checkoutEmail').focus();
        return;
    }

    if (!address) {
        showToast('Please enter your address');
        document.getElementById('checkoutAddress').focus();
        return;
    }

    goToCheckoutStep(3);
}

function selectPaymentMethod(method) {
    selectedPaymentMethod = method;

    // Update UI
    document.querySelectorAll('.payment-option').forEach(opt => {
        opt.classList.remove('selected');
    });

    event.currentTarget.classList.add('selected');

    // Hide all payment UIs
    hideAllPaymentUIs();

    // Show selected payment UI
    const paymentUI = document.getElementById(`${method}-payment-ui`);
    if (paymentUI) {
        paymentUI.style.display = 'block';

        // Update amounts
        const total = window.checkoutSubtotal - (window.checkoutDiscount || 0);
        const totalText = `৳${total.toLocaleString()}`;

        if (method === 'bkash') {
            document.getElementById('bkashAmount').textContent = totalText;
            initPinInputs('.pin-input');
        } else if (method === 'nagad') {
            document.getElementById('nagadAmount').textContent = totalText;
            initPinInputs('.nagad-pin-input');
        } else if (method === 'card') {
            document.getElementById('cardAmount').textContent = totalText;
            initCardInputs();
        } else if (method === 'cod') {
            document.getElementById('codAmount').textContent = totalText;
        }
    }

    // Hide back button when payment UI is shown
    document.getElementById('payment-back-btn').style.display = paymentUI ? 'none' : 'block';
}

function hideAllPaymentUIs() {
    document.querySelectorAll('.payment-ui').forEach(ui => {
        ui.style.display = 'none';
    });
    document.querySelectorAll('.payment-option').forEach(opt => {
        opt.classList.remove('selected');
    });
    document.getElementById('payment-back-btn').style.display = 'block';
    selectedPaymentMethod = null;
}

function initPinInputs(selector) {
    const inputs = document.querySelectorAll(selector);
    inputs.forEach((input, index) => {
        input.value = '';
        input.addEventListener('input', (e) => {
            if (e.target.value.length === 1 && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        });
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && e.target.value === '' && index > 0) {
                inputs[index - 1].focus();
            }
        });
    });
    if (inputs[0]) inputs[0].focus();
}

function initCardInputs() {
    const cardNumber = document.getElementById('cardNumber');
    const cardExpiry = document.getElementById('cardExpiry');

    if (cardNumber) {
        cardNumber.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{4})/g, '$1 ').trim();
            e.target.value = value.substring(0, 19);
        });
    }

    if (cardExpiry) {
        cardExpiry.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            e.target.value = value;
        });
    }
}

async function processPayment(method) {
    const btn = document.getElementById(`${method}PayBtn`);
    const originalText = btn.textContent;

    // DEMO MODE: Minimal validation - any input works
    if (method === 'bkash') {
        const number = document.getElementById('bkashNumber').value.trim();
        if (!number) {
            showToast('Please enter your bKash number');
            return;
        }
        // Any PIN works for demo
    } else if (method === 'nagad') {
        const number = document.getElementById('nagadNumber').value.trim();
        if (!number) {
            showToast('Please enter your Nagad number');
            return;
        }
        // Any PIN works for demo
    } else if (method === 'card') {
        const cardNumber = document.getElementById('cardNumber').value.trim();
        if (!cardNumber) {
            showToast('Please enter a card number');
            return;
        }
        // Any card details work for demo
    }

    // Show processing state
    btn.innerHTML = 'Processing... <span class="spinner"></span>';
    btn.classList.add('processing-btn');
    btn.disabled = true;

    // Simulate payment processing
    await new Promise(resolve => setTimeout(resolve, 2000));

    // Submit order to backend
    try {
        const orderId = document.getElementById('checkoutOrderId').textContent;
        const total = window.checkoutSubtotal - (window.checkoutDiscount || 0);

        const paymentMethodNames = {
            'bkash': 'bKash',
            'nagad': 'Nagad',
            'card': 'Credit/Debit Card',
            'cod': 'Cash on Delivery'
        };

        const orderData = {
            csrf_token: csrfToken,
            customer_name: document.getElementById('checkoutName').value,
            customer_email: document.getElementById('checkoutEmail').value,
            customer_phone: document.getElementById('checkoutPhone').value,
            customer_address: document.getElementById('checkoutAddress').value,
            payment_method: paymentMethodNames[method],
            items: cart.map(item => ({
                id: item.id,
                name: item.name,
                quantity: item.quantity,
                price: item.price
            })),
            subtotal: window.checkoutSubtotal,
            discount: window.checkoutDiscount || 0,
            total: total,
            coupon_code: window.appliedCoupon || null
        };

        const response = await fetch('api/submit_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(orderData)
        });

        const result = await response.json();

        if (result.success) {
            // Clear cart
            cart = [];
            saveCart();
            updateCartUI();

            // Show confirmation
            document.getElementById('confirmOrderId').textContent = result.order_id || orderId;
            document.getElementById('confirmPaymentMethod').textContent = paymentMethodNames[method];
            document.getElementById('confirmTotal').textContent = `৳${total.toLocaleString()}`;

            goToCheckoutStep(4);

            showToast('Order placed successfully!');
        } else {
            throw new Error(result.message || 'Order submission failed');
        }

    } catch (error) {
        console.error('Checkout error:', error);
        showToast('Error: ' + error.message);
        btn.textContent = originalText;
        btn.classList.remove('processing-btn');
        btn.disabled = false;
    }
}

// Update openCheckoutModal to initialize properly
const originalOpenCheckoutModal = openCheckoutModal;
openCheckoutModal = function () {
    if (!checkoutModal) return;

    // Reset to step 1
    currentCheckoutStep = 1;
    selectedPaymentMethod = null;

    // Generate Order ID
    const orderId = 'ORD-' + Math.floor(Math.random() * 1000000).toString().padStart(6, '0');
    document.getElementById('checkoutOrderId').textContent = orderId;

    // Calculate Total
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    window.checkoutSubtotal = subtotal;
    window.checkoutDiscount = 0;
    window.appliedCoupon = null;

    updateCheckoutTotal();

    // Render cart items
    renderCheckoutCartItems();

    // Pre-fill user data
    if (window.currentUser) {
        document.getElementById('checkoutName').value = window.currentUser.name || '';
        document.getElementById('checkoutPhone').value = window.currentUser.phone || '';
        document.getElementById('checkoutEmail').value = window.currentUser.email || '';
        document.getElementById('checkoutAddress').value = window.currentUser.address || '';
    } else {
        document.getElementById('checkoutName').value = userProfile.name || '';
        document.getElementById('checkoutPhone').value = userProfile.phone || '';
        document.getElementById('checkoutEmail').value = userProfile.email || '';
        document.getElementById('checkoutAddress').value = userProfile.address || '';
    }

    // Reset step indicators
    goToCheckoutStep(1);
    hideAllPaymentUIs();

    // Reset coupon
    const couponInput = document.getElementById('couponCode');
    if (couponInput) {
        couponInput.value = '';
        couponInput.disabled = false;
    }
    const applyBtn = document.querySelector('.apply-coupon-btn');
    if (applyBtn) {
        applyBtn.textContent = 'Apply';
    }

    checkoutModal.classList.add('active');
    checkoutModal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';

    // Close cart sidebar if open
    toggleCart(false);
};
