<?php
/**
 * Script untuk mengecek dan membuat tabel messages jika belum ada
 * Akses file ini melalui browser untuk mengecek status database
 */

require_once __DIR__ . '/config.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Check - Sewa Bus Jogja</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .success {
            color: #28a745;
            background: #d4edda;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .error {
            color: #dc3545;
            background: #f8d7da;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .info {
            color: #0c5460;
            background: #d1ecf1;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .warning {
            color: #856404;
            background: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }
        button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Database Check - Tabel Messages</h1>
        
        <?php
        // Check database connection
        if (!isset($conn) || $conn === null) {
            echo '<div class="error">❌ <strong>Error:</strong> Koneksi database tidak berhasil!</div>';
            
            // Display error message if available
            if (isset($db_error) && !empty($db_error)) {
                echo '<div class="error"><strong>Detail Error:</strong><br>';
                echo '<code>' . htmlspecialchars($db_error) . '</code></div>';
            }
            
            echo '<div class="info"><strong>Konfigurasi saat ini:</strong><br>';
            echo 'Host: <code>' . htmlspecialchars($db_host ?? 'tidak terdefinisi') . '</code><br>';
            echo 'Database: <code>' . htmlspecialchars($db_name ?? 'tidak terdefinisi') . '</code><br>';
            echo 'User: <code>' . htmlspecialchars($db_user ?? 'tidak terdefinisi') . '</code><br>';
            echo 'Password: <code>' . (isset($db_pass) ? '***' : 'tidak terdefinisi') . '</code></div>';
            
            echo '<div class="warning"><strong>Kemungkinan penyebab:</strong><br>';
            echo '1. Kredensial database salah<br>';
            echo '2. Database belum dibuat<br>';
            echo '3. Server MySQL/MariaDB tidak berjalan<br>';
            echo '4. Host database salah (bukan localhost)<br>';
            echo '5. Firewall memblokir koneksi</div>';
            
            echo '<div class="info"><strong>Langkah troubleshooting:</strong><br>';
            echo '1. Pastikan MySQL/MariaDB server berjalan<br>';
            echo '2. Cek apakah database <code>' . htmlspecialchars($db_name ?? '') . '</code> sudah dibuat<br>';
            echo '3. Coba login ke database dengan kredensial yang sama menggunakan phpMyAdmin atau MySQL client<br>';
            echo '4. Jika menggunakan hosting, pastikan host bukan "localhost" tapi IP atau hostname yang diberikan hosting</div>';
            
            exit;
        }
        
        echo '<div class="success">✅ <strong>Koneksi database berhasil!</strong></div>';
        // Get database name from connection
        $dbName = $conn->query("SELECT DATABASE()")->fetchColumn();
        echo '<div class="info">Database: <code>' . htmlspecialchars($dbName) . '</code></div>';
        
        // Check if table exists
        try {
            $tableCheck = $conn->query("SHOW TABLES LIKE 'messages'");
            $tableExists = $tableCheck->rowCount() > 0;
            
            if ($tableExists) {
                echo '<div class="success">✅ <strong>Tabel <code>messages</code> sudah ada!</strong></div>';
                
                // Check table structure
                $columns = $conn->query("DESCRIBE messages");
                echo '<div class="info"><strong>Struktur Tabel:</strong><br>';
                echo '<table border="1" cellpadding="5" style="border-collapse: collapse; width: 100%; margin-top: 10px;">';
                echo '<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>';
                while ($col = $columns->fetch(PDO::FETCH_ASSOC)) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($col['Field']) . '</td>';
                    echo '<td>' . htmlspecialchars($col['Type']) . '</td>';
                    echo '<td>' . htmlspecialchars($col['Null']) . '</td>';
                    echo '<td>' . htmlspecialchars($col['Key']) . '</td>';
                    echo '<td>' . htmlspecialchars($col['Default'] ?? 'NULL') . '</td>';
                    echo '</tr>';
                }
                echo '</table></div>';
                
                // Count existing messages
                $count = $conn->query("SELECT COUNT(*) as total FROM messages")->fetch(PDO::FETCH_ASSOC);
                echo '<div class="info">Total pesan di database: <strong>' . $count['total'] . '</strong></div>';
                
                // Test insert
                if (isset($_GET['test_insert'])) {
                    try {
                        $testName = 'Test User';
                        $testEmail = 'test@example.com';
                        $testPhone = '081234567890';
                        $testMessage = 'Ini adalah pesan test dari database checker';
                        
                        $stmt = $conn->prepare("INSERT INTO messages (name, email, phone, message, status) VALUES (?, ?, ?, ?, 'new')");
                        $result = $stmt->execute([$testName, $testEmail, $testPhone, $testMessage]);
                        $insertId = $conn->lastInsertId();
                        
                        if ($result && $insertId > 0) {
                            echo '<div class="success">✅ <strong>Test Insert Berhasil!</strong> ID: ' . $insertId . '</div>';
                            
                            // Delete test record
                            $conn->prepare("DELETE FROM messages WHERE id = ?")->execute([$insertId]);
                            echo '<div class="info">Record test telah dihapus.</div>';
                        } else {
                            echo '<div class="error">❌ <strong>Test Insert Gagal!</strong></div>';
                        }
                    } catch (PDOException $e) {
                        echo '<div class="error">❌ <strong>Error saat test insert:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
                    }
                } else {
                    echo '<a href="?test_insert=1"><button>🧪 Test Insert ke Database</button></a>';
                }
                
            } else {
                echo '<div class="warning">⚠️ <strong>Tabel <code>messages</code> belum ada!</strong></div>';
                
                // Create table
                if (isset($_GET['create_table'])) {
                    try {
                        $createTableSQL = "CREATE TABLE IF NOT EXISTS `messages` (
                          `id` int(11) NOT NULL AUTO_INCREMENT,
                          `name` varchar(255) NOT NULL COMMENT 'Nama lengkap pengirim',
                          `email` varchar(255) NOT NULL COMMENT 'Email pengirim',
                          `phone` varchar(20) NOT NULL COMMENT 'Nomor telepon/WhatsApp',
                          `message` text NOT NULL COMMENT 'Pesan dari pengirim',
                          `status` enum('new','read','replied','archived') DEFAULT 'new' COMMENT 'Status pesan',
                          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu pesan dikirim',
                          `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Waktu terakhir diupdate',
                          PRIMARY KEY (`id`),
                          KEY `idx_status` (`status`),
                          KEY `idx_created_at` (`created_at`),
                          KEY `idx_email` (`email`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel untuk menyimpan data form kontak'";
                        
                        $conn->exec($createTableSQL);
                        echo '<div class="success">✅ <strong>Tabel <code>messages</code> berhasil dibuat!</strong></div>';
                        echo '<meta http-equiv="refresh" content="2;url=check_database.php">';
                    } catch (PDOException $e) {
                        echo '<div class="error">❌ <strong>Error saat membuat tabel:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
                    }
                } else {
                    echo '<div class="info">Klik tombol di bawah untuk membuat tabel <code>messages</code>:</div>';
                    echo '<a href="?create_table=1"><button>🔨 Buat Tabel Messages</button></a>';
                }
            }
            
        } catch (PDOException $e) {
            echo '<div class="error">❌ <strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>
        
        <hr style="margin: 30px 0;">
        <div class="info">
            <strong>Catatan:</strong><br>
            • File ini aman untuk diakses dan hanya untuk debugging<br>
            • Setelah selesai, hapus atau rename file ini untuk keamanan<br>
            • Pastikan error log PHP aktif untuk melihat detail error
        </div>
    </div>
</body>
</html>

