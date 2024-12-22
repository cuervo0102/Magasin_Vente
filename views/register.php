<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . "/../config/connection.php");
require_once(__DIR__ . "/../models/user.php");
require_once(__DIR__ . "/../controllers/UserController.php");

$message = '';
$messageType = '';

try {
    $db = new Dba\Database();
    
    $userController = new App\Controllers\UserController($db->conn);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $result = json_decode($userController->handleRegistration(), true);
        
        if ($result['success']) {
            header('Location: login.php');
            exit;
        } else {
            $message = $result['message'];
            $messageType = 'error';
        }
    }
} catch (Exception $e) {
    $message = $e->getMessage();
    $messageType = 'error';
}

include 'login.php';
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Authentication</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f5f5f5;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 400px;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: #4CAF50;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background: #45a049;
        }

        .toggle-link {
            text-align: center;
            margin-top: 20px;
        }

        .toggle-link a {
            color: #4CAF50;
            text-decoration: none;
        }

        .toggle-link a:hover {
            text-decoration: underline;
        }

        .error-text {
            color: #f44336;
            font-size: 14px;
            margin-top: 5px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (basename($_SERVER['PHP_SELF']) === 'login.php'): ?>
            <h2 style="margin-bottom: 20px; text-align: center;">Login</h2>
            <form id="loginForm" method="POST" action="product_create.php">
                <div class="form-group">
                    <label for="loginEmail">Email</label>
                    <input type="email" id="loginEmail" name="email" required>
                    <div class="error-text" id="loginEmailError"></div>
                </div>
                <div class="form-group">
                    <label for="loginPassword">Password</label>
                    <input type="password" id="loginPassword" name="password" required>
                    <div class="error-text" id="loginPasswordError"></div>
                </div>
                <button type="submit">Login</button>
                <div class="toggle-link">
                    <a href="register.php">Need an account? Register</a>
                </div>
            </form>
        <?php else: ?>
            <h2 style="margin-bottom: 20px; text-align: center;">Register</h2>
            <form id="registerForm" method="POST" action="register.php">
                <div class="form-group">
                    <label for="registerUsername">Username</label>
                    <input type="text" id="registerUsername" name="username" required>
                    <div class="error-text" id="registerUsernameError"></div>
                </div>
                <div class="form-group">
                    <label for="registerEmail">Email</label>
                    <input type="email" id="registerEmail" name="email" required>
                    <div class="error-text" id="registerEmailError"></div>
                </div>
                <div class="form-group">
                    <label for="registerPassword">Password</label>
                    <input type="password" id="registerPassword" name="password" required>
                    <div class="error-text" id="registerPasswordError"></div>
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <input type="password" id="confirmPassword" name="confirm_password" required>
                    <div class="error-text" id="confirmPasswordError"></div>
                </div>
                <button type="submit">Register</button>
                <div class="toggle-link">
                    <a href="login.php">Already have an account? Login</a>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script>
        document.getElementById('loginForm')?.addEventListener('submit', function(e) {
            let valid = true;
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;
            
            document.querySelectorAll('.error-text').forEach(error => error.style.display = 'none');

            if (!email) {
                document.getElementById('loginEmailError').textContent = 'Email is required';
                document.getElementById('loginEmailError').style.display = 'block';
                valid = false;
            }

            if (!password) {
                document.getElementById('loginPasswordError').textContent = 'Password is required';
                document.getElementById('loginPasswordError').style.display = 'block';
                valid = false;
            }

            if (!valid) {
                e.preventDefault();
            }
        });

        document.getElementById('registerForm')?.addEventListener('submit', function(e) {
            let valid = true;
            const username = document.getElementById('registerUsername').value;
            const email = document.getElementById('registerEmail').value;
            const password = document.getElementById('registerPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            document.querySelectorAll('.error-text').forEach(error => error.style.display = 'none');

            if (!username || username.length < 3) {
                document.getElementById('registerUsernameError').textContent = 'Username must be at least 3 characters';
                document.getElementById('registerUsernameError').style.display = 'block';
                valid = false;
            }

            if (!email) {
                document.getElementById('registerEmailError').textContent = 'Valid email is required';
                document.getElementById('registerEmailError').style.display = 'block';
                valid = false;
            }

            if (!password || password.length < 6) {
                document.getElementById('registerPasswordError').textContent = 'Password must be at least 6 characters';
                document.getElementById('registerPasswordError').style.display = 'block';
                valid = false;
            }

            if (password !== confirmPassword) {
                document.getElementById('confirmPasswordError').textContent = 'Passwords do not match';
                document.getElementById('confirmPasswordError').style.display = 'block';
                valid = false;
            }

            if (!valid) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>