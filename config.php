<?php
// Database configuration
$db_host = 'localhost';
$db_user = 'sewabusjogja';
$db_pass = 'Wiyachan123.';
$db_name = 'sewabusjogja';

// Create database connection
$conn = null;
$db_error = null;
try {
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // Store error for debugging
    $db_error = $e->getMessage();
    error_log("Database connection failed: " . $db_error);
    // Don't set $conn, let it remain null so we can check it
}

// Include media helper functions
if (file_exists(__DIR__ . '/includes/media_helper.php')) {
    require_once __DIR__ . '/includes/media_helper.php';
}

// Site configuration
$site_name = 'Bus Jogja';
$site_email = 'info@busjogja.com';
$site_phone = '+62 812-3456-789';
$site_address = 'Jl. Malioboro No. 123, Yogyakarta';
?>