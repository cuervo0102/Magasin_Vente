<?php


error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../models/product.php';
require_once __DIR__. '/../models/category.php';


$connectiondb = new Dba\Database();
$conn = $connectiondb->conn;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
        $product_name = trim($_POST['product_name'] ?? '');
        $product_description = trim($_POST['product_description'] ?? '');
        $category_id = filter_var($_POST['category_id'] ?? 0, FILTER_VALIDATE_INT);

        if (!$id || empty($product_name) || empty($product_description) || !$category_id) {
            $_SESSION['error'] = "All fields are required and must be valid";
            header("Location: ../views/product_update.php?id=" . $id);
            exit();
        }

        $existing_product = Product::getById($conn, $id);
        if (!$existing_product) {
            $_SESSION['error'] = "Product not found";
            header("Location: ../views/product_update.php?id=" . $id);
            exit();
        }

        $product_image = $existing_product['product_image']; // Use existing image by default
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === 0) {
            $uploadDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "", basename($_FILES['product_image']['name']));
            $targetFile = $uploadDir . $fileName;
            $fileExtension = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            $maxFileSize = 2 * 1024 * 1024;

            if (!in_array($fileExtension, $allowedExtensions)) {
                $_SESSION['error'] = "Invalid file type. Allowed types: " . implode(', ', $allowedExtensions);
            } elseif ($_FILES['product_image']['size'] > $maxFileSize) {
                $_SESSION['error'] = "File size exceeds 2MB limit";
            } elseif (move_uploaded_file($_FILES['product_image']['tmp_name'], $targetFile)) {
                if ($existing_product['product_image'] && file_exists(__DIR__ . '/../' . $existing_product['product_image'])) {
                    unlink(__DIR__ . '/../' . $existing_product['product_image']);
                }
                $product_image = 'uploads/' . $fileName;
            } else {
                $_SESSION['error'] = "Failed to upload file";
            }
        }

        if (!isset($_SESSION['error'])) {
            $product = new Product($product_image, $product_name, $product_description, $category_id);
            if ($product->updateProduct($conn, $id)) {
                $_SESSION['success'] = "Product updated successfully!";
            } else {
                $_SESSION['error'] = Product::$errorMsg ?: "Failed to update product";
            }
        }

        header("Location: ../views/product_update.php?id=" . $id);
        exit();
    }
}




if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit'])) {
        $product_name = trim($_POST['product_name'] ?? '');
        $product_description = trim($_POST['product_description'] ?? '');
        $category_id = filter_var($_POST['category_id'] ?? 0, FILTER_VALIDATE_INT);

        if (empty($product_name) || empty($product_description) || $category_id === false) {
            $errorMessage = "All fields are required and must be valid";
        } else {

            $product_image = null;
            if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === 0) {
                $uploadDir = __DIR__ . '/../uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $fileName = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "", basename($_FILES['product_image']['name']));
                $targetFile = $uploadDir . $fileName;
                $fileExtension = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));


                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                $maxFileSize = 2 * 1024 * 1024; 

                if (!in_array($fileExtension, $allowedExtensions)) {
                    $errorMessage = "Invalid file type. Allowed types: " . implode(', ', $allowedExtensions);
                } elseif ($_FILES['product_image']['size'] > $maxFileSize) {
                    $errorMessage = "File size exceeds 2MB limit";
                } elseif (move_uploaded_file($_FILES['product_image']['tmp_name'], $targetFile)) {
                    $product_image = 'uploads/' . $fileName;
                } else {
                    $errorMessage = "Failed to upload file";
                }
            }

            if (!isset($errorMessage)) {
                $product = new Product($product_image, $product_name, $product_description, $category_id);
                if ($product->insertProduct($conn)) {
                    $successMessage = "Product created successfully!";

                } else {
                    $errorMessage = Product::$errorMsg ?: "Failed to create product";
                }
            }
        }
    }
}

if (isset($_POST['delete'])) {
    $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
    
    if (!$id) {
        $_SESSION['error'] = "Invalid product ID";
    } else {
        try {
            if (Product::deleteProduct($conn, $id)) {
                $_SESSION['success'] = "Product deleted successfully";
            } else {
                $_SESSION['error'] = Product::$errorMsg ?: "Failed to delete product";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Error deleting product: " . $e->getMessage();
        }
    }
    
    header('Location: ../views/product_list.php');
    exit();
}

// $categories = Category::getCategoryAll($conn);
?>