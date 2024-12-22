<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../models/category.php';

class CategoryController {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Dba\Database();
        $this->conn = $this->db->conn;
    }

    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['add_category'])) {
                $this->handleCreate();
            } elseif (isset($_POST['delete_category'])) {
                $this->handleDelete();
            }
        }
    }

    private function handleCreate() {
        $category_name = trim($_POST['category_name'] ?? '');
        
        if (empty($category_name)) {
            $_SESSION['error'] = "Category name is required";
            header('Location: ../views/category_create.php');
            exit();
        }

        $category = new Categories($category_name);
        if ($category->insertCategory($this->conn)) {
            $_SESSION['success'] = Categories::$successMsg;
            header('Location: ../views/category_list.php');
        } else {
            $_SESSION['error'] = Categories::$errorMsg;
            header('Location: ../views/category_create.php');
        }
        exit();
    }

    private function handleDelete() {
        $id = filter_var($_POST['category_id'] ?? 0, FILTER_VALIDATE_INT);
        
        if (!$id) {
            $_SESSION['error'] = "Invalid category ID";
            header('Location: ../views/category_list.php');
            exit();
        }

        if (Category::deleteCategory($this->conn, $id)) {
            $_SESSION['success'] = Categories::$successMsg;
        } else {
            $_SESSION['error'] = Categories::$errorMsg;
        }
        
        header('Location: ../views/category_list.php');
        exit();
    }

    public function getCategories() {
        return Categories::getCategoryAll($this->conn);
    }
}

// Initialize controller
if (!isset($categoryController)) {
    session_start();
    $categoryController = new CategoryController();
    $categoryController->handleRequest();
}
?>