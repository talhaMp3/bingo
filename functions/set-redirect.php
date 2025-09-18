<?php
session_start();

// Store the page where user currently is
if (isset($_POST['redirect_url'])) {
    $_SESSION['redirect_after_login'] = $_POST['redirect_url'];
    echo json_encode(['status' => 'success']);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'No URL received']);
exit;
