<?php
namespace App\Controllers;

use App\Models\User;
use Exception;
use PDO;

class UserController 
{
    private $userModel;
    private $db;

    public function __construct($db) 
    {
        $this->db = $db;
        $this->userModel = new User($this->db);
    }

    public function handleRegistration() 
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (!$username || !$email || !$password || !$confirmPassword) {
                throw new Exception('All fields are required');
            }

            if ($password !== $confirmPassword) {
                throw new Exception('Passwords do not match');
            }

            $userId = $this->userModel->register($username, $email, $password);

            session_start();
            $_SESSION['user_id'] = $userId;
            $_SESSION['username'] = $username;

            return json_encode([
                'success' => true,
                'message' => 'Registration successful',
                'user_id' => $userId
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            return json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function handleLogin() 
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';

            if (!$email || !$password) {
                throw new Exception('Email and password are required');
            }

            $user = $this->userModel->login($email, $password);

            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            return json_encode([
                'success' => true,
                'message' => 'Login successful',
                'user' => $user
            ]);

        } catch (Exception $e) {
            http_response_code(401);
            return json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    // public function handleUpdateProfile() 
    // {
    //     try {
    //         if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    //             throw new Exception('Invalid request method');
    //         }

    //         // Check if user is logged in
    //         session_start();
    //         if (!isset($_SESSION['user_id'])) {
    //             throw new Exception('User not authenticated');
    //         }

    //         // Get and sanitize input
    //         $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    //         $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    //         if (!$username || !$email) {
    //             throw new Exception('All fields are required');
    //         }

    //         // Update user
    //         $this->userModel->updateUser($_SESSION['user_id'], $username, $email);

    //         // Update session
    //         $_SESSION['username'] = $username;

    //         return json_encode([
    //             'success' => true,
    //             'message' => 'Profile updated successfully'
    //         ]);

    //     } catch (Exception $e) {
    //         http_response_code(400);
    //         return json_encode([
    //             'success' => false,
    //             'message' => $e->getMessage()
    //         ]);
    //     }
    // }

    // public function handleUpdatePassword() 
    // {
    //     try {
    //         if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    //             throw new Exception('Invalid request method');
    //         }

    //         // Check if user is logged in
    //         session_start();
    //         if (!isset($_SESSION['user_id'])) {
    //             throw new Exception('User not authenticated');
    //         }

    //         // Get input
    //         $currentPassword = $_POST['current_password'] ?? '';
    //         $newPassword = $_POST['new_password'] ?? '';
    //         $confirmNewPassword = $_POST['confirm_new_password'] ?? '';

    //         if (!$currentPassword || !$newPassword || !$confirmNewPassword) {
    //             throw new Exception('All fields are required');
    //         }

    //         if ($newPassword !== $confirmNewPassword) {
    //             throw new Exception('New passwords do not match');
    //         }

    //         // Update password
    //         $this->userModel->updatePassword($_SESSION['user_id'], $currentPassword, $newPassword);

    //         return json_encode([
    //             'success' => true,
    //             'message' => 'Password updated successfully'
    //         ]);

    //     } catch (Exception $e) {
    //         http_response_code(400);
    //         return json_encode([
    //             'success' => false,
    //             'message' => $e->getMessage()
    //         ]);
    //     }
    // }


    public function handleDeleteUser() 
{
    try {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Invalid request method');
        }

        session_start();
        if (!isset($_SESSION['user_id'])) {
            throw new Exception('User not authenticated');
        }

        $this->userModel->deleteUser($_SESSION['user_id']);

        session_destroy();

        return json_encode([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);

    } catch (Exception $e) {
        http_response_code(400);
        return json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

    public function handleLogout() 
    {
        session_start();
        session_destroy();
        
        return json_encode([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }
}