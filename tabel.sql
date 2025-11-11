-- ============================================
-- Database Schema untuk Sewa Bus Jogja
-- Tabel untuk Form Kontak
-- ============================================

-- Buat database (jika belum ada)
-- CREATE DATABASE IF NOT EXISTS sewabus_jogja CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE sewabus_jogja;

-- ============================================
-- Tabel: messages
-- Menyimpan data dari form kontak
-- Sesuai dengan process.php
-- ============================================

CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'Nama lengkap pengirim',
  `email` varchar(255) NOT NULL COMMENT 'Email pengirim',
  `phone` varchar(20) NOT NULL COMMENT 'Nomor telepon/WhatsApp',
  `message` text NOT NULL COMMENT 'Pesan dari pengirim',
  `status` enum('new','read','replied','archived') DEFAULT 'new' COMMENT 'Status pesan: new=baru, read=sudah dibaca, replied=sudah dibalas, archived=diarsipkan',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu pesan dikirim',
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Waktu terakhir diupdate',
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel untuk menyimpan data form kontak';

-- ============================================
-- Tabel: contacts (Alternatif - jika ingin menggunakan nama ini)
-- Uncomment jika ingin menggunakan tabel contacts
-- ============================================

-- CREATE TABLE IF NOT EXISTS `contacts` (
--   `id` int(11) NOT NULL AUTO_INCREMENT,
--   `name` varchar(255) NOT NULL COMMENT 'Nama lengkap pengirim',
--   `email` varchar(255) NOT NULL COMMENT 'Email pengirim',
--   `phone` varchar(20) NOT NULL COMMENT 'Nomor telepon/WhatsApp',
--   `message` text NOT NULL COMMENT 'Pesan dari pengirim',
--   `status` enum('new','read','replied','archived') DEFAULT 'new' COMMENT 'Status pesan: new=baru, read=sudah dibaca, replied=sudah dibalas, archived=diarsipkan',
--   `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu pesan dikirim',
--   `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Waktu terakhir diupdate',
--   PRIMARY KEY (`id`),
--   KEY `idx_status` (`status`),
--   KEY `idx_created_at` (`created_at`),
--   KEY `idx_email` (`email`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel untuk menyimpan data form kontak';

-- ============================================
-- Tabel: bookings (Opsional - untuk booking)
-- ============================================

CREATE TABLE IF NOT EXISTS `bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contact_id` int(11) DEFAULT NULL COMMENT 'ID dari tabel messages (jika booking dari form kontak)',
  `name` varchar(255) NOT NULL COMMENT 'Nama pemesan',
  `email` varchar(255) NOT NULL COMMENT 'Email pemesan',
  `phone` varchar(20) NOT NULL COMMENT 'Nomor telepon/WhatsApp',
  `package_type` varchar(50) DEFAULT NULL COMMENT 'Jenis paket: city_tour, wisata, drop_off',
  `bus_type` varchar(50) DEFAULT NULL COMMENT 'Jenis bus: big_bus, medium_bus, mini_bus',
  `departure_date` date DEFAULT NULL COMMENT 'Tanggal keberangkatan',
  `return_date` date DEFAULT NULL COMMENT 'Tanggal kembali (jika ada)',
  `departure_location` varchar(255) DEFAULT NULL COMMENT 'Lokasi keberangkatan',
  `destination` varchar(255) DEFAULT NULL COMMENT 'Tujuan perjalanan',
  `passenger_count` int(11) DEFAULT NULL COMMENT 'Jumlah penumpang',
  `message` text DEFAULT NULL COMMENT 'Pesan tambahan',
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending' COMMENT 'Status booking',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu booking dibuat',
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Waktu terakhir diupdate',
  PRIMARY KEY (`id`),
  KEY `idx_contact_id` (`contact_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_bookings_contact` FOREIGN KEY (`contact_id`) REFERENCES `messages` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel untuk menyimpan data booking';

-- ============================================
-- Tabel: admin_users (Opsional - untuk admin panel)
-- ============================================

CREATE TABLE IF NOT EXISTS `admin_users` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel untuk admin users';

-- ============================================
-- Insert default admin user (opsional)
-- Password: admin123 (harus di-hash dengan password_hash)
-- ============================================

-- INSERT INTO `admin_users` (`username`, `email`, `password`, `full_name`, `role`) 
-- VALUES ('admin', 'admin@sewabusjogja.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'super_admin');

-- ============================================
-- Tabel: media_categories
-- Kategori untuk media/gambar
-- ============================================

CREATE TABLE IF NOT EXISTS `media_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL COMMENT 'Nama kategori',
  `category_slug` varchar(100) NOT NULL COMMENT 'Slug kategori (untuk URL)',
  `description` text DEFAULT NULL COMMENT 'Deskripsi kategori',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Status aktif (1=aktif, 0=nonaktif)',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_category_slug` (`category_slug`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel kategori media';

-- ============================================
-- Tabel: media
-- Menyimpan semua informasi gambar/media
-- ============================================

CREATE TABLE IF NOT EXISTS `media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT NULL COMMENT 'ID kategori media',
  `media_key` varchar(100) NOT NULL COMMENT 'Key unik untuk identifikasi media (contoh: logo_navbar, hero_image, bus_big)',
  `file_name` varchar(255) NOT NULL COMMENT 'Nama file asli',
  `file_path` varchar(500) NOT NULL COMMENT 'Path relatif file (contoh: uploads/media/logo.png)',
  `file_url` varchar(500) DEFAULT NULL COMMENT 'URL lengkap file (jika menggunakan CDN)',
  `file_type` varchar(50) DEFAULT NULL COMMENT 'Tipe file (image/png, image/jpeg, dll)',
  `file_size` int(11) DEFAULT NULL COMMENT 'Ukuran file dalam bytes',
  `width` int(11) DEFAULT NULL COMMENT 'Lebar gambar (pixel)',
  `height` int(11) DEFAULT NULL COMMENT 'Tinggi gambar (pixel)',
  `alt_text` varchar(255) DEFAULT NULL COMMENT 'Alt text untuk SEO dan accessibility',
  `title` varchar(255) DEFAULT NULL COMMENT 'Title attribute',
  `description` text DEFAULT NULL COMMENT 'Deskripsi gambar',
  `caption` varchar(500) DEFAULT NULL COMMENT 'Caption gambar',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Status aktif (1=aktif, 0=nonaktif)',
  `sort_order` int(11) DEFAULT 0 COMMENT 'Urutan tampil (untuk sorting)',
  `uploaded_by` int(11) DEFAULT NULL COMMENT 'ID admin yang upload',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu diupload',
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Waktu terakhir diupdate',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_media_key` (`media_key`),
  KEY `idx_category_id` (`category_id`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_sort_order` (`sort_order`),
  KEY `idx_uploaded_by` (`uploaded_by`),
  CONSTRAINT `fk_media_category` FOREIGN KEY (`category_id`) REFERENCES `media_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_media_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel untuk menyimpan semua media/gambar';

-- ============================================
-- Insert default media categories
-- ============================================

INSERT INTO `media_categories` (`category_name`, `category_slug`, `description`, `is_active`) VALUES
('Logo', 'logo', 'Logo website dan brand', 1),
('Hero Section', 'hero', 'Gambar untuk hero section', 1),
('Armada', 'armada', 'Gambar armada bus', 1),
('Gallery', 'gallery', 'Gambar gallery/testimonial', 1),
('Icon', 'icon', 'Icon dan favicon', 1),
('Background', 'background', 'Gambar background', 1),
('Other', 'other', 'Media lainnya', 1);

-- ============================================
-- Insert default media (sesuai gambar yang sudah ada)
-- Sesuaikan file_path dengan struktur folder yang digunakan
-- ============================================

-- INSERT INTO `media` (`category_id`, `media_key`, `file_name`, `file_path`, `file_type`, `alt_text`, `title`, `is_active`, `sort_order`) VALUES
-- (1, 'logo_navbar', 'logobus.png', 'img/logobus.png', 'image/png', 'Sewa Bus Jogja - Rental Bus Yogyakarta', 'Logo Sewa Bus Jogja', 1, 1),
-- (2, 'hero_image', 'bus1.png', 'img/bus1.png', 'image/png', 'Sewa Bus Jogja - Armada Bus Premium untuk Rental Bus Yogyakarta', 'Armada Bus Premium', 1, 1),
-- (3, 'armada_big_bus', 'bus1.png', 'img/bus1.png', 'image/png', 'Sewa Big Bus Jogja 59 Kursi - Rental Bus Besar Yogyakarta', 'Big Bus 59 Kursi', 1, 1),
-- (3, 'armada_medium_bus', 'bus1.png', 'img/bus1.png', 'image/png', 'Sewa Medium Bus Jogja 35 Kursi - Rental Bus Sedang Yogyakarta', 'Medium Bus 35 Kursi', 1, 2),
-- (3, 'armada_mini_bus', 'bus1.png', 'img/bus1.png', 'image/png', 'Sewa Mini Bus Jogja 25 Kursi - Rental Bus Kecil Yogyakarta', 'Mini Bus 25 Kursi', 1, 3),
-- (5, 'favicon', 'logobus.png', 'img/logobus.png', 'image/png', 'Favicon Sewa Bus Jogja', 'Favicon', 1, 1),
-- (2, 'og_image', 'bus1.png', 'img/bus1.png', 'image/png', 'Sewa Bus Jogja - OG Image', 'OG Image', 1, 2);

-- ============================================
-- Tabel: settings (Opsional - untuk pengaturan website)
-- ============================================

CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL COMMENT 'Key pengaturan',
  `setting_value` text DEFAULT NULL COMMENT 'Value pengaturan',
  `setting_type` varchar(50) DEFAULT 'text' COMMENT 'Tipe: text, number, boolean, json',
  `description` varchar(255) DEFAULT NULL COMMENT 'Deskripsi pengaturan',
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel untuk pengaturan website';

-- ============================================
-- Insert default settings (opsional)
-- ============================================

-- INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
-- ('site_name', 'Sewa Bus Jogja', 'text', 'Nama website'),
-- ('site_email', 'info@sewabusjogja.com', 'text', 'Email website'),
-- ('site_phone', '+62 812-3456-789', 'text', 'Nomor telepon'),
-- ('site_whatsapp', '+628123456789', 'text', 'Nomor WhatsApp'),
-- ('site_address', 'Jl. Malioboro No. 123, Yogyakarta 55271', 'text', 'Alamat'),
-- ('maintenance_mode', '0', 'boolean', 'Mode maintenance (0=tidak, 1=ya)');

-- ============================================
-- View: contacts_summary (Opsional)
-- ============================================

-- ============================================
-- View: messages_summary (Opsional)
-- ============================================

-- CREATE OR REPLACE VIEW `messages_summary` AS
-- SELECT 
--     COUNT(*) as total_messages,
--     SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_messages,
--     SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as read_messages,
--     SUM(CASE WHEN status = 'replied' THEN 1 ELSE 0 END) as replied_messages,
--     SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_messages
-- FROM messages;

-- ============================================
-- Stored Procedure: Get Recent Contacts (Opsional)
-- ============================================

-- ============================================
-- Stored Procedure: Get Recent Messages (Opsional)
-- ============================================

-- DELIMITER //
-- CREATE PROCEDURE `GetRecentMessages`(IN limit_count INT)
-- BEGIN
--     SELECT * FROM messages 
--     ORDER BY created_at DESC 
--     LIMIT limit_count;
-- END //
-- DELIMITER ;

-- ============================================
-- Indexes untuk optimasi query
-- ============================================

-- Indexes sudah ditambahkan di CREATE TABLE
-- Untuk query tambahan, bisa ditambahkan index sesuai kebutuhan

-- ============================================
-- Tabel: media (untuk mengelola semua gambar/asset)
-- ============================================

CREATE TABLE IF NOT EXISTS `media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `media_key` varchar(100) NOT NULL COMMENT 'Key unik untuk identifikasi gambar (contoh: logo_navbar, hero_image, bus_big)',
  `file_name` varchar(255) NOT NULL COMMENT 'Nama file gambar',
  `file_path` varchar(500) NOT NULL COMMENT 'Path relatif file (contoh: uploads/logo.png)',
  `file_url` varchar(500) DEFAULT NULL COMMENT 'URL lengkap file (jika menggunakan CDN)',
  `file_type` varchar(50) DEFAULT 'image' COMMENT 'Tipe file: image, document, video',
  `mime_type` varchar(100) DEFAULT NULL COMMENT 'MIME type file',
  `file_size` int(11) DEFAULT NULL COMMENT 'Ukuran file dalam bytes',
  `width` int(11) DEFAULT NULL COMMENT 'Lebar gambar (pixels)',
  `height` int(11) DEFAULT NULL COMMENT 'Tinggi gambar (pixels)',
  `alt_text` varchar(255) DEFAULT NULL COMMENT 'Alt text untuk SEO dan accessibility',
  `title` varchar(255) DEFAULT NULL COMMENT 'Title attribute',
  `category` varchar(50) DEFAULT NULL COMMENT 'Kategori: logo, hero, armada, gallery, icon',
  `description` text DEFAULT NULL COMMENT 'Deskripsi gambar',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Status aktif (1=aktif, 0=nonaktif)',
  `sort_order` int(11) DEFAULT 0 COMMENT 'Urutan tampil (untuk sorting)',
  `uploaded_by` int(11) DEFAULT NULL COMMENT 'ID admin yang upload',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu upload',
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Waktu terakhir diupdate',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_media_key` (`media_key`),
  KEY `idx_category` (`category`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_uploaded_by` (`uploaded_by`),
  CONSTRAINT `fk_media_admin` FOREIGN KEY (`uploaded_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel untuk menyimpan semua media/gambar website';

-- ============================================
-- Insert default media keys (untuk referensi)
-- Admin bisa upload gambar untuk key-key ini
-- ============================================

-- Contoh data (uncomment jika ingin insert default)
-- Media keys ini sesuai dengan yang digunakan di index.php
-- INSERT INTO `media` (`media_key`, `file_name`, `file_path`, `alt_text`, `title`, `category`, `is_active`) VALUES
-- ('logo_navbar', 'logobus.png', 'img/logobus.png', 'Sewa Bus Jogja - Rental Bus Yogyakarta', 'Logo Navbar', 'logo', 1),
-- ('hero_image', 'bus1.png', 'img/bus1.png', 'Sewa Bus Jogja - Armada Bus Premium untuk Rental Bus Yogyakarta', 'Hero Image', 'hero', 1),
-- ('favicon', 'logobus.png', 'img/logobus.png', 'Favicon Sewa Bus Jogja', 'Favicon', 'icon', 1),
-- ('og_image', 'bus1.png', 'img/bus1.png', 'Sewa Bus Jogja - Open Graph Image', 'OG Image', 'hero', 1),
-- ('armada_big_bus', 'bus1.png', 'img/bus1.png', 'Sewa Big Bus Jogja 59 Kursi - Rental Bus Besar Yogyakarta', 'Sewa Big Bus Jogja 59 Kursi untuk Wisata dan Dinas', 'armada', 1),
-- ('armada_medium_bus', 'bus1.png', 'img/bus1.png', 'Sewa Medium Bus Jogja 35 Kursi - Rental Bus Sedang Yogyakarta', 'Sewa Medium Bus Jogja 35 Kursi untuk Perjalanan Menengah', 'armada', 1),
-- ('armada_mini_bus', 'bus1.png', 'img/bus1.png', 'Sewa Mini Bus Jogja 25 Kursi - Rental Bus Kecil Yogyakarta', 'Sewa Mini Bus Jogja 25 Kursi untuk Grup Kecil', 'armada', 1);

-- ============================================
-- Tabel: media_gallery (Opsional - untuk gallery gambar)
-- ============================================

CREATE TABLE IF NOT EXISTS `media_gallery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `media_id` int(11) NOT NULL COMMENT 'ID dari tabel media',
  `gallery_name` varchar(100) DEFAULT NULL COMMENT 'Nama gallery (contoh: armada, testimoni, kegiatan)',
  `title` varchar(255) DEFAULT NULL COMMENT 'Judul gambar',
  `description` text DEFAULT NULL COMMENT 'Deskripsi gambar',
  `sort_order` int(11) DEFAULT 0 COMMENT 'Urutan tampil',
  `is_featured` tinyint(1) DEFAULT 0 COMMENT 'Featured image (1=ya, 0=tidak)',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_media_id` (`media_id`),
  KEY `idx_gallery_name` (`gallery_name`),
  KEY `idx_is_featured` (`is_featured`),
  CONSTRAINT `fk_gallery_media` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel untuk gallery gambar';

-- ============================================
-- View: active_media (untuk query media aktif saja)
-- ============================================

-- CREATE OR REPLACE VIEW `active_media` AS
-- SELECT * FROM media WHERE is_active = 1 ORDER BY category, sort_order, created_at DESC;

-- ============================================
-- Stored Procedure: Get Media by Key
-- ============================================

-- DELIMITER //
-- CREATE PROCEDURE `GetMediaByKey`(IN media_key_param VARCHAR(100))
-- BEGIN
--     SELECT * FROM media 
--     WHERE media_key = media_key_param AND is_active = 1 
--     LIMIT 1;
-- END //
-- DELIMITER ;

-- ============================================
-- Catatan:
-- 1. Pastikan database menggunakan charset utf8mb4 untuk support emoji
-- 2. Ganti password admin dengan hash yang aman sebelum digunakan
-- 3. Sesuaikan nama database sesuai kebutuhan
-- 4. Backup database secara berkala
-- 5. Buat folder uploads/ untuk menyimpan file yang di-upload
-- 6. Set permission folder uploads/ menjadi 755 atau 775
-- 7. Validasi file upload (type, size) di aplikasi
-- ============================================

