<?php
session_start();
require_once '../include/connection.php'; // mysqli connection
require_once __DIR__ . '/../vendor/autoload.php';

use Sonata\GoogleAuthenticator\GoogleAuthenticator;

if (!isset($_SESSION['pending_admin_id'])) {
    header("Location: login.php");
    exit();
}

$g = new GoogleAuthenticator();

// 1. Generate secret
$secret = $g->generateSecret();

// 2. Save secret to DB for this admin
$stmt = $conn->prepare("UPDATE admins SET google2fa_secret = ? WHERE id = ?");
$stmt->bind_param("si", $secret, $_SESSION['pending_admin_id']);
$stmt->execute();

// 3. Fetch admin email from database
$stmt = $conn->prepare("SELECT email FROM admins WHERE id = ?");
$stmt->bind_param("i", $_SESSION['pending_admin_id']);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$adminEmail = $admin['email'] ?? 'admin@admin.com';

// 4. Build otpauth:// URI properly
$issuer = "Bingo Admin";
$accountName = $adminEmail;
$otpauth = sprintf(
    "otpauth://totp/%s:%s?secret=%s&issuer=%s",
    rawurlencode($issuer),
    rawurlencode($accountName),
    $secret,
    rawurlencode($issuer)
);

// 5. Generate QR code using Google Charts API with proper encoding
$qrCodeUrl = "https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=" . urlencode($otpauth);

