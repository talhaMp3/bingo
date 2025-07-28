<?php

require dirname(__DIR__) . '../vendor/autoload.php';

$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$user = $_ENV['DB_USER'] ?? 'root';
$password = $_ENV['DB_PASSWORD'] ?? '';
$database = $_ENV['DB_DATABASE'] ?? 'bingo_cycle';

$conn = mysqli_connect($host, $user, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}



$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();
mysqli_set_charset($conn, "utf8");

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();


function log_activity($conn, $type, $action, $message, $user_type = 'admin', $user_id = null)
{
    $query = "INSERT INTO activity_logs (type, action, message, user_type, user_id) 
              VALUES (?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $query);

    if (!$stmt) {
        die("Prepare failed: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "ssssi", $type, $action, $message, $user_type, $user_id);
    mysqli_stmt_execute($stmt);

    // Optional: check if it worked
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        // Success
    } else {
        echo "Failed to log activity.";
    }

    mysqli_stmt_close($stmt);
}


// log_activity($conn, 'product', 'added', 'New product "BMX Pro" added', 'admin', 1);