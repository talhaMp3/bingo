<?php
session_start();
require_once '../include/connection.php'; // contains $conn (mysqli connection)

// If already logged in
// if (isset($_SESSION['admin_id'])) {
//     header("Location: dashboard.php");
//     exit;
// }

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Fetch admin from DB
    $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    if ($admin && $password === $admin['password']) {
        // store admin id temporarily for 2FA
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['pending_admin_id'] = $admin['id'];
        header("Location: verify_2fa.php");
        exit;
    } else {
        $error = "Invalid login details.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Bingo</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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
            padding: 20px;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo i {
            font-size: 48px;
            color: #667eea;
            margin-bottom: 10px;
        }

        .logo h1 {
            color: #2d3748;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .logo p {
            color: #718096;
            font-size: 14px;
        }

        .error-message {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            border: 1px solid #fca5a5;
            color: #991b1b;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-5px);
            }

            75% {
                transform: translateX(5px);
            }
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2d3748;
            font-weight: 600;
            font-size: 14px;
        }

        .input-container {
            position: relative;
        }

        .input-container i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            transition: all 0.3s ease;
        }

        .form-input {
            width: 100%;
            padding: 16px 16px 16px 48px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 16px;
            background: #f7fafc;
            transition: all 0.3s ease;
            outline: none;
        }

        .form-input:focus {
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-input:focus+.input-icon {
            color: #667eea;
        }

        .login-btn {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 16px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .login-btn:hover::before {
            left: 100%;
        }

        .footer-text {
            text-align: center;
            margin-top: 30px;
            color: #718096;
            font-size: 14px;
        }

        .security-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 20px;
            color: #4a5568;
            font-size: 12px;
        }

        .security-badge i {
            color: #48bb78;
        }

        /* Responsive design */
        @media (max-width: 480px) {
            .login-container {
                padding: 30px 25px;
                margin: 10px;
            }

            .logo h1 {
                font-size: 24px;
            }
        }

        /* Loading animation */
        .loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid transparent;
            border-top: 2px solid #ffffff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Floating particles background effect */
        .bg-particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .particle:nth-child(1) {
            width: 80px;
            height: 80px;
            left: 10%;
            animation-delay: 0s;
        }

        .particle:nth-child(2) {
            width: 60px;
            height: 60px;
            left: 80%;
            animation-delay: 2s;
        }

        .particle:nth-child(3) {
            width: 40px;
            height: 40px;
            left: 60%;
            animation-delay: 4s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px) rotate(0deg);
                opacity: 0.7;
            }

            50% {
                transform: translateY(-20px) rotate(180deg);
                opacity: 0.3;
            }
        }
    </style>
</head>

<body>
    <div class="bg-particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <div class="login-container">
        <div class="logo">
            <i class="fas fa-chess-board"></i>
            <h1>Bingo Admin</h1>
            <p>Secure Administrative Portal</p>
        </div>

        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="post" id="loginForm">
            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-container">
                    <i class="fas fa-envelope"></i>
                    <input type="email"
                        id="email"
                        name="email"
                        class="form-input"
                        value="admin@admin.com"
                        required
                        autocomplete="email">
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-container">
                    <i class="fas fa-lock"></i>
                    <input type="password"
                        id="password"
                        name="password"
                        class="form-input"
                        value="admin@123"
                        required
                        autocomplete="current-password">
                </div>
            </div>

            <button type="submit" class="login-btn" id="loginBtn">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>

        <div class="security-badge">
            <i class="fas fa-shield-alt"></i>
            <span>Protected by 2FA Authentication</span>
        </div>

        <div class="footer-text">
            Powered by secure authentication system
        </div>
    </div>

    <script>
        // Add loading state on form submit
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            btn.classList.add('loading');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing In...';
        });

        // Add focus animations
        const inputs = document.querySelectorAll('.form-input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.querySelector('i').style.color = '#667eea';
            });

            input.addEventListener('blur', function() {
                if (!this.value) {
                    this.parentElement.querySelector('i').style.color = '#a0aec0';
                }
            });
        });
    </script>
</body>

</html>