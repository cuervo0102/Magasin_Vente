<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once dirname(__DIR__) . '/controllers/Product_Controller.php';
require_once dirname(__DIR__) . '/models/product.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = filter_var($_POST['delete_id'], FILTER_VALIDATE_INT);
    
    if (!$id) {
        header('Location: product_list.php?error=Invalid product ID');
        exit();
    }

    if (Product::deleteProduct($connectiondb->conn, $id)) {
        header('Location: product_list.php?success=Product deleted successfully');
    } else {
        header('Location: product_list.php?error=' . urlencode(Product::$errorMsg ?: 'Failed to delete product'));
    }
    exit();
}

$products = Product::getAll($connectiondb->conn);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple E-Commerce</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .product-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 30px;
        }

        .product-img {
            width: 100%;
            height: auto;
            border-radius: 10px;
        }

        .navbar-nav .nav-link {
            font-size: 18px;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">E-Commerce</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-heart"></i> Wishlist
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-shopping-cart"></i> Cart (<span id="cart-counter">0</span>)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <h2 class="text-center mb-4">Featured Products</h2>

        <div class="row">
        <?php foreach ($products as $product): ?>
            <div class="col-md-4">
                <div class="product-card">
                <?php if (!empty($product['product_image'])): ?>
                                <img src="../<?php echo htmlspecialchars($product['product_image']); ?>" 
                                     alt="Product Image" style="width: 100px;">
                            <?php endif; ?>
                    <h5 class="mt-3"><?php echo htmlspecialchars($product['product_name']); ?></h5>
                    <p><?php echo htmlspecialchars($product['product_description']); ?></p>
                    <p class="text-success">$99.99</p>
                    <button class="btn btn-primary add-to-cart" data-product="1">Add to Cart</button>
                    <button class="btn btn-outline-danger add-to-wishlist" data-product="1"><i class="fas fa-heart"></i> Add to Wishlist</button>
                </div>
            </div>
        <?php endforeach; ?>



    <footer class="bg-light text-center py-4">
        <p>&copy; 2024 E-Commerce Site. All Rights Reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
    
    <script>
        let cartCount = 0;
        let wishlistCount = 0;


        const addToCartButtons = document.querySelectorAll('.add-to-cart');
        addToCartButtons.forEach(button => {
            button.addEventListener('click', () => {
                cartCount++;
                document.getElementById('cart-counter').innerText = cartCount;
                alert('Product added to cart');
            });
        });


        const addToWishlistButtons = document.querySelectorAll('.add-to-wishlist');
        addToWishlistButtons.forEach(button => {
            button.addEventListener('click', () => {
                wishlistCount++;
                alert('Product added to wishlist');
            });
        });
    </script>
</body>

</html>