// Alternative: Use QR Server API (more reliable)
$qrCodeUrlAlt = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($otpauth);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Two-Factor Authentication - Bingo Admin</title>
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

        .setup-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-align: center;
        }

        .security-header {
            margin-bottom: 30px;
        }

        .security-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 30px rgba(72, 187, 120, 0.3);
            position: relative;
        }

        .security-icon::before {
            content: '';
            position: absolute;
            width: 100px;
            height: 100px;
            border: 2px solid rgba(72, 187, 120, 0.3);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 1;
            }

            100% {
                transform: scale(1.2);
                opacity: 0;
            }
        }

        .security-icon i {
            font-size: 32px;
            color: white;
            z-index: 1;
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
            line-height: 1.5;
        }

        .setup-steps {
            display: flex;
            justify-content: space-between;
            margin: 30px 0;
            padding: 0 10px;
        }

        .step {
            flex: 1;
            text-align: center;
            position: relative;
        }

        .step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 20px;
            right: -50%;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            z-index: 0;
        }

        .step-number {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            color: white;
            font-weight: bold;
            font-size: 16px;
            position: relative;
            z-index: 1;
        }

        .step.active .step-number {
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.2);
            animation: glow 2s infinite alternate;
        }

        @keyframes glow {
            from {
                box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.2);
            }

            to {
                box-shadow: 0 0 0 8px rgba(102, 126, 234, 0.4);
            }
        }

        .step-text {
            font-size: 12px;
            color: #4a5568;
            font-weight: 500;
        }

        .qr-section {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            border-radius: 16px;
            padding: 30px;
            margin: 30px 0;
            border: 1px solid #e2e8f0;
        }

        .qr-title {
            color: #2d3748;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .qr-code-container {
            background: white;
            border-radius: 12px;
            padding: 20px;
            display: inline-block;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            position: relative;
        }

        .qr-code-container::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 14px;
            z-index: -1;
        }

        .qr-code {
            display: block;
            width: 200px;
            height: 200px;
            border-radius: 8px;
        }

        .manual-section {
            background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%);
            border-radius: 16px;
            padding: 25px;
            margin: 25px 0;
            border: 1px solid #fbb6ce;
        }

        .manual-title {
            color: #2d3748;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .secret-code {
            background: white;
            padding: 15px 20px;
            border-radius: 10px;
            font-family: 'Courier New', monospace;
            font-size: 18px;
            font-weight: bold;
            color: #2d3748;
            letter-spacing: 2px;
            word-break: break-all;
            border: 2px solid #e2e8f0;
            position: relative;
            margin-bottom: 15px;
        }

        .copy-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: #667eea;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .copy-btn:hover {
            background: #5a67d8;
            transform: translateY(-50%) scale(1.05);
        }

        .instructions {
            background: linear-gradient(135deg, #ebf8ff 0%, #bee3f8 100%);
            border-radius: 16px;
            padding: 25px;
            margin: 25px 0;
            border: 1px solid #90cdf4;
            text-align: left;
        }

        .instructions-title {
            color: #2d3748;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .instructions ol {
            list-style: none;
            counter-reset: step-counter;
        }

        .instructions li {
            counter-increment: step-counter;
            margin-bottom: 12px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            color: #4a5568;
            line-height: 1.5;
        }

        .instructions li::before {
            content: counter(step-counter);
            background: linear-gradient(135deg, #3182ce, #2b77cb);
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .continue-btn {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white;
            padding: 18px 40px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
            position: relative;
            overflow: hidden;
        }

        .continue-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(72, 187, 120, 0.3);
        }

        .continue-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .continue-btn:hover::before {
            left: 100%;
        }

        .app-badges {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin: 20px 0;
            flex-wrap: wrap;
        }

        .app-badge {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 15px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: #4a5568;
            transition: all 0.3s ease;
        }

        .app-badge:hover {
            border-color: #667eea;
            transform: translateY(-2px);
        }

        .app-badge i {
            font-size: 16px;
            color: #667eea;
        }

        /* Responsive design */
        @media (max-width: 480px) {
            .setup-container {
                padding: 30px 25px;
                margin: 10px;
            }

            .header-section h1 {
                font-size: 24px;
            }

            .setup-steps {
                flex-direction: column;
                gap: 15px;
            }

            .step:not(:last-child)::after {
                display: none;
            }

            .app-badges {
                flex-direction: column;
                align-items: center;
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
            animation: float 10s ease-in-out infinite;
        }

        .particle:nth-child(1) {
            width: 60px;
            height: 60px;
            left: 10%;
            animation-delay: 0s;
        }

        .particle:nth-child(2) {
            width: 80px;
            height: 80px;
            left: 80%;
            animation-delay: 4s;
        }

        .particle:nth-child(3) {
            width: 40px;
            height: 40px;
            left: 50%;
            animation-delay: 8s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px) rotate(0deg);
                opacity: 0.3;
            }

            50% {
                transform: translateY(-40px) rotate(180deg);
                opacity: 0.7;
            }
        }

        .success-message {
            background: linear-gradient(135deg, #f0fff4, #c6f6d5);
            border: 2px solid #9ae6b4;
            color: #276749;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
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

    <div class="setup-container">
        <div class="security-header">
            <div class="security-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div class="header-section">
                <h1>Setup Two-Factor Authentication</h1>
                <p>Secure your account with an additional layer of protection</p>
            </div>
        </div>

        <div class="setup-steps">
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-text">Admin Login</div>
            </div>
            <div class="step active">
                <div class="step-number">2</div>
                <div class="step-text">Scan QR Code</div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-text">Verify Setup</div>
            </div>
        </div>
        <div class="qr-section">
            <div class="qr-title">
                <i class="fas fa-qrcode"></i>
                Scan QR Code
            </div>
            <div class="qr-code-container">
                <img src="<?php echo htmlspecialchars($qrCodeUrl); ?>"
                    alt="2FA QR Code"
                    class="qr-code"
                    onerror="this.src='<?php echo htmlspecialchars($qrCodeUrlAlt); ?>'">
            </div>
            <p style="color: #718096; font-size: 14px;">
                Point your authenticator app's camera at this QR code
            </p>
        </div>

        <div class="manual-section">
            <div class="manual-title">
                <i class="fas fa-keyboard"></i>
                Manual Entry Option
            </div>
            <p style="color: #718096; font-size: 14px; margin-bottom: 15px;">
                Can't scan? Enter this secret code manually:
            </p>
            <div class="secret-code">
                <?php echo htmlspecialchars($secret); ?>
                <button class="copy-btn" onclick="copySecret()">
                    <i class="fas fa-copy"></i> Copy
                </button>
            </div>
        </div>

        <div class="instructions">
            <div class="instructions-title">
                <i class="fas fa-list-ol"></i>
                Setup Instructions
            </div>
            <ol>
                <li>Open Google Authenticator or any TOTP app on your mobile device</li>
                <li>Tap the "+" button to add a new account</li>
                <li>Choose "Scan QR code" and point your camera at the code above</li>
                <li>Your app will generate 6-digit codes every 30 seconds</li>
                <li>Click "Continue" below to verify your setup</li>
            </ol>
        </div>

        <a href="verify_2fa.php" class="continue-btn">
            <i class="fas fa-arrow-right"></i>
            Continue to Verification
        </a>
    </div>

    <script>
        function copySecret() {
            const secretText = '<?php echo $secret; ?>';
            const copyBtn = document.querySelector('.copy-btn');

            navigator.clipboard.writeText(secretText).then(function() {
                const originalContent = copyBtn.innerHTML;
                copyBtn.innerHTML = '<i class="fas fa-check"></i> Copied!';
                copyBtn.style.background = '#48bb78';

                setTimeout(function() {
                    copyBtn.innerHTML = originalContent;
                    copyBtn.style.background = '#667eea';
                }, 2000);
            }).catch(function() {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = secretText;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);

                const originalContent = copyBtn.innerHTML;
                copyBtn.innerHTML = '<i class="fas fa-check"></i> Copied!';
                copyBtn.style.background = '#48bb78';

                setTimeout(function() {
                    copyBtn.innerHTML = originalContent;
                    copyBtn.style.background = '#667eea';
                }, 2000);
            });
        }

        // Add some interactive feedback
        document.querySelector('.qr-code').addEventListener('load', function() {
            this.style.animation = 'fadeIn 0.5s ease-in';
        });

        // Animate step progression
        setTimeout(function() {
            document.querySelector('.step.active .step-number').style.transform = 'scale(1.1)';
        }, 1000);
    </script>
</body>

</html>