<?php include('./includes/head.php'); ?>

<!-- Cart Management Class -->
<?php
class CartManager {
    private $cartItems = [];
    
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
    }
    
    public function addToCart($productId, $title, $price, $image, $quantity = 1) {
        if (isset($this->cartItems[$productId])) {
            $this->cartItems[$productId]['quantity'] += $quantity;
        } else {
            $this->cartItems[$productId] = [
                'id' => $productId,
                'title' => $title,
                'price' => $price,
                'image' => $image,
                'quantity' => $quantity
            ];
        }
        $this->saveCart();
        return true;
    }
    
    public function updateQuantity($productId, $quantity) {
        if (isset($this->cartItems[$productId])) {
            if ($quantity <= 0) {
                unset($this->cartItems[$productId]);
            } else {
                $this->cartItems[$productId]['quantity'] = $quantity;
            }
            $this->saveCart();
            return true;
        }
        return false;
    }
    
    public function removeFromCart($productId) {
        if (isset($this->cartItems[$productId])) {
            unset($this->cartItems[$productId]);
            $this->saveCart();
            return true;
        }
        return false;
    }
    
    public function getCartItems() {
        return $this->cartItems;
    }
    
    public function getCartCount() {
        return array_sum(array_column($this->cartItems, 'quantity'));
    }
    
    public function getCartTotal() {
        $total = 0;
        foreach ($this->cartItems as $item) {
            $total += floatval(str_replace(['$', ','], '', $item['price'])) * $item['quantity'];
        }
        return $total;
    }
    
    private function saveCart() {
        $_SESSION['cart'] = $this->cartItems;
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $cart = new CartManager();
    $response = ['success' => false, 'message' => ''];
    
    switch ($_POST['action']) {
        case 'add_to_cart':
            if (isset($_POST['product_id'], $_POST['title'], $_POST['price'], $_POST['image'])) {
                $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
                $cart->addToCart($_POST['product_id'], $_POST['title'], $_POST['price'], $_POST['image'], $quantity);
                $response = [
                    'success' => true, 
                    'message' => 'Product added to cart!',
                    'cart_count' => $cart->getCartCount()
                ];
            }
            break;
            
        case 'update_quantity':
            if (isset($_POST['product_id'], $_POST['quantity'])) {
                $cart->updateQuantity($_POST['product_id'], intval($_POST['quantity']));
                $response = [
                    'success' => true, 
                    'message' => 'Cart updated!',
                    'cart_count' => $cart->getCartCount(),
                    'cart_total' => $cart->getCartTotal()
                ];
            }
            break;
            
        case 'remove_from_cart':
            if (isset($_POST['product_id'])) {
                $cart->removeFromCart($_POST['product_id']);
                $response = [
                    'success' => true, 
                    'message' => 'Product removed from cart!',
                    'cart_count' => $cart->getCartCount(),
                    'cart_total' => $cart->getCartTotal()
                ];
            }
            break;
            
        case 'get_cart_items':
            $cartItems = $cart->getCartItems();
            if (empty($cartItems)) {
                echo '<div style="text-align: center; padding: 2rem; color: #666;">
                        <i class="fas fa-shopping-cart" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                        <p>Your cart is empty</p>
                      </div>';
            } else {
                foreach ($cartItems as $item) {
                    echo '<div class="cart-item">
                            <img src="' . htmlspecialchars($item['image']) . '" alt="Product Image">
                            <div class="cart-item-details">
                                <div class="cart-item-title">' . htmlspecialchars($item['title']) . '</div>
                                <div style="color: #007bff; font-weight: bold;">' . htmlspecialchars($item['price']) . '</div>
                                <div class="cart-item-controls">
                                    <button class="quantity-btn" onclick="updateCartQuantity(' . $item['id'] . ', ' . ($item['quantity'] - 1) . ')">-</button>
                                    <span style="margin: 0 0.5rem; font-weight: bold;">' . $item['quantity'] . '</span>
                                    <button class="quantity-btn" onclick="updateCartQuantity(' . $item['id'] . ', ' . ($item['quantity'] + 1) . ')">+</button>
                                    <button onclick="removeFromCart(' . $item['id'] . ')" style="background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 4px; margin-left: 1rem; cursor: pointer;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                          </div>';
                }
                echo '<script>
                        document.getElementById("cartTotal").textContent = "' . number_format($cart->getCartTotal(), 2) . '";
                      </script>';
            }
            exit;
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

$cart = new CartManager();
?>

    

<nav class="dash-nav navbar">
    <img src="./assets/images/logo.png" class="logo" alt="Logo">
    <div class="nav-right">
        <i class="fas fa-bell"></i>
        <div class="cart-icon" onclick="toggleCart()">
            <i class="fas fa-shopping-cart"></i>
            <span class="cart-count" id="cartCount"><?= $cart->getCartCount() ?></span>
        </div>
        <i class="fas fa-user-circle"></i>
        <i class="fas fa-bars"></i>
    </div>
</nav>

<section class="dash-container">
    <div class="container">
        <?php
        if (file_exists('./data/products.json') && is_readable('./data/products.json')) {
            $jsonData = file_get_contents('./data/products.json');

            if ($jsonData !== false) {
                $products = json_decode($jsonData, true);

                if ($products !== null && is_array($products) && count($products) > 0) {
                    foreach ($products as $index => $product) {
                        $productId = $product['id'] ?? $index;
                        ?>
                        <div class="card">
                            <img src="<?= htmlspecialchars($product['image']) ?>" alt="">
                            <div class="text-container relative">
                                <h4 class="title"><?= htmlspecialchars($product['title']) ?></h4>
                                <p class="desc"><?= htmlspecialchars($product['desc']) ?></p>
                                <p style="text-align: end; font-weight: bold; color: #007bff;"><?= htmlspecialchars($product['price']) ?></p>
                            </div>
                            <div class="add-to-cart">
                                <div class="cart-dropdown">
                                    <button class="cart-btn" onclick="toggleDropdown(<?= $productId ?>)">
                                        <i class="fas fa-shopping-cart"></i>
                                        Add to Cart
                                    </button>
                                    <div class="dropdown-content" id="dropdown-<?= $productId ?>">
                                        <div class="quantity-controls">
                                            <button class="quantity-btn" onclick="changeQuantity(<?= $productId ?>, -1)">-</button>
                                            <input type="number" class="quantity-input" id="quantity-<?= $productId ?>" value="1" min="1" max="99">
                                            <button class="quantity-btn" onclick="changeQuantity(<?= $productId ?>, 1)">+</button>
                                        </div>
                                        <div style="padding: 0 1rem 1rem;">
                                            <button class="cart-btn" onclick="addToCart(<?= $productId ?>, '<?= htmlspecialchars($product['title']) ?>', '<?= htmlspecialchars($product['price']) ?>', '<?= htmlspecialchars($product['image']) ?>')">
                                                Add to Cart
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <div class="no-products">
                        <div class="no-products-content">
                            <i class="fas fa-box-open no-products-icon"></i>
                            <h3>No Products Available</h3>
                            <p>There are currently no products to display.</p>
                        </div>
                    </div>
                    <?php
                }
            } else {
                ?>
                <div class="no-products">
                    <div class="no-products-content">
                        <i class="fas fa-exclamation-triangle no-products-icon"></i>
                        <h3>Unable to Load Products</h3>
                        <p>There was an error loading the products data.</p>
                    </div>
                </div>
                <?php
            }
        } else {
            ?>
            <div class="no-products">
                <div class="no-products-content">
                    <i class="fas fa-file-times no-products-icon"></i>
                    <h3>Products Data Not Found</h3>
                    <p>The products data file could not be found.</p>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
</section>

<!-- Cart Sidebar -->
<div class="cart-sidebar" id="cartSidebar">
    <div class="cart-header">
        <h3>Shopping Cart</h3>
        <i class="fas fa-times" onclick="toggleCart()" style="cursor: pointer; font-size: 1.5rem;"></i>
    </div>
    <div class="cart-items" id="cartItems">
        <!-- Cart items will be loaded here -->
    </div>
    <div class="cart-total">
        <h4>Total: $<span id="cartTotal">0.00</span></h4>
        <button class="cart-btn" style="margin-top: 1rem;">Checkout</button>
    </div>
</div>

<!-- Toast Notification -->
<div class="toast" id="toast"></div>

<
<?php include('./includes/footer.php'); ?>