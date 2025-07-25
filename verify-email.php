<?php
session_start();
include_once './include/connection.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Prepare and execute select query
    $stmt = $conn->prepare("
        SELECT id, email, full_name, created_at 
        FROM customers 
        WHERE remember_token = ? AND email_verified = 0
    ");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user) {
        // Token age check
        $created_time = new DateTime($user['created_at']);
        $current_time = new DateTime();
        $diff = $current_time->diff($created_time);

        if ($diff->days < 1) {
            // Update email_verified and status
            $stmt = $conn->prepare("
                UPDATE customers 
                SET email_verified = 1, status = 'active', remember_token = NULL, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->bind_param("i", $user['id']);
            $result = $stmt->execute();
            $stmt->close();

            if ($result) {
                $_SESSION['success'] = "Email verified successfully! You can now login.";
                header("Location: login.php");
                exit();
            } else {
                $_SESSION['error'] = "Failed to verify email. Please try again.";
            }
        } else {
            $_SESSION['error'] = "Verification link has expired. Please register again.";
        }
    } else {
        $_SESSION['error'] = "Invalid or already used verification link.";
    }
} else {
    $_SESSION['error'] = "No verification token provided.";
}

header("Location: register.php");
exit();
