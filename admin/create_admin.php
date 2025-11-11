<?php
/**
 * Script untuk membuat admin default jika belum ada
 * Akses file ini melalui browser untuk membuat admin default
 */

require_once __DIR__ . '/../config.php';

if (!isset($conn) || $conn === null) {
    die("❌ Database connection failed!");
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Default Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 600px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <i class="fas fa-user-shield fa-3x text-primary mb-3"></i>
                <h3>Create Default Admin</h3>
            </div>
            
            <?php
            try {
                // Check if admin_users table exists
                $tableCheck = $conn->query("SHOW TABLES LIKE 'admin_users'");
                if ($tableCheck->rowCount() == 0) {
                    // Create admin_users table
                    $adminTableSQL = "CREATE TABLE IF NOT EXISTS `admin_users` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `username` varchar(100) NOT NULL COMMENT 'Username admin',
                      `email` varchar(255) NOT NULL COMMENT 'Email admin',
                      `password` varchar(255) NOT NULL COMMENT 'Password (hashed)',
                      `full_name` varchar(255) DEFAULT NULL COMMENT 'Nama lengkap admin',
                      `role` enum('admin','super_admin') DEFAULT 'admin' COMMENT 'Role admin',
                      `is_active` tinyint(1) DEFAULT 1 COMMENT 'Status aktif (1=aktif, 0=nonaktif)',
                      `last_login` timestamp NULL DEFAULT NULL COMMENT 'Waktu login terakhir',
                      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu dibuat',
                      `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Waktu terakhir diupdate',
                      PRIMARY KEY (`id`),
                      UNIQUE KEY `unique_username` (`username`),
                      UNIQUE KEY `unique_email` (`email`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel untuk admin users'";
                    
                    $conn->exec($adminTableSQL);
                    echo '<div class="alert alert-success">✅ Tabel admin_users berhasil dibuat!</div>';
                }
                
                // Check if admin user already exists
                $adminCheck = $conn->query("SELECT * FROM admin_users WHERE username = 'admin'");
                if ($adminCheck->rowCount() > 0) {
                    echo '<div class="alert alert-warning">';
                    echo '⚠️ Admin user dengan username "admin" sudah ada!<br><br>';
                    echo '<strong>Login dengan:</strong><br>';
                    echo 'Username: <code>admin</code><br>';
                    echo 'Password: <code>admin123</code><br><br>';
                    echo '<a href="login.php" class="btn btn-primary mt-2">Ke Halaman Login</a>';
                    echo '</div>';
                } else {
                    // Create default admin user
                    $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
                    $insertAdmin = "INSERT INTO `admin_users` (`username`, `email`, `password`, `full_name`, `role`) 
                                    VALUES ('admin', 'admin@sewabusjogja.com', ?, 'Administrator', 'super_admin')";
                    $stmt = $conn->prepare($insertAdmin);
                    $stmt->execute([$defaultPassword]);
                    
                    echo '<div class="alert alert-success">';
                    echo '<h5><i class="fas fa-check-circle me-2"></i>Admin Default Berhasil Dibuat!</h5>';
                    echo '<hr>';
                    echo '<div class="bg-light p-3 rounded mt-3">';
                    echo '<strong>Kredensial Login:</strong><br><br>';
                    echo '<div class="row">';
                    echo '<div class="col-4"><strong>Username:</strong></div>';
                    echo '<div class="col-8"><code class="bg-white p-2 rounded d-inline-block">admin</code></div>';
                    echo '</div>';
                    echo '<div class="row mt-2">';
                    echo '<div class="col-4"><strong>Password:</strong></div>';
                    echo '<div class="col-8"><code class="bg-white p-2 rounded d-inline-block">admin123</code></div>';
                    echo '</div>';
                    echo '<div class="row mt-2">';
                    echo '<div class="col-4"><strong>Role:</strong></div>';
                    echo '<div class="col-8"><span class="badge bg-primary">Super Admin</span></div>';
                    echo '</div>';
                    echo '</div>';
                    echo '<div class="alert alert-warning mt-3">';
                    echo '<i class="fas fa-exclamation-triangle me-2"></i>';
                    echo '<strong>Penting!</strong> Setelah login, segera ubah password default untuk keamanan!';
                    echo '</div>';
                    echo '<a href="login.php" class="btn btn-primary btn-lg w-100 mt-3">';
                    echo '<i class="fas fa-sign-in-alt me-2"></i>Ke Halaman Login';
                    echo '</a>';
                    echo '</div>';
                }
                
            } catch(PDOException $e) {
                echo '<div class="alert alert-danger">';
                echo '<i class="fas fa-times-circle me-2"></i>';
                echo '<strong>Error:</strong> ' . htmlspecialchars($e->getMessage());
                echo '</div>';
            }
            ?>
            
            <div class="text-center mt-4">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Setelah selesai, hapus atau rename file ini untuk keamanan
                </small>
            </div>
        </div>
    </div>
</body>
</html>

