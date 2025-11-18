<?php
/**
 * Media Helper Functions
 * Fungsi helper untuk mengambil gambar dari database
 */

/**
 * Mendapatkan media berdasarkan media_key (alias untuk getMedia)
 * 
 * @param string $media_key Key unik media
 * @return array|null Array dengan informasi media atau null
 */
function getMediaByKey($media_key) {
    return getMedia($media_key);
}

/**
 * Mendapatkan media berdasarkan media_key
 * 
 * @param string $media_key Key unik media
 * @param string $default_path Path default jika media tidak ditemukan
 * @return array|null Array dengan informasi media atau null
 */
function getMedia($media_key, $default_path = '') {
    global $conn;
    
    // Check if database connection exists
    if (!isset($conn)) {
        if (!empty($default_path)) {
            return [
                'file_path' => $default_path,
                'alt_text' => '',
                'title' => ''
            ];
        }
        return null;
    }
    
    try {
        $stmt = $conn->prepare("SELECT * FROM media WHERE media_key = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$media_key]);
        $media = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($media) {
            return $media;
        }
        
        // Jika tidak ditemukan, return default
        if (!empty($default_path)) {
            return [
                'file_path' => $default_path,
                'alt_text' => '',
                'title' => ''
            ];
        }
        
        return null;
    } catch(PDOException $e) {
        // Log error
        error_log("Error getting media: " . $e->getMessage());
        
        // Return default jika error
        if (!empty($default_path)) {
            return [
                'file_path' => $default_path,
                'alt_text' => '',
                'title' => ''
            ];
        }
        
        return null;
    }
}

/**
 * Mendapatkan URL gambar dari media_key
 * 
 * @param string $media_key Key unik media
 * @param string $default_path Path default jika media tidak ditemukan
 * @return string URL/path gambar
 */
function getMediaUrl($media_key, $default_path = '') {
    $media = getMedia($media_key, $default_path);
    
    if ($media) {
        // Jika ada file_url (CDN), gunakan itu
        if (!empty($media['file_url'])) {
            return $media['file_url'];
        }
        // Jika tidak, gunakan file_path
        return $media['file_path'];
    }
    
    return $default_path;
}

/**
 * Mendapatkan alt text dari media_key
 * 
 * @param string $media_key Key unik media
 * @param string $default_alt Alt text default jika media tidak ditemukan
 * @return string Alt text
 */
function getMediaAlt($media_key, $default_alt = '') {
    $media = getMedia($media_key);
    
    if ($media && !empty($media['alt_text'])) {
        return htmlspecialchars($media['alt_text'], ENT_QUOTES, 'UTF-8');
    }
    
    return $default_alt;
}

/**
 * Mendapatkan title dari media_key
 * 
 * @param string $media_key Key unik media
 * @param string $default_title Title default jika media tidak ditemukan
 * @return string Title
 */
function getMediaTitle($media_key, $default_title = '') {
    $media = getMedia($media_key);
    
    if ($media && !empty($media['title'])) {
        return htmlspecialchars($media['title'], ENT_QUOTES, 'UTF-8');
    }
    
    return $default_title;
}

/**
 * Mendapatkan semua media berdasarkan category
 * 
 * @param string $category Nama kategori (logo, hero, armada, gallery, icon)
 * @param int $limit Limit jumlah hasil
 * @return array Array of media
 */
function getMediaByCategory($category, $limit = 10) {
    global $conn;
    
    if (!isset($conn)) {
        return [];
    }
    
    try {
        $stmt = $conn->prepare("SELECT * FROM media WHERE category = ? AND is_active = 1 ORDER BY sort_order ASC, created_at DESC LIMIT ?");
        $stmt->execute([$category, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error getting media by category: " . $e->getMessage());
        return [];
    }
}

/**
 * Mendapatkan semua media aktif
 * 
 * @param int $limit Limit jumlah hasil
 * @return array Array of media
 */
function getAllMedia($limit = 100) {
    global $conn;
    
    if (!isset($conn)) {
        return [];
    }
    
    try {
        $stmt = $conn->prepare("SELECT * FROM media WHERE is_active = 1 ORDER BY category, sort_order ASC, created_at DESC LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error getting all media: " . $e->getMessage());
        return [];
    }
}

/**
 * Fungsi helper untuk generate tag img HTML
 * 
 * @param string $media_key Key unik media
 * @param string $default_path Path default
 * @param string $default_alt Alt text default
 * @param string $classes CSS classes tambahan
 * @param array $attributes Attributes tambahan (width, height, dll)
 * @return string HTML tag img
 */
function getMediaImage($media_key, $default_path = '', $default_alt = '', $classes = '', $attributes = []) {
    $media = getMedia($media_key, $default_path);
    
    if ($media) {
        $src = !empty($media['file_url']) ? $media['file_url'] : $media['file_path'];
        $alt = !empty($media['alt_text']) ? htmlspecialchars($media['alt_text'], ENT_QUOTES, 'UTF-8') : $default_alt;
        $title = !empty($media['title']) ? htmlspecialchars($media['title'], ENT_QUOTES, 'UTF-8') : '';
        $width = !empty($media['width']) ? $media['width'] : (isset($attributes['width']) ? $attributes['width'] : '');
        $height = !empty($media['height']) ? $media['height'] : (isset($attributes['height']) ? $attributes['height'] : '');
        
        $img = '<img src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '"';
        $img .= ' alt="' . $alt . '"';
        
        if (!empty($title)) {
            $img .= ' title="' . $title . '"';
        }
        
        if (!empty($classes)) {
            $img .= ' class="' . htmlspecialchars($classes, ENT_QUOTES, 'UTF-8') . '"';
        }
        
        if (!empty($width)) {
            $img .= ' width="' . $width . '"';
        }
        
        if (!empty($height)) {
            $img .= ' height="' . $height . '"';
        }
        
        // Tambahkan attributes lainnya
        foreach ($attributes as $key => $value) {
            if (!in_array($key, ['width', 'height', 'class', 'src', 'alt', 'title'])) {
                $img .= ' ' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . '="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"';
            }
        }
        
        $img .= '>';
        
        return $img;
    }
    
    // Fallback ke default
    if (!empty($default_path)) {
        $img = '<img src="' . htmlspecialchars($default_path, ENT_QUOTES, 'UTF-8') . '"';
        $img .= ' alt="' . htmlspecialchars($default_alt, ENT_QUOTES, 'UTF-8') . '"';
        if (!empty($classes)) {
            $img .= ' class="' . htmlspecialchars($classes, ENT_QUOTES, 'UTF-8') . '"';
        }
        foreach ($attributes as $key => $value) {
            $img .= ' ' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . '="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"';
        }
        $img .= '>';
        return $img;
    }
    
    return '';
}

/**
 * Upload file media
 * 
 * @param array $file $_FILES array
 * @param string $media_key Key unik media
 * @param string $category Kategori (logo, hero, armada, gallery, icon)
 * @param string $alt_text Alt text
 * @param string $title Title attribute
 * @param int $uploaded_by ID admin yang upload
 * @return array|false Array dengan informasi media atau false jika gagal
 */
function uploadMedia($file, $media_key, $category = 'gallery', $alt_text = '', $title = '', $uploaded_by = null) {
    global $conn;
    
    if (!isset($conn)) {
        return false;
    }
    
    // Validasi file
    if (!isset($file['error']) || is_array($file['error'])) {
        return false;
    }
    
    // Check error
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Validasi ukuran file (max 5MB)
    if ($file['size'] > 5242880) {
        return false;
    }
    
    // Validasi tipe file
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    // Get MIME type dengan beberapa metode fallback
    $mime_type = null;
    
    // Method 1: Try finfo (jika tersedia)
    if (class_exists('finfo')) {
        try {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime_type = $finfo->file($file['tmp_name']);
        } catch (Exception $e) {
            // Fallback jika error
        }
    }
    
    // Method 2: Try mime_content_type (jika finfo tidak tersedia)
    if (empty($mime_type) && function_exists('mime_content_type')) {
        $mime_type = mime_content_type($file['tmp_name']);
    }
    
    // Method 3: Try getimagesize (untuk gambar)
    if (empty($mime_type)) {
        $image_info = @getimagesize($file['tmp_name']);
        if ($image_info && isset($image_info['mime'])) {
            $mime_type = $image_info['mime'];
        }
    }
    
    // Method 4: Fallback ke validasi ekstensi file
    if (empty($mime_type)) {
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($file_extension, $allowed_extensions)) {
            return false;
        }
        // Set mime type berdasarkan ekstensi
        $mime_map = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp'
        ];
        $mime_type = $mime_map[$file_extension] ?? null;
    }
    
    // Final validation
    if (empty($mime_type) || !in_array($mime_type, $allowed_types)) {
        return false;
    }
    
    // Buat directory jika belum ada dengan error handling yang lebih baik
    $upload_dir = 'uploads/media/';
    // Path absolut dari root website (includes folder ada di satu level di bawah root)
    $upload_dir_abs = dirname(__DIR__) . '/' . $upload_dir;
    
    // Coba buat dengan path relatif dulu (untuk hosting yang menggunakan relative path)
    if (!file_exists($upload_dir) && !is_dir($upload_dir)) {
        // Cek dan buat folder parent terlebih dahulu
        $parent_dir = dirname($upload_dir);
        if (!file_exists($parent_dir) && !is_dir($parent_dir)) {
            @mkdir($parent_dir, 0755, true);
        }
        
        // Buat folder uploads/media dengan recursive
        $result = @mkdir($upload_dir, 0755, true);
        
        // Jika gagal dengan relative path, coba absolute path
        if (!$result && !file_exists($upload_dir)) {
            if (!file_exists($upload_dir_abs) && !is_dir($upload_dir_abs)) {
                $parent_abs = dirname($upload_dir_abs);
                if (!file_exists($parent_abs) && !is_dir($parent_abs)) {
                    @mkdir($parent_abs, 0755, true);
                }
                @mkdir($upload_dir_abs, 0755, true);
            }
        }
    }
    
    // Verifikasi folder bisa ditulis
    if (!is_dir($upload_dir) && !is_dir($upload_dir_abs)) {
        error_log("Failed to create upload directory: " . $upload_dir);
        return false;
    }
    
    // Pilih direktori yang berhasil dibuat
    $final_upload_dir = is_dir($upload_dir) ? $upload_dir : $upload_dir_abs;
    
    // Pastikan direktori writable - coba beberapa permission
    if (!is_writable($final_upload_dir)) {
        // Coba 0777 dulu (untuk shared hosting yang memerlukan permission lebih luas)
        @chmod($final_upload_dir, 0777);
        // Jika masih gagal, coba 0755
        if (!is_writable($final_upload_dir)) {
            @chmod($final_upload_dir, 0755);
        }
        // Jika masih gagal, coba 0775
        if (!is_writable($final_upload_dir)) {
            @chmod($final_upload_dir, 0775);
        }
        
        // Final check
        if (!is_writable($final_upload_dir)) {
            error_log("Directory still not writable after chmod attempts: " . $final_upload_dir);
            error_log("Current permission: " . substr(sprintf('%o', fileperms($final_upload_dir)), -4));
        }
    }
    
    // Pastikan folder parent juga writable
    $parent_dir = dirname($final_upload_dir);
    if (is_dir($parent_dir) && !is_writable($parent_dir)) {
        @chmod($parent_dir, 0755);
        if (!is_writable($parent_dir)) {
            @chmod($parent_dir, 0777);
        }
    }
    
    // Generate nama file unik
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $file_name = $media_key . '_' . time() . '_' . uniqid() . '.' . $file_extension;
    $file_path = rtrim($final_upload_dir, '/') . '/' . $file_name;
    
    // Debug: Log path yang akan digunakan
    error_log("Upload attempt - tmp_name: " . $file['tmp_name'] . ", target: " . $file_path);
    error_log("Upload directory exists: " . (is_dir($final_upload_dir) ? 'YES' : 'NO') . ", writable: " . (is_writable($final_upload_dir) ? 'YES' : 'NO'));
    
    // Pindahkan file dengan error handling
    if (!move_uploaded_file($file['tmp_name'], $file_path)) {
        $error = error_get_last();
        $error_msg = $error['message'] ?? 'Unknown error';
        error_log("Failed to move uploaded file: " . $error_msg);
        error_log("Source: " . $file['tmp_name'] . ", Destination: " . $file_path);
        error_log("Directory exists: " . (file_exists($final_upload_dir) ? 'YES' : 'NO'));
        error_log("Directory writable: " . (is_writable($final_upload_dir) ? 'YES' : 'NO'));
        
        // Hapus file temp jika gagal
        if (file_exists($file['tmp_name'])) {
            @unlink($file['tmp_name']);
        }
        return false;
    }
    
    // Verifikasi file berhasil dibuat
    if (!file_exists($file_path)) {
        error_log("Uploaded file does not exist after move: " . $file_path);
        error_log("Absolute path check: " . (file_exists($file_path) ? 'EXISTS' : 'NOT EXISTS'));
        return false;
    }
    
    error_log("Upload success - File saved to: " . $file_path);
    
    // Normalize path untuk database (gunakan relative path)
    $db_file_path = 'uploads/media/' . $file_name;
    
    // Get image dimensions
    $image_info = getimagesize($file_path);
    $width = $image_info[0] ?? null;
    $height = $image_info[1] ?? null;
    
    try {
        // Check if media_key already exists
        $stmt = $conn->prepare("SELECT id FROM media WHERE media_key = ?");
        $stmt->execute([$media_key]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            // Hapus file lama jika ada
            $old_media = getMedia($media_key);
            if ($old_media && !empty($old_media['file_path'])) {
                $old_file_path = $old_media['file_path'];
                // Coba relative path dulu
                if (file_exists($old_file_path)) {
                    @unlink($old_file_path);
                } else {
                    // Coba absolute path
                    $old_file_abs = __DIR__ . '/' . $old_file_path;
                    if (file_exists($old_file_abs)) {
                        @unlink($old_file_abs);
                    }
                }
            }
            
            // Update existing media
            $stmt = $conn->prepare("
                UPDATE media SET
                    category = ?,
                    file_name = ?,
                    file_path = ?,
                    mime_type = ?,
                    file_size = ?,
                    width = ?,
                    height = ?,
                    alt_text = ?,
                    title = ?,
                    uploaded_by = ?,
                    updated_at = NOW()
                WHERE media_key = ?
            ");
            $stmt->execute([
                $category,
                $file['name'],
                $db_file_path,
                $mime_type,
                $file['size'],
                $width,
                $height,
                $alt_text,
                $title,
                $uploaded_by,
                $media_key
            ]);
        } else {
            // Insert new media
            $stmt = $conn->prepare("
                INSERT INTO media (
                    media_key, file_name, file_path, file_type, mime_type,
                    file_size, width, height, alt_text, title, category, uploaded_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $media_key,
                $file['name'],
                $db_file_path,
                'image',
                $mime_type,
                $file['size'],
                $width,
                $height,
                $alt_text,
                $title,
                $category,
                $uploaded_by
            ]);
        }
        
        return getMedia($media_key);
    } catch(PDOException $e) {
        error_log("Error uploading media: " . $e->getMessage());
        // Delete uploaded file if database insert fails
        if (file_exists($file_path)) {
            @unlink($file_path);
        }
        return false;
    }
}

/**
 * Delete media
 * 
 * @param string $media_key Key unik media
 * @return bool True jika berhasil, false jika gagal
 */
function deleteMedia($media_key) {
    global $conn;
    
    if (!isset($conn)) {
        return false;
    }
    
    try {
        $media = getMedia($media_key);
        
        if ($media && !empty($media['file_path'])) {
            // Delete file - coba relative path dulu
            $media_path = $media['file_path'];
            if (file_exists($media_path)) {
                @unlink($media_path);
            } else {
                // Coba absolute path
                $media_path_abs = __DIR__ . '/' . $media_path;
                if (file_exists($media_path_abs)) {
                    @unlink($media_path_abs);
                }
            }
            
            // Delete from database
            $stmt = $conn->prepare("DELETE FROM media WHERE media_key = ?");
            $stmt->execute([$media_key]);
            
            return true;
        }
        
        return false;
    } catch(PDOException $e) {
        error_log("Error deleting media: " . $e->getMessage());
        return false;
    }
}

/**
 * Update media info (tanpa upload file baru)
 * 
 * @param string $media_key Key unik media
 * @param array $data Data yang akan diupdate (alt_text, title, category, is_active, sort_order)
 * @return bool True jika berhasil, false jika gagal
 */
function updateMediaInfo($media_key, $data) {
    global $conn;
    
    if (!isset($conn)) {
        return false;
    }
    
    try {
        $fields = [];
        $values = [];
        
        $allowed_fields = ['alt_text', 'title', 'category', 'is_active', 'sort_order', 'description'];
        
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $values[] = $media_key;
        
        $sql = "UPDATE media SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE media_key = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute($values);
        
        return true;
    } catch(PDOException $e) {
        error_log("Error updating media info: " . $e->getMessage());
        return false;
    }
}

