function toggleDropdown(productId) {
        const dropdown = document.getElementById(`dropdown-${productId}`);

        document.querySelectorAll('.dropdown-content').forEach(d => {
            if (d.id !== `dropdown-${productId}`) {
                d.classList.remove('show');
            }
        });

        dropdown.classList.toggle('show');
    }

    function changeQuantity(productId, change) {
        const input = document.getElementById(`quantity-${productId}`);
        let value = parseInt(input.value) + change;
        if (value < 1) value = 1;
        if (value > 99) value = 99;
        input.value = value;
    }

    function addToCart(productId, title, price, image) {
        const quantity = document.getElementById(`quantity-${productId}`).value;

        const params = new URLSearchParams();
        params.append('action', 'add_to_cart');
        params.append('product_id', productId);
        params.append('title', title);
        params.append('price', price);
        params.append('image', image);
        params.append('quantity', quantity);

        fetch('', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: params.toString() 
        })
        .then(response => {
            if (!response.ok) {
              
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json(); 
        })
        .then(data => {
            if (data.success) {
                document.getElementById('cartCount').textContent = data.cart_count;
                showToast(data.message, 'success');
                document.getElementById(`dropdown-${productId}`).classList.remove('show');
                document.getElementById(`quantity-${productId}`).value = 1;
                loadCartItems();
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error adding to cart:', error);
            showToast('An error occurred while adding to cart.', 'error');
        });
    }

    function updateCartQuantity(productId, quantity) {
        const params = new URLSearchParams();
        params.append('action', 'update_quantity');
        params.append('product_id', productId);
        params.append('quantity', quantity);

        fetch('', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: params.toString()
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                document.getElementById('cartCount').textContent = data.cart_count;
                document.getElementById('cartTotal').textContent = parseFloat(data.cart_total).toFixed(2);
                showToast(data.message, 'success'); 
                if (quantity === 0) {
                    loadCartItems();
                } else {
                    loadCartItems(); 
                }
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error updating cart quantity:', error);
            showToast('An error occurred while updating cart quantity.', 'error');
        });
    }

    function removeFromCart(productId) {
        const params = new URLSearchParams();
        params.append('action', 'remove_from_cart');
        params.append('product_id', productId);

        fetch('', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: params.toString()
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                document.getElementById('cartCount').textContent = data.cart_count;
                document.getElementById('cartTotal').textContent = parseFloat(data.cart_total).toFixed(2);
                showToast(data.message, 'success');
                loadCartItems();
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error removing from cart:', error);
            showToast('An error occurred while removing from cart.', 'error');
        });
    }

    function toggleCart() {
        const sidebar = document.getElementById('cartSidebar');
        sidebar.classList.toggle('open');
        if (sidebar.classList.contains('open')) {
            loadCartItems();
        }
    }

    function loadCartItems() {
        if (document.getElementById('cartSidebar').classList.contains('open')) {
            const params = new URLSearchParams();
            params.append('action', 'get_cart_items');

            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: params.toString()
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.text();
            })
            .then(data => {
                document.getElementById('cartItems').innerHTML = data;
            })
            .catch(error => {
                console.error('Error loading cart items:', error);
                showToast('Error loading cart items.', 'error');
            });
        }
    }

    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        toast.textContent = message;
        toast.className = `toast show ${type}`;

        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    }

    document.addEventListener('click', function(event) {
        if (!event.target.closest('.cart-dropdown')) {
            document.querySelectorAll('.dropdown-content').forEach(d => {
                d.classList.remove('show');
            });
        }
    });

    document.addEventListener('click', function(event) {
        const sidebar = document.getElementById('cartSidebar');
        const cartIcon = event.target.closest('.cart-icon');

        if (sidebar.classList.contains('open') && !sidebar.contains(event.target) && !cartIcon) {
            sidebar.classList.remove('open');
        }
    });