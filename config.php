<?php
// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'sewabus';

// Initialize connection variable
$conn = null;

// Create database connection
try {
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $conn = new PDO($dsn, $db_user, $db_pass, $options);
} catch(PDOException $e) {
    // Log error in production
    error_log("Database connection failed: " . $e->getMessage());
    // Set $conn to null so we can check it later
    $conn = null;
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