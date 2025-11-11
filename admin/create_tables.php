<?php
/**
 * Script untuk membuat tabel articles jika belum ada
 * Akses file ini sekali untuk membuat tabel
 */

require_once __DIR__ . '/../config.php';

if (!isset($conn) || $conn === null) {
    die("Database connection failed!");
}

try {
    // Create articles table
    $sql = "CREATE TABLE IF NOT EXISTS `articles` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `title` varchar(255) NOT NULL COMMENT 'Judul artikel',
      `slug` varchar(255) NOT NULL COMMENT 'URL slug untuk artikel',
      `excerpt` text DEFAULT NULL COMMENT 'Ringkasan artikel',
      `content` longtext NOT NULL COMMENT 'Isi artikel lengkap',
      `featured_image` varchar(500) DEFAULT NULL COMMENT 'Gambar utama artikel',
      `author_id` int(11) DEFAULT NULL COMMENT 'ID admin yang membuat artikel',
      `status` enum('draft','published','archived') DEFAULT 'draft' COMMENT 'Status artikel',
      `views` int(11) DEFAULT 0 COMMENT 'Jumlah view',
      `published_at` timestamp NULL DEFAULT NULL COMMENT 'Waktu publish',
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu dibuat',
      `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Waktu terakhir diupdate',
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_slug` (`slug`),
      KEY `idx_status` (`status`),
      KEY `idx_author_id` (`author_id`),
      KEY `idx_published_at` (`published_at`),
      KEY `idx_created_at` (`created_at`),
      CONSTRAINT `fk_articles_author` FOREIGN KEY (`author_id`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel untuk artikel/berita'";
    
    $conn->exec($sql);
    echo "✅ Tabel articles berhasil dibuat!<br>";
    
    // Check if admin_users table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'admin_users'");
    if ($tableCheck->rowCount() == 0) {
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
        echo "✅ Tabel admin_users berhasil dibuat!<br>";
        
        // Create default admin user (password: admin123)
        $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $insertAdmin = "INSERT INTO `admin_users` (`username`, `email`, `password`, `full_name`, `role`) 
                        VALUES ('admin', 'admin@sewabusjogja.com', ?, 'Administrator', 'super_admin')";
        $stmt = $conn->prepare($insertAdmin);
        $stmt->execute([$defaultPassword]);
        echo "✅ Default admin user berhasil dibuat!<br>";
        echo "Username: admin<br>Password: admin123<br>";
    }
    
    echo "<br>✅ Semua tabel siap digunakan!<br>";
    echo "<a href='login.php'>Kembali ke Login</a>";
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}

