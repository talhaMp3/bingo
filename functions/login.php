<?php
session_start();
include_once '../include/connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $remember_me = isset($_POST['remember_me']) ? true : false;

    // Basic validation
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Please fill in all fields.";
        header("Location: ../login.php");
        exit();
    }

    // Email format validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Please enter a valid email address.";
        header("Location: ../login.php");
        exit();
    }

    try {
        // Check user credentials with status check
        $stmt = $conn->prepare("SELECT id, full_name, email, password, phone, gender, status FROM customers WHERE email = ? AND status = 'active'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            // Login successful - store additional user data in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_phone'] = $user['phone'];
            $_SESSION['user_gender'] = $user['gender'];
            $_SESSION['logged_in'] = true;

            // Handle remember me functionality
            if ($remember_me) {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+30 days'));

                // Store remember token in database
                $update_stmt = $conn->prepare("UPDATE customers SET remember_token = ?, remember_expires = ? WHERE id = ?");
                $update_stmt->bind_param("ssi", $token, $expires, $user['id']);
                $update_stmt->execute();

                // Set remember me cookie (secure and httponly)
                setcookie('remember_token', $token, strtotime('+30 days'), '/', '', isset($_SERVER['HTTPS']), true);
            }

            // Update last login timestamp
            $login_stmt = $conn->prepare("UPDATE customers SET last_login = NOW() WHERE id = ?");
            $login_stmt->bind_param("i", $user['id']);
            $login_stmt->execute();

            $_SESSION['success'] = "Login successful! Welcome back, " . $user['full_name'];

            // Redirect after login feature
            $redirect = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : 'dashboard.php';
            unset($_SESSION['redirect_after_login']);

            // Detailed error logging for successful login
            error_log("Successful login for user ID: " . $user['id'] . " Email: " . $email . " IP: " . $_SERVER['REMOTE_ADDR'] . " Time: " . date('Y-m-d H:i:s'));

            header("Location: $redirect");
            exit();
        } else {
            $_SESSION['error'] = "Invalid email or password.";

            // Detailed error logging for failed login
            error_log("Failed login attempt for email: " . $email . " IP: " . $_SERVER['REMOTE_ADDR'] . " Time: " . date('Y-m-d H:i:s') . " Reason: Invalid credentials");

            header("Location: ../login.php");
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Login failed. Please try again.";

        // Detailed error logging for exceptions
        error_log("Login error for email: " . $email . " Error: " . $e->getMessage() . " IP: " . $_SERVER['REMOTE_ADDR'] . " Time: " . date('Y-m-d H:i:s'));

        header("Location: ../login.php");
        exit();
    }
} else {
    header("Location: ../login.php");
    exit();
}
