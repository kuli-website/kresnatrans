-- ============================================
-- Database Schema untuk Sewa Bus Jogja
-- Tabel: testimonials (Rating & Testimoni)
-- ============================================

-- Buat database (jika belum ada)
-- CREATE DATABASE IF NOT EXISTS sewabus_jogja CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE sewabus_jogja;

-- ============================================
-- Tabel: testimonials
-- Menyimpan rating dan testimoni dari pelanggan
-- ============================================

CREATE TABLE IF NOT EXISTS `testimonials` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel untuk menyimpan rating dan testimoni pelanggan';

-- ============================================
-- Insert Data Sample (Opsional)
-- ============================================

-- INSERT INTO `testimonials` (`name`, `rating`, `testimonial`, `location`, `is_active`, `sort_order`) VALUES
-- ('Budi Santoso', 5, 'Pelayanan sangat memuaskan! Busnya nyaman, AC dingin, dan sopirnya ramah. Perjalanan dari Jogja ke Malang sangat lancar. Recommended!', 'Yogyakarta', 1, 1),
-- ('Siti Nurhaliza', 5, 'Armada busnya bagus dan terawat dengan baik. Fasilitas lengkap, ada toilet, LCD TV, dan reclining seat. Harga juga terjangkau. Puas sekali!', 'Surabaya', 1, 2),
-- ('Ahmad Hidayat', 4, 'Overall pengalaman baik. Bus tepat waktu, sopir profesional. Mungkin untuk kedepannya bisa ditambahkan WiFi. Tetap recommended!', 'Jakarta', 1, 3),
-- ('Dewi Sari', 5, 'Baru pertama kali sewa bus untuk acara kantor. Proses booking mudah, admin responsif. Bus sesuai foto dan deskripsi. Mantap!', 'Bandung', 1, 4),
-- ('Rudi Hartono', 5, 'Sewa untuk rombongan keluarga besar. Semua puas dengan pelayanannya. Harganya juga pas di kantong. Next time pasti sewa lagi!', 'Malang', 1, 5);

