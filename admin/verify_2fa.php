<?php
session_start();

// If the admin is not logged in from login step, redirect back
if (!isset($_SESSION['pending_admin_id'])) {
    header("Location: login.php");
    exit();
}

require_once '../include/connection.php'; // contains $conn (mysqli)
require_once __DIR__ . "/../vendor/autoload.php";

use Sonata\GoogleAuthenticator\GoogleAuthenticator;

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code']);

    // Get admin secret from DB
    $stmt = $conn->prepare("SELECT google2fa_secret FROM admins WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['pending_admin_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    if (!$admin || empty($admin['google2fa_secret'])) {
        die("⚠️ 2FA is not configured for this admin.");
    }

    $secret = $admin['google2fa_secret'];

    $g = new GoogleAuthenticator();

    // Verify the code
    if ($g->checkCode($secret, $code)) {
        // ✅ Success → Mark admin fully logged in
        $_SESSION['admin_id'] = $_SESSION['pending_admin_id'];
        unset($_SESSION['pending_admin_id']); // clean temp session
        $_SESSION['is_verified'] = true;

        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid 2FA code. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication - Bingo Admin</title>
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
            position: relative;
        }

        .verify-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 450px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-align: center;
        }

        .security-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .security-icon i {
            font-size: 32px;
            color: white;
        }

        .header-section h1 {
            color: #2d3748;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .header-section p {
            color: #718096;
            font-size: 16px;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        .error-message {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            border: 1px solid #fca5a5;
            color: #991b1b;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
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

        .code-input-container {
            margin: 30px 0;
        }

        .code-input-label {
            display: block;
            margin-bottom: 15px;
            color: #2d3748;
            font-weight: 600;
            font-size: 16px;
        }

        .code-input {
            width: 100%;
            padding: 18px 24px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 24px;
            font-weight: 600;
            text-align: center;
            letter-spacing: 8px;
            background: #f7fafc;
            transition: all 0.3s ease;
            outline: none;
            font-family: 'Courier New', monospace;
        }

        .code-input:focus {
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        .verify-btn {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 18px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin-top: 20px;
        }

        .verify-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .verify-btn:active {
            transform: translateY(0);
        }

        .verify-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .verify-btn:hover::before {
            left: 100%;
        }

        .help-section {
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid #e2e8f0;
        }

        .help-title {
            color: #4a5568;
            font-weight: 600;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .help-steps {
            text-align: left;
            color: #718096;
            font-size: 13px;
            line-height: 1.6;
        }

        .help-steps li {
            margin-bottom: 8px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }

        .help-steps i {
            color: #667eea;
            margin-top: 2px;
            font-size: 12px;
        }

        .timer-container {
            margin-top: 20px;
            padding: 15px;
            background: linear-gradient(135deg, #f0f4f8, #e6f3ff);
            border-radius: 10px;
            border: 1px solid #bee3f8;
        }

        .timer-text {
            color: #2b6cb0;
            font-size: 14px;
            font-weight: 500;
        }

        .timer-countdown {
            font-weight: 700;
            color: #1e40af;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            margin-top: 20px;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            background: rgba(102, 126, 234, 0.1);
            transform: translateX(-3px);
        }

        /* Responsive design */
        @media (max-width: 480px) {
            .verify-container {
                padding: 30px 25px;
                margin: 10px;
            }

            .header-section h1 {
                font-size: 24px;
            }

            .code-input {
                font-size: 20px;
                letter-spacing: 6px;
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

        /* Background particles */
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
            animation: float 8s ease-in-out infinite;
        }

        .particle:nth-child(1) {
            width: 60px;
            height: 60px;
            left: 15%;
            animation-delay: 0s;
        }

        .particle:nth-child(2) {
            width: 80px;
            height: 80px;
            left: 75%;
            animation-delay: 3s;
        }

        .particle:nth-child(3) {
            width: 40px;
            height: 40px;
            left: 45%;
            animation-delay: 6s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px) rotate(0deg);
                opacity: 0.4;
            }

            50% {
                transform: translateY(-30px) rotate(180deg);
                opacity: 0.8;
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

    <div class="verify-container">
        <div class="security-icon">
            <i class="fas fa-shield-alt"></i>
        </div>

        <div class="header-section">
            <h1>Two-Factor Authentication</h1>
            <p>Enter the 6-digit code from your authenticator app to complete the login process</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="verifyForm">
            <div class="code-input-container">
                <label for="code" class="code-input-label">
                    <i class="fas fa-mobile-alt"></i> Authentication Code
                </label>
                <input type="text"
                    id="code"
                    name="code"
                    class="code-input"
                    required
                    maxlength="6"
                    placeholder="000000"
                    autocomplete="one-time-code"
                    inputmode="numeric"
                    pattern="[0-9]{6}">
            </div>

            <button type="submit" class="verify-btn" id="verifyBtn">
                <i class="fas fa-check-circle"></i> Verify & Continue
            </button>
        </form>

        <div class="timer-container">
            <div class="timer-text">
                <i class="fas fa-clock"></i>
                Code expires in: <span class="timer-countdown" id="countdown">30s</span>
            </div>
        </div>

        <div class="help-section">
            <div class="help-title">
                <i class="fas fa-question-circle"></i> Need Help?
            </div>
            <ol class="help-steps">
                <li><i class="fas fa-mobile-alt"></i>Open your Google Authenticator app</li>
                <li><i class="fas fa-search"></i>Find "Bingo Admin" in your app</li>
                <li><i class="fas fa-keyboard"></i>Enter the 6-digit code above</li>
                <li><i class="fas fa-sync-alt"></i>Codes refresh every 30 seconds</li>
            </ol>
        </div>

        <a href="login.php" class="back-link">
            <i class="fas fa-arrow-left"></i>
            Back to Login
        </a>
    </div>

    <script>
        // Add loading state on form submit
        document.getElementById('verifyForm').addEventListener('submit', function() {
            const btn = document.getElementById('verifyBtn');
            btn.classList.add('loading');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';
        });

        // Auto-focus and format code input
        const codeInput = document.getElementById('code');

        codeInput.addEventListener('input', function(e) {
            // Remove non-digits
            this.value = this.value.replace(/\D/g, '');

            // Limit to 6 digits
            if (this.value.length > 6) {
                this.value = this.value.slice(0, 6);
            }

            // Auto-submit when 6 digits are entered
            if (this.value.length === 6) {
                setTimeout(() => {
                    document.getElementById('verifyForm').submit();
                }, 300);
            }
        });

        // Auto-focus on load
        window.addEventListener('load', function() {
            codeInput.focus();
        });

        // Countdown timer (cosmetic)
        let timeLeft = 30;
        const countdown = document.getElementById('countdown');

        const timer = setInterval(function() {
            timeLeft--;
            countdown.textContent = timeLeft + 's';

            if (timeLeft <= 0) {
                timeLeft = 30; // Reset for demo purposes
            }

            if (timeLeft <= 10) {
                countdown.style.color = '#e53e3e';
            } else {
                countdown.style.color = '#1e40af';
            }
        }, 1000);

        // Add pulse animation to security icon
        const securityIcon = document.querySelector('.security-icon');
        setInterval(function() {
            securityIcon.style.transform = 'scale(1.05)';
            setTimeout(() => {
                securityIcon.style.transform = 'scale(1)';
            }, 200);
        }, 3000);
    </script>
</body>

</html>