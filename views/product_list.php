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
    <title>Product List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Product List</h2>

        <?php if (!empty($_GET['success'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($_GET['error'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo $product['id']; ?></td>
                        <td>
                            <?php if (!empty($product['product_image'])): ?>
                                <img src="../<?php echo htmlspecialchars($product['product_image']); ?>" 
                                     alt="Product Image" style="width: 100px;">
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($product['product_description']); ?></td>
                        <td>
                            <a href="product_update.php?id=<?php echo $product['id']; ?>" 
                               class="btn btn-warning btn-sm">Edit</a>
                            <form action="" method="POST" style="display:inline;" 
                                  onsubmit="return confirm('Are you sure you want to delete this product?');">
                                <input type="hidden" name="delete_id" value="<?php echo $product['id']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>