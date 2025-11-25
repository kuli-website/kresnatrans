<?php
/**
 * Script untuk test upload folder dan permissions
 * Akses file ini untuk melihat status folder uploads/media
 */
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/auth_helper.php';

requireLogin();

$page_title = 'Test Upload Folder';
include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h2>Test Upload Folder & Permissions</h2>
    <p>Script untuk memeriksa folder uploads/media dan permissions</p>
</div>

<div class="card">
    <div class="card-body">
        <h5 class="card-title">Status Folder Upload</h5>
        <?php
        // Path dari root website
        $root_dir = dirname(__DIR__);
        $upload_dir_rel = 'uploads/media/';
        $upload_dir_abs = $root_dir . '/' . $upload_dir_rel;
        
        echo "<div class='mb-3'>";
        echo "<strong>Root Directory:</strong> " . htmlspecialchars($root_dir) . "<br>";
        echo "<strong>Relative Path:</strong> " . htmlspecialchars($upload_dir_rel) . "<br>";
        echo "<strong>Absolute Path:</strong> " . htmlspecialchars($upload_dir_abs) . "<br>";
        echo "</div>";
        
        // Cek relative path
        echo "<h6>Status Path Relatif (uploads/media/):</h6>";
        echo "<ul>";
        echo "<li>Exists: " . (file_exists($upload_dir_rel) ? '✅ YES' : '❌ NO') . "</li>";
        echo "<li>Is Directory: " . (is_dir($upload_dir_rel) ? '✅ YES' : '❌ NO') . "</li>";
        echo "<li>Readable: " . (is_readable($upload_dir_rel) ? '✅ YES' : '❌ NO') . "</li>";
        echo "<li>Writable: " . (is_writable($upload_dir_rel) ? '✅ YES' : '❌ NO') . "</li>";
        if (file_exists($upload_dir_rel)) {
            $perms = fileperms($upload_dir_rel);
            echo "<li>Permissions: " . substr(sprintf('%o', $perms), -4) . "</li>";
        }
        echo "</ul>";
        
        // Cek absolute path
        echo "<h6>Status Path Absolut:</h6>";
        echo "<ul>";
        echo "<li>Exists: " . (file_exists($upload_dir_abs) ? '✅ YES' : '❌ NO') . "</li>";
        echo "<li>Is Directory: " . (is_dir($upload_dir_abs) ? '✅ YES' : '❌ NO') . "</li>";
        echo "<li>Readable: " . (is_readable($upload_dir_abs) ? '✅ YES' : '❌ NO') . "</li>";
        echo "<li>Writable: " . (is_writable($upload_dir_abs) ? '✅ YES' : '❌ NO') . "</li>";
        if (file_exists($upload_dir_abs)) {
            $perms = fileperms($upload_dir_abs);
            echo "<li>Permissions: " . substr(sprintf('%o', $perms), -4) . "</li>";
        }
        echo "</ul>";
        
        // Coba buat folder
        echo "<h6>Aksi:</h6>";
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_folder'])) {
            // Coba buat dengan absolute path
            if (!is_dir($upload_dir_abs)) {
                $created = @mkdir($upload_dir_abs, 0777, true);
                if ($created) {
                    echo "<div class='alert alert-success'>✅ Folder berhasil dibuat dengan path absolut!</div>";
                } else {
                    echo "<div class='alert alert-danger'>❌ Gagal membuat folder dengan path absolut.</div>";
                    // Coba dengan relative path
                    if (!is_dir($upload_dir_rel)) {
                        $created = @mkdir($upload_dir_rel, 0777, true);
                        if ($created) {
                            echo "<div class='alert alert-success'>✅ Folder berhasil dibuat dengan path relatif!</div>";
                        } else {
                            echo "<div class='alert alert-danger'>❌ Gagal membuat folder dengan path relatif.</div>";
                        }
                    }
                }
            } else {
                echo "<div class='alert alert-info'>ℹ️ Folder sudah ada.</div>";
            }
            
            // Set permissions
            if (is_dir($upload_dir_abs)) {
                @chmod($upload_dir_abs, 0777);
                echo "<div class='alert alert-info'>📝 Permissions di-set ke 0777</div>";
            } elseif (is_dir($upload_dir_rel)) {
                @chmod($upload_dir_rel, 0777);
                echo "<div class='alert alert-info'>📝 Permissions di-set ke 0777</div>";
            }
        }
        
        // Form untuk create folder
        echo "<form method='POST'>";
        echo "<button type='submit' name='create_folder' class='btn btn-primary'>Buat Folder uploads/media/</button>";
        echo "</form>";
        ?>
        
        <hr>
        
        <h5 class="card-title mt-4">Test Upload File</h5>
        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['test_file'])) {
            if ($_FILES['test_file']['error'] === UPLOAD_ERR_OK) {
                $test_file_name = 'test_' . time() . '_' . basename($_FILES['test_file']['name']);
                $test_file_path_abs = $upload_dir_abs . '/' . $test_file_name;
                $test_file_path_rel = $upload_dir_rel . '/' . $test_file_name;
                
                // Pilih path yang bisa digunakan
                $final_upload_dir = is_dir($upload_dir_abs) ? $upload_dir_abs : (is_dir($upload_dir_rel) ? $upload_dir_rel : null);
                
                if ($final_upload_dir) {
                    $test_file_path = $final_upload_dir . '/' . $test_file_name;
                    
                    if (move_uploaded_file($_FILES['test_file']['tmp_name'], $test_file_path)) {
                        echo "<div class='alert alert-success'>✅ File berhasil di-upload!</div>";
                        echo "<p>File path: <code>" . htmlspecialchars($test_file_path) . "</code></p>";
                        if (file_exists($test_file_path)) {
                            echo "<p>File size: " . filesize($test_file_path) . " bytes</p>";
                        }
                    } else {
                        $error = error_get_last();
                        echo "<div class='alert alert-danger'>❌ Gagal upload file!</div>";
                        echo "<p>Error: " . htmlspecialchars($error['message'] ?? 'Unknown error') . "</p>";
                        echo "<p>Target: <code>" . htmlspecialchars($test_file_path) . "</code></p>";
                        echo "<p>Directory exists: " . (is_dir($final_upload_dir) ? 'YES' : 'NO') . "</p>";
                        echo "<p>Directory writable: " . (is_writable($final_upload_dir) ? 'YES' : 'NO') . "</p>";
                    }
                } else {
                    echo "<div class='alert alert-danger'>❌ Folder upload tidak ditemukan. Silakan buat folder terlebih dahulu.</div>";
                }
            } else {
                echo "<div class='alert alert-danger'>❌ Error upload: " . $_FILES['test_file']['error'] . "</div>";
            }
        }
        ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="test_file" class="form-label">Pilih File Test</label>
                <input type="file" class="form-control" id="test_file" name="test_file" accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary">Upload Test File</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

