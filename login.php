<?php
require_once 'config/db.php';

// Initialize database
Database::setup();

// Check if admin exists, if not create default admin
$db = getDB();
$conn = $db->getConnection();

$result = $conn->query("SELECT id FROM admins LIMIT 1");
if ($result->num_rows == 0) {
    // Create default admin account
    $username = 'admin';
    $email = 'admin@cooperative.com';
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $password);
    $stmt->execute();
    $stmt->close();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_type = $_POST['login_type'] ?? 'admin';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        if ($login_type === 'admin') {
            // Admin login
            $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ? OR email = ?");
            $stmt->bind_param("ss", $username, $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $admin = $result->fetch_assoc();
                
                if (password_verify($password, $admin['password'])) {
                    // Password correct - create session
                    $_SESSION['user_id'] = $admin['id'];
                    $_SESSION['username'] = $admin['username'];
                    $_SESSION['user_type'] = 'admin';
                    $_SESSION['logged_in'] = true;
                    $_SESSION['login_time'] = time();
                    
                    // Regenerate session ID for security
                    session_regenerate_id(true);
                    
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $error = 'Invalid password';
                }
            } else {
                $error = 'Invalid username or email';
            }
            $stmt->close();
        } else {
            // Client login
            $stmt = $conn->prepare("SELECT id, name, password FROM clients WHERE name = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $client = $result->fetch_assoc();
                
                // Check if password is set
                if (empty($client['password'])) {
                    $error = 'Account not set up for login. Contact administrator.';
                } elseif (password_verify($password, $client['password'])) {
                    // Password correct - create session
                    $_SESSION['user_id'] = $client['id'];
                    $_SESSION['username'] = $client['name'];
                    $_SESSION['user_type'] = 'client';
                    $_SESSION['logged_in'] = true;
                    $_SESSION['login_time'] = time();
                    
                    // Regenerate session ID for security
                    session_regenerate_id(true);
                    
                    header('Location: client_dashboard.php');
                    exit;
                } else {
                    $error = 'Invalid password';
                }
            } else {
                $error = 'Invalid client name';
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - UMUHUZA Cooperative</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            padding: 40px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .login-header p {
            color: #666;
            font-size: 14px;
        }

        .login-type {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .login-type label {
            flex: 1;
            padding: 10px;
            text-align: center;
            border: 2px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .login-type input[type="radio"] {
            display: none;
        }

        .login-type input[type="radio"]:checked + label {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
        }

        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .default-credentials {
            background: #f0f0f0;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 13px;
            color: #666;
        }

        .default-credentials strong {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>UMUHUZA Cooperative</h1>
            <p>Member Management System</p>
        </div>

        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="login-type">
                <input type="radio" id="admin" name="login_type" value="admin" checked>
                <label for="admin">Admin Login</label>
                
                <input type="radio" id="client" name="login_type" value="client">
                <label for="client">Client Login</label>
            </div>

            <div class="form-group">
                <label for="username" id="usernameLabel">Username or Email</label>
                <input type="text" id="username" name="username" required autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>

            <button type="submit" class="btn-login">Login</button>
        </form>

        <div class="default-credentials">
            <strong>Admin Default Login:</strong><br>
            Username: admin<br>
            Password: admin123<br><br>
            <strong>Note:</strong> Clients need to be set up by admin with a password to login.
        </div>
    </div>

    <script>
        const adminRadio = document.getElementById('admin');
        const clientRadio = document.getElementById('client');
        const usernameLabel = document.getElementById('usernameLabel');
        const usernameInput = document.getElementById('username');

        adminRadio.addEventListener('change', function() {
            usernameLabel.textContent = 'Username or Email';
            usernameInput.placeholder = '';
        });

        clientRadio.addEventListener('change', function() {
            usernameLabel.textContent = 'Client Name';
            usernameInput.placeholder = 'Enter your company/client name';
        });
    </script>
</body>
</html>
