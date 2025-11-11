<?php
/**
 * Simple database connection test
 * Akses file ini untuk melihat error koneksi database secara detail
 */

// Database configuration
$db_host = 'localhost';
$db_user = 'sewabusjo_nIqUPo';
$db_pass = 'Wiyachan123.';
$db_name = 'sewabusjo_nIqUPo';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Test Database Connection</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: green; background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { color: red; background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>🔌 Test Koneksi Database</h1>
    
    <div class="info">
        <strong>Konfigurasi:</strong><br>
        Host: <code><?php echo htmlspecialchars($db_host); ?></code><br>
        Database: <code><?php echo htmlspecialchars($db_name); ?></code><br>
        User: <code><?php echo htmlspecialchars($db_user); ?></code><br>
        Password: <code>***</code>
    </div>
    
    <?php
    echo "<h2>Test 1: Koneksi tanpa database</h2>";
    try {
        $testConn = new PDO("mysql:host=$db_host", $db_user, $db_pass);
        $testConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo '<div class="success">✅ Koneksi ke MySQL server berhasil!</div>';
        
        // Check if database exists
        echo "<h2>Test 2: Cek apakah database ada</h2>";
        $databases = $testConn->query("SHOW DATABASES LIKE '$db_name'")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array($db_name, $databases)) {
            echo '<div class="success">✅ Database <code>' . htmlspecialchars($db_name) . '</code> ditemukan!</div>';
        } else {
            echo '<div class="error">❌ Database <code>' . htmlspecialchars($db_name) . '</code> TIDAK ditemukan!</div>';
            echo '<div class="info">Database yang tersedia:<br>';
            $allDbs = $testConn->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
            foreach ($allDbs as $db) {
                if (!in_array($db, ['information_schema', 'performance_schema', 'mysql', 'sys'])) {
                    echo '- <code>' . htmlspecialchars($db) . '</code><br>';
                }
            }
            echo '</div>';
        }
        
    } catch(PDOException $e) {
        echo '<div class="error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        echo '<div class="info">Kemungkinan penyebab:<br>';
        echo '1. MySQL server tidak berjalan<br>';
        echo '2. Host salah (jika hosting, coba gunakan IP atau hostname yang diberikan)<br>';
        echo '3. Username/password salah<br>';
        echo '4. Port MySQL tidak standar (3306)</div>';
    }
    
    echo "<h2>Test 3: Koneksi dengan database</h2>";
    try {
        $conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo '<div class="success">✅ Koneksi ke database berhasil!</div>';
        
        // Test query
        $result = $conn->query("SELECT 1 as test")->fetch();
        echo '<div class="success">✅ Query test berhasil!</div>';
        
    } catch(PDOException $e) {
        echo '<div class="error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        echo '<div class="info">Error Code: ' . $e->getCode() . '</div>';
    }
    ?>
    
    <hr>
    <div class="info">
        <strong>Catatan:</strong> Setelah selesai debugging, hapus atau rename file ini untuk keamanan.
    </div>
</body>
</html>

