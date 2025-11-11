<?php
// Database configuration
$db_host = 'localhost';
$db_user = 'sewabusjo_nIqUPo';
$db_pass = 'Wiyachan123.';
$db_name = 'sewabusjo_nIqUPo';

// Create database connection
try {
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // For development only. In production, log errors instead of displaying them
    // echo "Connection failed: " . $e->getMessage();
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