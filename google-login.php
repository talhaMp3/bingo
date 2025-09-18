<?php
session_start();
require 'vendor/autoload.php';
include_once './include/connection.php';

// Simple logger function
function logError($message)
{
    $log = "[" . date('Y-m-d H:i:s') . "] " . $message . PHP_EOL;
    file_put_contents(__DIR__ . '/logs/google-login.log', $log, FILE_APPEND);
}

// Check if Google credential exists
if (!isset($_POST['credential']) || empty($_POST['credential'])) {
    logError("Credential missing in POST");
    die("No credential received. Please try logging in again.");
}

$client = new Google_Client(['client_id' => '29999697984-l7ihjbcmdnettkh5f9fhr78sskghe8qm.apps.googleusercontent.com']);
$id_token = $_POST['credential'];

try {
    $payload = $client->verifyIdToken($id_token);
    if ($payload) {
        $email = $payload['email'];
        $name = $payload['name'];
        $google_id = $payload['sub'];
        $picture = $payload['picture'] ?? '';

        // Email validation (basic)
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            logError("Invalid email format: $email");
            die("Invalid email format.");
        }

        // Check if user exists
        $stmt = $conn->prepare("SELECT * FROM customers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $user = $res->fetch_assoc();

            // Status check
            if ($user['status'] !== 'active') {
                logError("Login blocked. Inactive user: $email");
                die("Your account is inactive. Please contact support.");
            }

            // Update last login
            $update = $conn->prepare("UPDATE customers SET last_login = NOW() WHERE id = ?");
            $update->bind_param("i", $user['id']);
            $update->execute();

            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['profile_image'] = $user['profile_image'];
        } else {
            // Create new user
            $insert = $conn->prepare("INSERT INTO customers (full_name, email, google_id, login_type, profile_image, email_verified, status, last_login) VALUES (?, ?, ?, 'google', ?, 1, 'active', NOW())");
            $insert->bind_param("ssss", $name, $email, $google_id, $picture);

            if ($insert->execute()) {
                $_SESSION['user_id'] = $insert->insert_id;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['profile_image'] = $picture;
            } else {
                logError("DB Insert Failed: " . $conn->error);
                die("Error creating user account. Please try again.");
            }
        }

        // Remember Me: Store login cookie for 7 days
        if (isset($_POST['remember_me']) && $_POST['remember_me'] === 'on') {
            $token = bin2hex(random_bytes(16));
            setcookie("remember_token", $token, time() + (7 * 24 * 60 * 60), "/");

            // Save token in DB (make sure the column exists in your customers table)
            $token_stmt = $conn->prepare("UPDATE customers SET remember_token = ? WHERE email = ?");
            $token_stmt->bind_param("ss", $token, $email);
            $token_stmt->execute();
        }

        $_SESSION['toast'] = [
            'type' => 'success',
            'message' => 'Logged in successfully with Google!'
        ];

        // Redirect logic
        $redirect_url = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : 'index.php';
        unset($_SESSION['redirect_after_login']);

        header("Location: $redirect_url");
        exit;
    } else {
        logError("Google ID Token verification failed.");
        echo "Invalid ID token. Please try logging in again.";
    }
} catch (Exception $e) {
    logError("Exception: " . $e->getMessage());
    echo "Error verifying Google login: " . $e->getMessage();
}
