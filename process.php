<?php
// Include config file
require_once __DIR__ . '/config.php';

// Check if database connection exists
if (!isset($conn) || $conn === null) {
    error_log("Database connection not established in process.php");
    header("Location: index.php#contact?status=error");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input (mengganti FILTER_SANITIZE_STRING yang deprecated)
    $name = isset($_POST['name']) ? trim(htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8')) : '';
    $email = isset($_POST['email']) ? filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) : '';
    $phone = isset($_POST['phone']) ? trim(htmlspecialchars($_POST['phone'], ENT_QUOTES, 'UTF-8')) : '';
    $message = isset($_POST['message']) ? trim(htmlspecialchars($_POST['message'], ENT_QUOTES, 'UTF-8')) : '';
    
    // Validate input
    if (empty($name) || empty($email) || empty($phone) || empty($message)) {
        header("Location: index.php#contact?status=invalid");
        exit();
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: index.php#contact?status=invalid_email");
        exit();
    }
    
    try {
        // Prepare SQL statement
        $stmt = $conn->prepare("INSERT INTO messages (name, email, phone, message) VALUES (?, ?, ?, ?)");
        
        // Execute with parameters
        $stmt->execute([$name, $email, $phone, $message]);
        
        // Check if insert was successful
        if ($stmt->rowCount() > 0) {
            // Redirect back with success message
            header("Location: index.php#contact?status=success");
            exit();
        } else {
            // No rows inserted
            header("Location: index.php#contact?status=error");
            exit();
        }
    } catch(PDOException $e) {
        // Log error (jangan tampilkan error ke user di production)
        error_log("Error inserting message: " . $e->getMessage());
        
        // Redirect with error message
        header("Location: index.php#contact?status=error");
        exit();
    }
} else {
    // If not POST request, redirect to home
    header("Location: index.php");
    exit();
}
?>