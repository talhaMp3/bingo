<?php
session_start();
include_once './include/connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
    $email = trim(strtolower($_POST['email']));

    // Check if user exists and is not verified
    $stmt = $pdo->prepare("SELECT id, full_name FROM customers WHERE email = ? AND email_verified = 0");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Generate new token
        $verification_token = bin2hex(random_bytes(32));

        // Update token
        $stmt = $pdo->prepare("UPDATE customers SET remember_token = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$verification_token, $user['id']]);

        // Send email (reuse the function from register-process.php)
        if (sendVerificationEmail($email, $user['full_name'], $verification_token)) {
            $_SESSION['success'] = "Verification email sent successfully!";
        } else {
            $_SESSION['error'] = "Failed to send verification email.";
        }
    } else {
        $_SESSION['error'] = "Email not found or already verified.";
    }
}

header("Location: login.php");
exit();
