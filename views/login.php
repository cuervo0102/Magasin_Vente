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
        $result = json_decode($userController->handleLogin(), true);
        
        if ($result['success']) {
            header('Location: index.php');
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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

        <h2 style="margin-bottom: 20px; text-align: center;">Login</h2>
        <form id="loginForm" method="POST" action="index.php">
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
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            let valid = true;
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;
            
            // Reset errors
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
    </script>
</body>
</html>