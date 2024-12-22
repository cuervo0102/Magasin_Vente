<?php

class Categories {
    private $category_name;
    private $db;
    public static $errorMsg = "";
    public static $successMsg = "";

    public function __construct($category_name = null, $db = null) {
        $this->category_name = $category_name;
        $this->db = $db ?: new Dba\Database();
    }

    public function insertCategory($conn) {
        if (empty($this->category_name)) {
            self::$errorMsg = "Category name cannot be empty.";
            return false;
        }

        try {
            $conn->beginTransaction();

            $query = "INSERT INTO category(category_name) VALUES(:category_name)";
            $stmt = $conn->prepare($query);
            $result = $stmt->execute([':category_name' => $this->category_name]);

            if ($result) {
                $conn->commit();
                self::$successMsg = "Category inserted successfully!";
                return true;
            } else {
                $conn->rollBack();
                self::$errorMsg = "Failed to insert category.";
                return false;
            }
        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            self::$errorMsg = "Error inserting category: " . $e->getMessage();
            error_log("SQL Error: " . $e->getMessage(), 3, __DIR__ . '/debug.log');
            return false;
        }
    }

    public static function getCategoryAll($conn) {
        try {
            $query = "SELECT * FROM category ORDER BY created_at DESC";
            $stmt = $conn->query($query);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching categories: " . $e->getMessage(), 3, __DIR__ . '/debug.log');
            return [];
        }
    }

    public static function deleteCategory($conn, $id) {
        try {
            $conn->beginTransaction();

            $query = "SELECT category_name FROM category WHERE id = :id";
            $stmt = $conn->prepare($query);
            $stmt->execute([':id' => $id]);
            $category = $stmt->fetch(PDO::FETCH_ASSOC);

            $query = "DELETE FROM category WHERE id = :id";
            $stmt = $conn->prepare($query);
            $result = $stmt->execute([':id' => $id]);

            if ($result) {
                if ($category && isset($category['category_name'])) {
                    $imagePath = __DIR__ . '/../images/' . $category['category_name'];
                    if (is_file($imagePath)) {
                        unlink($imagePath);
                    }
                }

                $conn->commit();
                self::$successMsg = "Category deleted successfully!";
                return true;
            } else {
                $conn->rollBack();
                self::$errorMsg = "Failed to delete category.";
                return false;
            }
        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            self::$errorMsg = "Error deleting category: " . $e->getMessage();
            return false;
        }
    }
}
?>
