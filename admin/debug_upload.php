<?php
/**
 * Debug script untuk mengecek masalah upload
 */
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/auth_helper.php';
require_once __DIR__ . '/../includes/media_helper.php';

requireLogin();

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Debug Upload</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-5">
    <div class="container">
        <h2>Debug Upload Directory</h2>
        
        <div class="card mt-4">
            <div class="card-header">Info Directory</div>
            <div class="card-body">
                <?php
                $upload_dir = 'uploads/media/';
                // Path absolut dari root website (admin folder ada di satu level di bawah root)
                $upload_dir_abs = dirname(__DIR__) . '/uploads/media/';
                
                echo '<h5>Path Check:</h5>';
                echo '<ul>';
                echo '<li><strong>Relative Path:</strong> <code>' . $upload_dir . '</code></li>';
                echo '<li><strong>Absolute Path:</strong> <code>' . $upload_dir_abs . '</code></li>';
                echo '<li><strong>Current Script:</strong> <code>' . __FILE__ . '</code></li>';
                echo '<li><strong>Current Dir:</strong> <code>' . __DIR__ . '</code></li>';
                echo '<li><strong>Root Dir (config):</strong> <code>' . dirname(__DIR__) . '</code></li>';
                echo '</ul>';
                
                echo '<h5 class="mt-4">Directory Status:</h5>';
                
                // Check relative path
                echo '<p><strong>Relative Path (' . $upload_dir . '):</strong></p>';
                if (file_exists($upload_dir)) {
                    echo '<span class="badge bg-success">EXISTS</span> ';
                    echo '<span class="badge bg-' . (is_dir($upload_dir) ? 'success' : 'danger') . '">' . (is_dir($upload_dir) ? 'IS DIRECTORY' : 'NOT DIRECTORY') . '</span> ';
                    echo '<span class="badge bg-' . (is_readable($upload_dir) ? 'success' : 'danger') . '">' . (is_readable($upload_dir) ? 'READABLE' : 'NOT READABLE') . '</span> ';
                    echo '<span class="badge bg-' . (is_writable($upload_dir) ? 'success' : 'danger') . '">' . (is_writable($upload_dir) ? 'WRITABLE' : 'NOT WRITABLE') . '</span>';
                    
                    $perms = fileperms($upload_dir);
                    echo '<br><small class="text-muted">Permission: ' . substr(sprintf('%o', $perms), -4) . '</small>';
                } else {
                    echo '<span class="badge bg-danger">NOT EXISTS</span>';
                }
                
                // Check absolute path
                echo '<p class="mt-2"><strong>Absolute Path (' . $upload_dir_abs . '):</strong></p>';
                if (file_exists($upload_dir_abs)) {
                    echo '<span class="badge bg-success">EXISTS</span> ';
                    echo '<span class="badge bg-' . (is_dir($upload_dir_abs) ? 'success' : 'danger') . '">' . (is_dir($upload_dir_abs) ? 'IS DIRECTORY' : 'NOT DIRECTORY') . '</span> ';
                    echo '<span class="badge bg-' . (is_readable($upload_dir_abs) ? 'success' : 'danger') . '">' . (is_readable($upload_dir_abs) ? 'READABLE' : 'NOT READABLE') . '</span> ';
                    echo '<span class="badge bg-' . (is_writable($upload_dir_abs) ? 'success' : 'danger') . '">' . (is_writable($upload_dir_abs) ? 'WRITABLE' : 'NOT WRITABLE') . '</span>';
                    
                    $perms = fileperms($upload_dir_abs);
                    echo '<br><small class="text-muted">Permission: ' . substr(sprintf('%o', $perms), -4) . '</small>';
                } else {
                    echo '<span class="badge bg-danger">NOT EXISTS</span>';
                }
                
                // List files if directory exists
                $dir_to_check = file_exists($upload_dir_abs) ? $upload_dir_abs : (file_exists($upload_dir) ? $upload_dir : null);
                if ($dir_to_check) {
                    echo '<h5 class="mt-4">Files in Directory:</h5>';
                    $files = scandir($dir_to_check);
                    if ($files) {
                        $file_count = 0;
                        echo '<ul>';
                        foreach ($files as $file) {
                            if ($file != '.' && $file != '..') {
                                $file_count++;
                                $file_path = rtrim($dir_to_check, '/') . '/' . $file;
                                $file_size = filesize($file_path);
                                $file_perms = fileperms($file_path);
                                echo '<li>';
                                echo '<code>' . htmlspecialchars($file) . '</code> ';
                                echo '<small>(' . number_format($file_size) . ' bytes, ' . substr(sprintf('%o', $file_perms), -4) . ')</small>';
                                echo '</li>';
                            }
                        }
                        echo '</ul>';
                        if ($file_count == 0) {
                            echo '<p class="text-muted">Directory kosong (tidak ada file).</p>';
                        }
                    } else {
                        echo '<p class="text-danger">Gagal membaca directory!</p>';
                    }
                }
                
                // Try to create directory
                echo '<h5 class="mt-4">Create Directory Test:</h5>';
                $test_create = false;
                if (!file_exists($upload_dir_abs)) {
                    echo '<p>Mencoba membuat directory...</p>';
                    $result = @mkdir($upload_dir_abs, 0755, true);
                    if ($result) {
                        echo '<span class="badge bg-success">Berhasil membuat directory!</span>';
                        $test_create = true;
                    } else {
                        echo '<span class="badge bg-danger">Gagal membuat directory!</span>';
                        $error = error_get_last();
                        if ($error) {
                            echo '<br><small class="text-danger">Error: ' . htmlspecialchars($error['message']) . '</small>';
                        }
                    }
                } else {
                    echo '<p class="text-muted">Directory sudah ada.</p>';
                    $test_create = true;
                }
                
                // Try to write test file
                if ($test_create || file_exists($upload_dir_abs)) {
                    echo '<h5 class="mt-4">Write Test File:</h5>';
                    
                    // Coba fix permission dulu dengan berbagai cara
                    echo '<p>Mencoba fix permission folder...</p>';
                    $perms_to_try = [0777, 0775, 0755];
                    $fixed = false;
                    foreach ($perms_to_try as $perm) {
                        @chmod($upload_dir_abs, $perm);
                        if (is_writable($upload_dir_abs)) {
                            $fixed = true;
                            echo '<span class="badge bg-success">Permission berhasil diubah ke ' . substr(sprintf('%o', $perm), -4) . '</span><br>';
                            break;
                        }
                    }
                    
                    if (!$fixed) {
                        echo '<span class="badge bg-warning">Permission tidak bisa diubah dari PHP</span><br>';
                    }
                    
                    $perms_after = fileperms($upload_dir_abs);
                    echo '<p>Status saat ini:</p>';
                    echo '<ul>';
                    echo '<li>Permission: <code>' . substr(sprintf('%o', $perms_after), -4) . '</code></li>';
                    echo '<li>Writable: <span class="badge bg-' . (is_writable($upload_dir_abs) ? 'success' : 'danger') . '">' . (is_writable($upload_dir_abs) ? 'YES' : 'NO') . '</span></li>';
                    echo '<li>Readable: <span class="badge bg-' . (is_readable($upload_dir_abs) ? 'success' : 'danger') . '">' . (is_readable($upload_dir_abs) ? 'YES' : 'NO') . '</span></li>';
                    echo '</ul>';
                    
                    // Cek owner folder (jika function tersedia)
                    if (function_exists('posix_getpwuid') && function_exists('fileowner')) {
                        $owner = fileowner($upload_dir_abs);
                        $owner_info = posix_getpwuid($owner);
                        echo '<p>Owner: <code>' . ($owner_info['name'] ?? 'Unknown') . '</code> (UID: ' . $owner . ')</p>';
                    }
                    
                    $test_file = $upload_dir_abs . 'test_' . time() . '.txt';
                    $test_content = 'Test upload file - ' . date('Y-m-d H:i:s');
                    
                    echo '<p class="mt-3">Mencoba menulis test file ke: <br><code>' . htmlspecialchars($test_file) . '</code></p>';
                    
                    // Coba beberapa metode write
                    $write_result = false;
                    $write_method = '';
                    
                    // Method 1: file_put_contents
                    $write_result = @file_put_contents($test_file, $test_content);
                    if ($write_result !== false) {
                        $write_method = 'file_put_contents';
                    }
                    
                    // Method 2: fopen + fwrite
                    if (!$write_result) {
                        $fp = @fopen($test_file, 'w');
                        if ($fp) {
                            $write_result = @fwrite($fp, $test_content);
                            @fclose($fp);
                            if ($write_result !== false) {
                                $write_method = 'fopen+fwrite';
                            }
                        }
                    }
                    
                    // Method 3: touch + file_put_contents
                    if (!$write_result) {
                        if (@touch($test_file)) {
                            $write_result = @file_put_contents($test_file, $test_content);
                            if ($write_result !== false) {
                                $write_method = 'touch+file_put_contents';
                            }
                        }
                    }
                    
                    if ($write_result !== false) {
                        echo '<div class="alert alert-success mt-3">';
                        echo '<span class="badge bg-success">Berhasil menulis test file!</span><br>';
                        echo '<small>Method: <code>' . $write_method . '</code></small><br>';
                        echo '<small>File: <code>' . htmlspecialchars(basename($test_file)) . '</code> (' . $write_result . ' bytes)</small>';
                        
                        // Verify file exists
                        if (file_exists($test_file)) {
                            echo '<br><small class="text-success">✓ File verified exists</small>';
                        }
                        echo '</div>';
                        
                        // Try to delete test file
                        if (@unlink($test_file)) {
                            echo '<span class="badge bg-success">Berhasil menghapus test file!</span>';
                        } else {
                            echo '<span class="badge bg-warning">Test file masih ada (gagal delete, tidak masalah)</span>';
                            echo '<br><small>File: <code>' . htmlspecialchars($test_file) . '</code></small>';
                        }
                    } else {
                        echo '<div class="alert alert-danger mt-3">';
                        echo '<span class="badge bg-danger">Gagal menulis test file dengan semua metode!</span>';
                        $error = error_get_last();
                        if ($error) {
                            echo '<br><small class="text-danger"><strong>Error:</strong> ' . htmlspecialchars($error['message']) . '</small>';
                        }
                        
                        echo '<br><br><strong>Kemungkinan penyebab:</strong>';
                        echo '<ul>';
                        echo '<li>Folder tidak writable (permission tidak cukup atau owner salah)</li>';
                        echo '<li>Owner folder bukan user web server (www-data, apache, nginx, dll)</li>';
                        echo '<li>SELinux atau security policy memblokir write</li>';
                        echo '<li>Disk quota penuh</li>';
                        echo '<li>Folder diproteksi oleh hosting provider</li>';
                        echo '</ul>';
                        
                        echo '<br><strong>Solusi yang bisa dicoba:</strong>';
                        echo '<ol>';
                        echo '<li><strong>Via File Manager hosting:</strong><br>';
                        echo '  - Buka File Manager di cPanel/hosting panel<br>';
                        echo '  - Navigate ke folder <code>uploads/media/</code><br>';
                        echo '  - Right click → Change Permissions → Set ke <code>777</code> atau <code>755</code><br>';
                        echo '  - Atau ubah owner ke user web server</li>';
                        echo '<li><strong>Via SSH/Terminal (jika tersedia):</strong><br>';
                        echo '  <code>chmod -R 777 uploads/media/</code><br>';
                        echo '  <code>chown -R www-data:www-data uploads/media/</code> (atau user web server lainnya)</li>';
                        echo '<li><strong>Via cPanel File Manager:</strong><br>';
                        echo '  - Buka File Manager → uploads → media<br>';
                        echo '  - Klik "Change Permissions" → Centang semua (777)</li>';
                        echo '<li>Jika masih gagal, hubungi support hosting untuk set permission/owner yang benar</li>';
                        echo '</ol>';
                        echo '</div>';
                    }
                }
                
                // Check database records
                echo '<h5 class="mt-4">Database Check:</h5>';
                try {
                    $stmt = $conn->query("SELECT id, media_key, file_path, file_name, created_at FROM media ORDER BY created_at DESC LIMIT 10");
                    $media_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (empty($media_records)) {
                        echo '<p class="text-muted">Tidak ada record media di database.</p>';
                    } else {
                        echo '<p>Total: ' . count($media_records) . ' record(s)</p>';
                        echo '<table class="table table-sm">';
                        echo '<thead><tr><th>ID</th><th>Media Key</th><th>File Path</th><th>File Name</th><th>Exists?</th><th>Created</th></tr></thead>';
                        echo '<tbody>';
                        foreach ($media_records as $media) {
                            $file_exists_rel = file_exists($media['file_path']);
                            $file_exists_abs = file_exists(dirname(__DIR__) . '/' . $media['file_path']);
                            $file_exists_abs2 = file_exists(__DIR__ . '/../' . $media['file_path']);
                            $file_exists = $file_exists_rel || $file_exists_abs || $file_exists_abs2;
                            
                            echo '<tr>';
                            echo '<td>' . $media['id'] . '</td>';
                            echo '<td><code>' . htmlspecialchars($media['media_key']) . '</code></td>';
                            echo '<td><code>' . htmlspecialchars($media['file_path']) . '</code></td>';
                            echo '<td>' . htmlspecialchars($media['file_name']) . '</td>';
                            echo '<td>' . ($file_exists ? '<span class="badge bg-success">YES</span>' : '<span class="badge bg-danger">NO</span>') . '</td>';
                            echo '<td>' . date('Y-m-d H:i', strtotime($media['created_at'])) . '</td>';
                            echo '</tr>';
                        }
                        echo '</tbody></table>';
                    }
                } catch(PDOException $e) {
                    echo '<p class="text-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
                }
                ?>
            </div>
        </div>
        
        <div class="mt-4">
            <a href="armada.php" class="btn btn-primary">Kembali ke Armada</a>
            <a href="index.php" class="btn btn-secondary">Dashboard</a>
        </div>
    </div>
</body>
</html>

