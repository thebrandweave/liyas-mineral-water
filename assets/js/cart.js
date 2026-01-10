document.addEventListener('DOMContentLoaded', () => {

    const cartSidebar = document.getElementById('cart-sidebar');
    const closeCartBtn = document.getElementById('close-cart-btn');
    const cartIcon = document.getElementById('cart-icon');
    const addButtons = document.querySelectorAll('.add-btn');
    const cartBody = document.querySelector('.cart-body');
    const subtotalPrice = document.getElementById('subtotal-price');

    let cart = [];

    // --- EVENT LISTENERS ---

    // Open cart
    if (cartIcon) {
        cartIcon.addEventListener('click', () => {
            cartSidebar.classList.add('open');
        });
    }

    // Close cart
    if (closeCartBtn) {
        closeCartBtn.addEventListener('click', () => {
            cartSidebar.classList.remove('open');
        });
    }

    // Add to cart
    addButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            const card = e.target.closest('.product-card');
            const product = {
                id: card.dataset.name, // Using name as a simple ID
                name: card.querySelector('h4').textContent,
                price: parseFloat(card.querySelector('.new-price').textContent.replace('₹', '')),
                image: card.querySelector('img').src,
                quantity: 1
            };
            addToCart(product);
            cartSidebar.classList.add('open');
        });
    });

    // --- FUNCTIONS ---

    function addToCart(product) {
        const existingItem = cart.find(item => item.id === product.id);
        if (existingItem) {
            existingItem.quantity++;
        } else {
            cart.push(product);
        }
        renderCart();
    }

    function renderCart() {
        cartBody.innerHTML = ''; // Clear previous items

        if (cart.length === 0) {
            cartBody.innerHTML = '<p class="cart-empty-message">Your cart is empty.</p>';
        } else {
            cart.forEach(item => {
                const cartItem = document.createElement('div');
                cartItem.className = 'cart-item';
                cartItem.innerHTML = `
                    <img src="${item.image}" alt="${item.name}" class="cart-item-img">
                    <div class="cart-item-details">
                        <h5 class="cart-item-title">${item.name}</h5>
                        <p class="cart-item-price">₹${item.price.toFixed(2)}</p>
                        <div class="cart-item-quantity">
                            <button class="quantity-btn minus-btn" data-id="${item.id}">-</button>
                            <span class="item-quantity">${item.quantity}</span>
                            <button class="quantity-btn plus-btn" data-id="${item.id}">+</button>
                        </div>
                    </div>
                `;
                cartBody.appendChild(cartItem);
            });
        }
        updateSubtotal();
        addQuantityEventListeners();
    }

    function updateSubtotal() {
        const subtotal = cart.reduce((total, item) => total + (item.price * item.quantity), 0);
        subtotalPrice.textContent = `₹${subtotal.toFixed(2)}`;
    }
    
    function addQuantityEventListeners() {
        document.querySelectorAll('.minus-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                const id = e.target.dataset.id;
                updateQuantity(id, -1);
            });
        });

        document.querySelectorAll('.plus-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                const id = e.target.dataset.id;
                updateQuantity(id, 1);
            });
        });
    }

    function updateQuantity(id, change) {
        const item = cart.find(item => item.id === id);
        if (item) {
            item.quantity += change;
            if (item.quantity <= 0) {
                // Remove item if quantity is 0 or less
                cart = cart.filter(cartItem => cartItem.id !== id);
            }
            renderCart();
        }
    }

    // Initial render
    renderCart();
});
