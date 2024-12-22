<?php
class Product {
    private $product_image;
    private $product_name;
    private $product_description;
    private $category_id;
    private $db;
    
    public static $errorMsg = "";
    public static $successMsg = "";

    public function __construct($product_image = null, $product_name = null, $product_description = null, $category_id = null) {
        $this->product_image = $product_image;
        $this->product_name = $product_name;
        $this->product_description = $product_description;
        $this->category_id = $category_id;
        $this->db = new Dba\Database();
    }

    public function validate() {
        if (empty($this->product_name)) {
            self::$errorMsg = "Product name is required";
            return false;
        }
        
        if (empty($this->product_description)) {
            self::$errorMsg = "Product description is required";
            return false;
        }
        
        if (empty($this->category_id)) {
            self::$errorMsg = "Category is required";
            return false;
        }
        
        return true;
    }

    public function insertProduct($conn) {
        if (!$this->validate()) {
            return false;
        }

        try {
            $conn->beginTransaction();
            
            $query = "INSERT INTO product (product_image, product_name, product_description, category_id, created_at) 
                     VALUES (:product_image, :product_name, :product_description, :category_id, CURRENT_TIMESTAMP)";
            
            $stmt = $conn->prepare($query);
            $result = $stmt->execute([
                ':product_image' => $this->product_image,
                ':product_name' => $this->product_name,
                ':product_description' => $this->product_description,
                ':category_id' => $this->category_id
            ]);
            
            if ($result) {
                $conn->commit();
                self::$successMsg = "Product inserted successfully!";
                return true;
            }
            
            $conn->rollBack();
            self::$errorMsg = "Failed to insert product";
            return false;
            
        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            self::$errorMsg = "Error inserting product: " . $e->getMessage();
            error_log("SQL Error: " . $e->getMessage(), 3, __DIR__ . '/debug.log');
            return false;
        }
    }

    public static function getAll($conn) {
        try {
            $query = "SELECT p.*, c.category_name 
                     FROM product p 
                     LEFT JOIN category c ON p.category_id = c.id 
                     ORDER BY p.created_at DESC";
            $stmt = $conn->query($query);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching products: " . $e->getMessage(), 3, __DIR__ . '/debug.log');
            return [];
        }
    }

    public static function getById($conn, $id) {
        try {
            $query = "SELECT p.*, c.category_name 
                     FROM product p 
                     LEFT JOIN category c ON p.category_id = c.id 
                     WHERE p.id = :id";
            $stmt = $conn->prepare($query);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching product: " . $e->getMessage(), 3, __DIR__ . '/debug.log');
            return null;
        }
    }

    public function updateProduct($conn, $id) {
        if (!$this->validate()) {
            return false;
        }

        try {
            $conn->beginTransaction();
            
            $query = "UPDATE product SET 
                        product_image = :product_image,
                        product_name = :product_name,
                        product_description = :product_description,
                        category_id = :category_id
                     WHERE id = :id";
            
            $stmt = $conn->prepare($query);
            $result = $stmt->execute([
                ':id' => $id,
                ':product_image' => $this->product_image,
                ':product_name' => $this->product_name,
                ':product_description' => $this->product_description,
                ':category_id' => $this->category_id
            ]);
            
            if ($result) {
                $conn->commit();
                self::$successMsg = "Product updated successfully!";
                return true;
            } else {
                $conn->rollBack();
                self::$errorMsg = "Failed to update product";
                return false;
            }
        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            self::$errorMsg = "Error updating product: " . $e->getMessage();
            error_log("SQL Error: " . $e->getMessage(), 3, __DIR__ . '/debug.log');
            return false;
        }
    }

    
    public static function deleteProduct($conn, $id) {
        try {
            $conn->beginTransaction();

            $query = "SELECT product_image FROM product WHERE id = :id";
            $stmt = $conn->prepare($query);
            $stmt->execute([':id' => $id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Delete the record
            $query = "DELETE FROM product WHERE id = :id";
            $stmt = $conn->prepare($query);
            $result = $stmt->execute([':id' => $id]);
            
            if ($result) {
                if ($product && !empty($product['product_image'])) {
                    $imagePath = __DIR__ . '/../' . $product['product_image'];
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
                
                $conn->commit();
                self::$successMsg = "Product deleted successfully!";
                return true;
            } else {
                $conn->rollBack();
                self::$errorMsg = "Failed to delete product";
                return false;
            }
        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            self::$errorMsg = "Error deleting product: " . $e->getMessage();
            return false;
        }
    }
}
?>