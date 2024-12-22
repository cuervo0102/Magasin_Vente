<?php
namespace App\Models;

use PDO;
use Exception;

class User {
    private $db;
    private $username;
    private $email;
    private $password;

    public function __construct($db) {
        $this->db = $db;
    }

    public function register($username, $email, $password) {
        try {
            $this->setUsername($username);
            $this->setEmail($email);
            $this->setPassword($password);

            $this->db->beginTransaction();

            $query = "SELECT * FROM p_user WHERE email = :email OR username = :username";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':email' => $this->email, ':username' => $this->username]);

            if ($stmt->rowCount() > 0) {
                throw new Exception("Username or email already exists.");
            }

            $query = "INSERT INTO p_user (username, email, password) VALUES (:username, :email, :password) RETURNING id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':username' => $this->username,
                ':email' => $this->email,
                ':password' => $this->password
            ]);

            $userId = $stmt->fetchColumn();
            $this->db->commit();
            
            return $userId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Registration failed: " . $e->getMessage());
        }
    }

    public function login($email, $password) {
        try {
            $query = "SELECT * FROM p_user WHERE email = :email";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':email' => $email]);
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || !password_verify($password, $user['password'])) {
                throw new Exception("Invalid email or password");
            }
            
            return $user;
        } catch (Exception $e) {
            throw new Exception("Login failed: " . $e->getMessage());
        }
    }

    public function deleteUser($userId) {
        try {
            $this->db->beginTransaction();
            
            $query = "DELETE FROM p_user WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id' => $userId]);
            
            if ($stmt->rowCount() === 0) {
                throw new Exception("User not found");
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Delete failed: " . $e->getMessage());
        }
    }

    private function setUsername($username) {
        if (strlen($username) < 3) {
            throw new Exception("Username must be at least 3 characters long.");
        }
        $this->username = htmlspecialchars(strip_tags($username));
    }

    private function setEmail($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }
        $this->email = htmlspecialchars(strip_tags($email));
    }

    private function setPassword($password) {
        if (strlen($password) < 6) {
            throw new Exception("Password must be at least 6 characters long.");
        }
        $this->password = password_hash($password, PASSWORD_BCRYPT);
    }
}