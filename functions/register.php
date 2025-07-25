<?php
session_start();
include_once '../include/connection.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $full_name = trim($_POST['full_name']);
    $email = trim(strtolower($_POST['email']));
    $phone = trim($_POST['phone']);
    $gender = $_POST['gender'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    $errors = [];

    // Check if passwords match
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    // Validate password strength
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM customers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $errors[] = "Email already exists";
    }
    $stmt->close();

    if (empty($errors)) {
        try {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Generate email verification token
            $verification_token = bin2hex(random_bytes(32));

            // Insert user into database
            $stmt = $conn->prepare("
                INSERT INTO customers (
                    full_name, email, phone, password, gender, 
                    login_type, email_verified, status, 
                    remember_token, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, 'email', 0, 'inactive', ?, NOW(), NOW())
            ");

            $stmt->bind_param(
                "ssssss",
                $full_name,
                $email,
                $phone,
                $hashed_password,
                $gender,
                $verification_token
            );

            $result = $stmt->execute();

            if ($result) {
                $stmt->close();

                // Send verification email
                if (sendVerificationEmail($email, $full_name, $verification_token)) {
                    $_SESSION['success'] = "Registration successful! Please check your email to verify your account.";
                    header("Location: ../login.php");
                    exit();
                } else {
                    $_SESSION['error'] = "Registration successful but failed to send verification email. Please contact support.";
                    header("Location: ../register.php");
                    exit();
                }
            } else {
                $stmt->close();
                $_SESSION['error'] = "Registration failed. Please try again.";
                header("Location: ../register.php");
                exit();
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
            header("Location: ../register.php");
            exit();
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
        header("Location: ../register.php");
        exit();
    }
}

// Email verification function

function sendVerificationEmail($email, $full_name, $token)
{
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'kashaikh2802@gmail.com';
        $mail->Password   = 'wndq edvh medi nogd';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('noreply@BingoBikes.com', 'Bingo Bikes Team');
        $mail->addAddress($email, $full_name);

        // Content
        $verification_link = "http://localhost:8000/verify-email.php?token=" . $token;
        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Email Address';
        $mail->Body    = "
            <h2>Welcome to Our Platform!</h2>
            <p>Dear {$full_name},</p>
            <p>Click below to verify your email address:</p>
            <a href='{$verification_link}' style='
                background: #007bff; color: white; 
                padding: 10px 20px; text-decoration: none; 
                border-radius: 5px;'>Verify Email</a>
            <p>Or copy and paste this link:</p>
            <p>{$verification_link}</p>
        ";
        $mail->AltBody = "Please visit the following link to verify your email: {$verification_link}";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
