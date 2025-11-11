<?php
// Include config file
require_once __DIR__ . '/config.php';

// Check if database connection exists
if (!isset($conn) || $conn === null) {
    error_log("Database connection not established in process.php");
    header("Location: index.php?status=error#contact");
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
        header("Location: index.php?status=invalid#contact");
        exit();
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: index.php?status=invalid_email#contact");
        exit();
    }
    
    try {
        // Check if table exists first
        $tableCheck = $conn->query("SHOW TABLES LIKE 'messages'");
        if ($tableCheck->rowCount() == 0) {
            error_log("Table 'messages' does not exist in database");
            header("Location: index.php?status=error#contact");
            exit();
        }
        
        // Prepare SQL statement - explicitly include status column with default
        $stmt = $conn->prepare("INSERT INTO messages (name, email, phone, message, status) VALUES (?, ?, ?, ?, 'new')");
        
        // Execute with parameters
        $result = $stmt->execute([$name, $email, $phone, $message]);
        
        // Check if insert was successful
        // Use lastInsertId() as it's more reliable for INSERT operations
        $insertId = $conn->lastInsertId();
        if ($result && $insertId > 0) {
            // Log success for debugging (optional)
            error_log("Message inserted successfully with ID: " . $insertId);
            
            // Redirect back with success message
            header("Location: index.php?status=success#contact");
            exit();
        } else {
            // No rows inserted
            error_log("Failed to insert message: result=" . ($result ? 'true' : 'false') . ", rowCount=" . $stmt->rowCount() . ", lastInsertId=" . $insertId);
            header("Location: index.php?status=error#contact");
            exit();
        }
    } catch(PDOException $e) {
        // Log error (jangan tampilkan error ke user di production)
        error_log("Error inserting message: " . $e->getMessage());
        error_log("SQL Error Code: " . $e->getCode());
        error_log("SQL State: " . $e->getCode());
        error_log("SQL Query: INSERT INTO messages (name, email, phone, message, status) VALUES (?, ?, ?, ?, 'new')");
        
        // Redirect with error message
        header("Location: index.php?status=error#contact");
        exit();
    }
} else {
    // If not POST request, redirect to home
    header("Location: index.php");
    exit();
}
?>