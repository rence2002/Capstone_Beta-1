<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] == "") {
    header("location: ../index.php");
    exit;
}

include("../config/database.php");

// Modify the SQL query to properly handle multiple images
$stmt = $pdo->prepare("
    SELECT c.*, p.Product_Name, p.ImageURL, p.Price
    FROM tbl_cart c
    JOIN tbl_prod_info p ON c.Product_ID = p.Product_ID
    WHERE c.User_ID = :user_id
    ORDER BY c.Date_Added DESC
");
$stmt->execute(['user_id' => $_SESSION["user_id"]]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total from cart Total_Price
$total = array_sum(array_column($cartItems, 'Total_Price'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - RM BETIS FURNITURE</title>
    <link rel="stylesheet" href="../static/css-files/Home.css">
    <link rel="stylesheet" href="../static/css-files/Cart.css">
    <!-- Add Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
</head>

<body>
    <header>
        <nav class="navbar">
            <a href="home.php" class="logo">
                <img src="../static/images/rm raw png.png" alt="" class="logo">
            </a>
            <ul class="menu-links">
                <li><a href="home.php">Home</a></li>
                <li><a href="Review.php">Reviews</a></li>
                <li><a href="gallery.php">Gallery</a></li>
                <li><a href="cart.php" class="cart active" id="cart">Cart</a></li>
                <li><a href="profile.php" class="profile" id="sign_in">Profile</a></li>
                <li><a href="logout.php" class="profile" id="sign_in">Logout</a></li>
                <span id="close-menu-btn" class="material-symbols-outlined">close</span>
            </ul>
            <span id="hamburger-btn" class="material-symbols-outlined">menu</span>
        </nav>
    </header>

    <main>
        <div class="container">
            <h1>Shopping Cart</h1>
            
            <?php if (empty($cartItems)): ?>
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <h2>Your cart is empty</h2>
                    <p>Browse our gallery to add some items!</p>
                    <a href="gallery.php" class="browse-btn">Browse Gallery</a>
                </div>
            <?php else: ?>
                <div class="cart_line">
                    <div class="col-md-12 col-lg-10 mx-auto">
                        <?php foreach ($cartItems as $item): 
                            // Get the first image from the comma-separated list
                            $images = explode(',', $item['ImageURL']);
                            $firstImage = isset($images[0]) ? trim($images[0]) : '../static/images/placeholder.jpg';
                        ?>
                            <div class="cart-item" data-id="<?php echo htmlspecialchars($item['Cart_ID']); ?>">
                                <div class="row">
                                    <div class="col-md-7 center-item">
                                        <img src="<?php echo htmlspecialchars($firstImage); ?>" 
                                             alt="<?php echo htmlspecialchars($item['Product_Name']); ?>"
                                             class="cart-product-image">
                                        <h5><?php echo htmlspecialchars($item['Product_Name']); ?> 
                                            (₱<?php echo number_format($item['Price'], 2); ?>)</h5>
                                    </div>

                                    <div class="col-md-5 center-item">
                                        <div class="input-group number-spinner">
                                            <button class="btn btn-default qty-btn minus"><i class="fas fa-minus"></i></button>
                                            <input type="number" class="form-control text-center quantity" 
                                                   value="<?php echo $item['Quantity']; ?>" min="1">
                                            <button class="btn btn-default qty-btn plus"><i class="fas fa-plus"></i></button>
                                        </div>
                                        <h5>₱<span class="item-total">
                                            <?php echo number_format($item['Price'] * $item['Quantity'], 2); ?>
                                        </span></h5>
                                        <button class="btn btn-danger remove-btn">
                                            <i class="fas fa-trash-alt"></i> Remove
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <div class="cart-summary mt-4">
                            <div class="summary-line">
                                <h4>Subtotal: <span class="subtotal">₱<?php echo number_format($total, 2); ?></span></h4>
                            </div>
                            <button class="btn btn-primary checkout-btn" onclick="proceedToCheckout()">
                                Proceed to Checkout
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="footer">
        <div class="footer-row">
            <div class="footer-col">
                <h4>Info</h4>
                <ul class="links">
                    <li><a href="home.php">Home</a></li>
                    <li><a href="home.php#about-section">About Us</a></li>
                    <li><a href="Gallery.php">Gallery</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Explore</h4>
                <ul class="links">
                    <li><a href="#">Free Designs</a></li>
                    <li><a href="#">Latest Designs</a></li>
                    <li><a href="#">Themes</a></li>
                    <li><a href="#">Popular Designs</a></li>
                    <li><a href="#">Art Skills</a></li>
                    <li><a href="#">New Uploads</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Legal</h4>
                <ul class="links">
                    <li><a href="#">Customer Agreement</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">GDPR</a></li>
                    <li><a href="#">Security</a></li>
                    <li><a href="#">Testimonials</a></li>
                    <li><a href="#">Media Kit</a></li>
                </ul>
            </div>

            <div class="icons">
                <i class="fa-brands fa-facebook-f"></i>
                <i class="fa-brands fa-twitter"></i>
                <i class="fa-brands fa-linkedin"></i>
                <i class="fa-brands fa-github"></i>
            </div>
        </div>
    </footer>

    <script src="../static/Javascript-files/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Quantity control buttons
            document.querySelectorAll('.qty-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const input = this.parentElement.querySelector('.quantity');
                    const currentValue = parseInt(input.value);
                    
                    if (this.classList.contains('minus')) {
                        if (currentValue > 1) input.value = currentValue - 1;
                    } else {
                        input.value = currentValue + 1;
                    }
                    
                    updateCartItem(this.closest('.cart-item'));
                });
            });

            // Remove buttons
            document.querySelectorAll('.remove-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const cartItem = this.closest('.cart-item');
                    const cartId = cartItem.dataset.id;
                    
                    fetch('remove_from_cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ cart_id: cartId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            cartItem.remove();
                            updateCartTotal();
                        }
                    });
                });
            });

            // Update cart total
            function updateCartTotal() {
                const totals = Array.from(document.querySelectorAll('.item-total'))
                    .map(el => parseFloat(el.textContent.replace('₱', '')))
                    .reduce((a, b) => a + b, 0);
                
                document.querySelector('.subtotal').textContent = '₱' + totals.toFixed(2);
            }

            // Update cart item
            function updateCartItem(cartItem) {
                const cartId = cartItem.dataset.id;
                const quantity = cartItem.querySelector('.quantity').value;
                
                fetch('update_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ 
                        cart_id: cartId,
                        quantity: quantity
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        cartItem.querySelector('.item-total').textContent = 
                            '₱' + parseFloat(data.total_price).toFixed(2);
                        updateCartTotal();
                    }
                });
            }
        });

        function proceedToCheckout() {
            fetch('cart-rec.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Order placed successfully!');
                    window.location.href = 'home.php'; // Changed from orders.php to home.php
                } else {
                    alert(data.message || 'Failed to place order. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add to Cart buttons
            document.querySelectorAll('.add-to-cart-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const productId = this.dataset.productId;
                    const quantity = 1; // Default quantity to add

                    fetch('add_to_cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ product_id: productId, quantity: quantity })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Item added to cart successfully!');
                        } else {
                            alert(data.message || 'Failed to add item to cart. Please try again.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred. Please try again.');
                    });
                });
            });
        });
    </script>

    <!-- Add Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>