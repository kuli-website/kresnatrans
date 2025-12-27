<?php
/**
 * Script untuk membuat tabel testimonials jika belum ada
 * Akses file ini sekali untuk membuat tabel
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
    <title>Create Testimonials Table</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-5">
    <div class="container">
        <h2>Create Testimonials Table</h2>
        <?php
        try {
            // Create testimonials table
            $sql = "CREATE TABLE IF NOT EXISTS `testimonials` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(255) NOT NULL COMMENT 'Nama pelanggan/reviewer',
              `rating` tinyint(1) NOT NULL DEFAULT 5 COMMENT 'Rating bintang (1-5)',
              `testimonial` text NOT NULL COMMENT 'Isi testimoni/komentar',
              `photo` varchar(500) DEFAULT NULL COMMENT 'Path foto pelanggan (opsional)',
              `location` varchar(255) DEFAULT NULL COMMENT 'Lokasi/asal pelanggan (opsional)',
              `company` varchar(255) DEFAULT NULL COMMENT 'Perusahaan/institusi (opsional)',
              `is_active` tinyint(1) DEFAULT 1 COMMENT 'Status aktif (1=aktif/tampil, 0=nonaktif)',
              `sort_order` int(11) DEFAULT 0 COMMENT 'Urutan tampil (angka lebih kecil = tampil lebih awal)',
              `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu testimoni dibuat',
              `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Waktu terakhir diupdate',
              PRIMARY KEY (`id`),
              KEY `idx_rating` (`rating`),
              KEY `idx_is_active` (`is_active`),
              KEY `idx_sort_order` (`sort_order`),
              KEY `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel untuk menyimpan rating dan testimoni pelanggan'";
            
            $conn->exec($sql);
            echo '<div class="alert alert-success">✅ Tabel testimonials berhasil dibuat!</div>';
            
            // Check if data exists
            $check = $conn->query("SELECT COUNT(*) as count FROM testimonials")->fetch(PDO::FETCH_ASSOC);
            if ($check['count'] == 0) {
                // Insert sample data
                $sampleTestimonials = [
                    [
                        'name' => 'Budi Santoso',
                        'rating' => 5,
                        'testimonial' => 'Pelayanan sangat memuaskan! Busnya nyaman, AC dingin, dan sopirnya ramah. Perjalanan dari Jogja ke Malang sangat lancar. Recommended!',
                        'location' => 'Yogyakarta',
                        'is_active' => 1,
                        'sort_order' => 1
                    ],
                    [
                        'name' => 'Siti Nurhaliza',
                        'rating' => 5,
                        'testimonial' => 'Armada busnya bagus dan terawat dengan baik. Fasilitas lengkap, ada toilet, LCD TV, dan reclining seat. Harga juga terjangkau. Puas sekali!',
                        'location' => 'Surabaya',
                        'is_active' => 1,
                        'sort_order' => 2
                    ],
                    [
                        'name' => 'Ahmad Hidayat',
                        'rating' => 4,
                        'testimonial' => 'Overall pengalaman baik. Bus tepat waktu, sopir profesional. Mungkin untuk kedepannya bisa ditambahkan WiFi. Tetap recommended!',
                        'location' => 'Jakarta',
                        'is_active' => 1,
                        'sort_order' => 3
                    ],
                    [
                        'name' => 'Dewi Sari',
                        'rating' => 5,
                        'testimonial' => 'Baru pertama kali sewa bus untuk acara kantor. Proses booking mudah, admin responsif. Bus sesuai foto dan deskripsi. Mantap!',
                        'location' => 'Bandung',
                        'is_active' => 1,
                        'sort_order' => 4
                    ],
                    [
                        'name' => 'Rudi Hartono',
                        'rating' => 5,
                        'testimonial' => 'Sewa untuk rombongan keluarga besar. Semua puas dengan pelayanannya. Harganya juga pas di kantong. Next time pasti sewa lagi!',
                        'location' => 'Malang',
                        'is_active' => 1,
                        'sort_order' => 5
                    ],
                    [
                        'name' => 'Linda Wijaya',
                        'rating' => 5,
                        'testimonial' => 'Sempurna! Bus bersih, nyaman, dan driver berpengalaman. Perjalanan pulang pergi Jogja-Bali aman dan nyaman. Terima kasih Kresna Trans!',
                        'location' => 'Yogyakarta',
                        'is_active' => 1,
                        'sort_order' => 6
                    ]
                ];
                
                $stmt = $conn->prepare("INSERT INTO testimonials (name, rating, testimonial, location, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
                foreach ($sampleTestimonials as $testimonial) {
                    $stmt->execute([
                        $testimonial['name'],
                        $testimonial['rating'],
                        $testimonial['testimonial'],
                        $testimonial['location'],
                        $testimonial['is_active'],
                        $testimonial['sort_order']
                    ]);
                }
                
                echo '<div class="alert alert-success">✅ Data sample testimonials berhasil ditambahkan! (' . count($sampleTestimonials) . ' testimoni)</div>';
            } else {
                echo '<div class="alert alert-info">ℹ️ Data testimonials sudah ada (' . $check['count'] . ' testimoni)</div>';
            }
            
            echo '<br><a href="../index.php" class="btn btn-primary me-2">Lihat Landing Page</a>';
            echo '<a href="testimonials.php" class="btn btn-secondary">Ke Halaman Kelola Testimonials</a>';
            
        } catch(PDOException $e) {
            echo '<div class="alert alert-danger">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>
    </div>
</body>
</html>

