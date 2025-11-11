<?php
/**
 * Authentication Helper Functions
 */

/**
 * Check if user is logged in
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && 
           $_SESSION['admin_logged_in'] === true && 
           isset($_SESSION['admin_id']);
}

/**
 * Require login - redirect to login if not logged in
 */
function requireLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Check if user is super admin
 */
function isSuperAdmin() {
    return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'super_admin';
}

/**
 * Require super admin access
 */
function requireSuperAdmin() {
    requireLogin();
    if (!isSuperAdmin()) {
        header('Location: index.php?error=access_denied');
        exit;
    }
}

/**
 * Login user
 */
function loginUser($username, $password) {
    global $conn;
    
    if (!isset($conn) || $conn === null) {
        return ['success' => false, 'message' => 'Database connection failed'];
    }
    
    try {
        $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = ? AND is_active = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Update last login
            $updateStmt = $conn->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
            $updateStmt->execute([$user['id']]);
            
            // Set session
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_email'] = $user['email'];
            $_SESSION['admin_full_name'] = $user['full_name'];
            $_SESSION['admin_role'] = $user['role'];
            
            return ['success' => true, 'user' => $user];
        } else {
            return ['success' => false, 'message' => 'Username atau password salah'];
        }
    } catch(PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Terjadi kesalahan sistem'];
    }
}

/**
 * Logout user
 */
function logoutUser() {
    $_SESSION = array();
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }
    session_destroy();
}

/**
 * Get current admin user info
 */
function getCurrentAdmin() {
    if (!isAdminLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['admin_id'],
        'username' => $_SESSION['admin_username'],
        'email' => $_SESSION['admin_email'],
        'full_name' => $_SESSION['admin_full_name'] ?? '',
        'role' => $_SESSION['admin_role'] ?? 'admin'
    ];
}

