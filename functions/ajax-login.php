<?php
session_start();
require_once '../include/connection.php';

header('Content-Type: application/json');

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember_me']);

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Please enter both email and password.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
    exit;
}

$stmt = $conn->prepare("SELECT id, email, full_name, profile_image, password, status FROM customers WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Account not found.']);
    exit;
}

$user = $result->fetch_assoc();

if ($user['status'] !== 'active') {
    echo json_encode(['success' => false, 'message' => 'Your account is ' . $user['status'] . '.']);
    exit;
}

if (!password_verify($password, $user['password'])) {
    echo json_encode(['success' => false, 'message' => 'Incorrect password.']);
    exit;
}

// ✅ All good — login the user!
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_name'] = $user['full_name'];
$_SESSION['profile_image'] = $user['profile_image'] ?? null;

// Update login time
$update = $conn->prepare("UPDATE customers SET last_login = NOW() WHERE id = ?");
$update->bind_param("i", $user['id']);
$update->execute();

// Handle remember token
if ($remember) {
    $token = bin2hex(random_bytes(32));
    $expire_time = date('Y-m-d H:i:s', strtotime('+7 days'));

    $token_update = $conn->prepare("UPDATE customers SET remember_token = ?, remember_expires = ? WHERE id = ?");
    $token_update->bind_param("ssi", $token, $expire_time, $user['id']);
    $token_update->execute();

    setcookie(
        "remember_token",
        $token,
        [
            'expires' => time() + (7 * 24 * 60 * 60),
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax'
        ]
    );
}

// ✅ Respond with success
echo json_encode([
    'success' => true,
    'message' => 'Login successful!',
    'redirect' => $_POST['redirect_to'] ?? null
]);
exit;
