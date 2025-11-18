<?php
/**
 * Script untuk membuat tabel armada jika belum ada
 */

require_once __DIR__ . '/../config.php';

if (!isset($conn) || $conn === null) {
    die("Database connection failed!");
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Create Armada Table</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-5">
    <div class="container">
        <h2>Create Armada Table</h2>
        <?php
        try {
            // Create armada table
            $sql = "CREATE TABLE IF NOT EXISTS `armada` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(100) NOT NULL COMMENT 'Nama armada (Big Bus, Medium Bus, Mini Bus)',
              `capacity` varchar(50) NOT NULL COMMENT 'Kapasitas (59 Kursi, 35 Kursi, dll)',
              `slug` varchar(100) NOT NULL COMMENT 'URL slug',
              `image_path` varchar(500) DEFAULT NULL COMMENT 'Path gambar armada',
              `media_key` varchar(100) DEFAULT NULL COMMENT 'Key media untuk referensi',
              `features` text DEFAULT NULL COMMENT 'JSON array fasilitas',
              `description` text DEFAULT NULL COMMENT 'Deskripsi armada',
              `is_active` tinyint(1) DEFAULT 1 COMMENT 'Status aktif',
              `sort_order` int(11) DEFAULT 0 COMMENT 'Urutan tampil',
              `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `unique_slug` (`slug`),
              KEY `idx_is_active` (`is_active`),
              KEY `idx_sort_order` (`sort_order`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel untuk data armada bus'";
            
            $conn->exec($sql);
            echo '<div class="alert alert-success">✅ Tabel armada berhasil dibuat!</div>';
            
            // Check if data exists
            $check = $conn->query("SELECT COUNT(*) as count FROM armada")->fetch(PDO::FETCH_ASSOC);
            if ($check['count'] == 0) {
                // Insert default data
                $defaultArmada = [
                    [
                        'name' => 'Big Bus',
                        'capacity' => '59 Kursi',
                        'slug' => 'big-bus-59-kursi',
                        'media_key' => 'armada_big_bus',
                        'features' => json_encode(['AC Dingin', 'Reclining Seat', 'LCD TV', 'Toilet', 'Bagasi Luas']),
                        'description' => 'Bus besar dengan kapasitas 59 kursi, cocok untuk perjalanan jarak jauh dengan fasilitas lengkap.',
                        'sort_order' => 1
                    ],
                    [
                        'name' => 'Medium Bus',
                        'capacity' => '35 Kursi',
                        'slug' => 'medium-bus-35-kursi',
                        'media_key' => 'armada_medium_bus',
                        'features' => json_encode(['AC Dingin', 'Reclining Seat', 'LCD TV', 'Bagasi']),
                        'description' => 'Bus sedang dengan kapasitas 35 kursi, nyaman untuk perjalanan menengah.',
                        'sort_order' => 2
                    ],
                    [
                        'name' => 'Mini Bus',
                        'capacity' => '25 Kursi',
                        'slug' => 'mini-bus-25-kursi',
                        'media_key' => 'armada_mini_bus',
                        'features' => json_encode(['AC Dingin', 'Reclining Seat', 'LCD TV', 'Compact']),
                        'description' => 'Bus kecil dengan kapasitas 25 kursi, praktis untuk grup kecil.',
                        'sort_order' => 3
                    ]
                ];
                
                $stmt = $conn->prepare("INSERT INTO armada (name, capacity, slug, media_key, features, description, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
                foreach ($defaultArmada as $armada) {
                    $stmt->execute([
                        $armada['name'],
                        $armada['capacity'],
                        $armada['slug'],
                        $armada['media_key'],
                        $armada['features'],
                        $armada['description'],
                        $armada['sort_order']
                    ]);
                }
                
                echo '<div class="alert alert-success">✅ Data default armada berhasil ditambahkan!</div>';
            } else {
                echo '<div class="alert alert-info">ℹ️ Data armada sudah ada (' . $check['count'] . ' armada)</div>';
            }
            
            echo '<br><a href="armada.php" class="btn btn-primary">Ke Halaman Kelola Armada</a>';
            
        } catch(PDOException $e) {
            echo '<div class="alert alert-danger">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>
    </div>
</body>
</html>

