<?php
session_start();
include '../config.php';
include '../includes/media_helper.php';

// Simple authentication (ganti dengan sistem auth yang lebih aman)
$is_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

if (!$is_logged_in) {
    // Redirect to login (buat halaman login terpisah)
    header('Location: login.php');
    exit;
}

$uploaded_by = $_SESSION['admin_id'] ?? null;
$message = '';
$message_type = '';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'upload') {
    if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
        $media_key = $_POST['media_key'] ?? '';
        $category = $_POST['category'] ?? 'gallery';
        $alt_text = $_POST['alt_text'] ?? '';
        $title = $_POST['title'] ?? '';
        
        if (!empty($media_key)) {
            $result = uploadMedia($_FILES['media_file'], $media_key, $category, $alt_text, $title, $uploaded_by);
            if ($result) {
                $message = 'Gambar berhasil diupload!';
                $message_type = 'success';
            } else {
                $message = 'Gagal upload gambar. Pastikan file valid dan tidak melebihi 5MB.';
                $message_type = 'error';
            }
        } else {
            $message = 'Media key tidak boleh kosong!';
            $message_type = 'error';
        }
    } else {
        $message = 'Tidak ada file yang diupload atau terjadi error!';
        $message_type = 'error';
    }
}

// Handle update info
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update') {
    $media_key = $_POST['media_key'] ?? '';
    $data = [
        'alt_text' => $_POST['alt_text'] ?? '',
        'title' => $_POST['title'] ?? '',
        'category' => $_POST['category'] ?? 'gallery',
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
        'sort_order' => intval($_POST['sort_order'] ?? 0)
    ];
    
    if (updateMediaInfo($media_key, $data)) {
        $message = 'Informasi gambar berhasil diupdate!';
        $message_type = 'success';
    } else {
        $message = 'Gagal update informasi gambar!';
        $message_type = 'error';
    }
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $media_key = $_POST['media_key'] ?? '';
    if (deleteMedia($media_key)) {
        $message = 'Gambar berhasil dihapus!';
        $message_type = 'success';
    } else {
        $message = 'Gagal menghapus gambar!';
        $message_type = 'error';
    }
}

// Get all media
$all_media = getAllMedia(100);
$media_by_category = [];
foreach ($all_media as $media) {
    $cat = $media['category'] ?? 'other';
    if (!isset($media_by_category[$cat])) {
        $media_by_category[$cat] = [];
    }
    $media_by_category[$cat][] = $media;
}

// Predefined media keys yang digunakan di website
$predefined_keys = [
    'logo_navbar' => ['name' => 'Logo Navbar', 'category' => 'logo', 'description' => 'Logo yang ditampilkan di navbar'],
    'hero_image' => ['name' => 'Hero Image', 'category' => 'hero', 'description' => 'Gambar utama di hero section'],
    'favicon' => ['name' => 'Favicon', 'category' => 'icon', 'description' => 'Icon untuk browser tab'],
    'og_image' => ['name' => 'Open Graph Image', 'category' => 'hero', 'description' => 'Gambar untuk social media sharing'],
    'bus_big' => ['name' => 'Big Bus Image', 'category' => 'armada', 'description' => 'Gambar untuk Big Bus 59 kursi'],
    'bus_medium' => ['name' => 'Medium Bus Image', 'category' => 'armada', 'description' => 'Gambar untuk Medium Bus 35 kursi'],
    'bus_mini' => ['name' => 'Mini Bus Image', 'category' => 'armada', 'description' => 'Gambar untuk Mini Bus 25 kursi'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media Manager - Sewa Bus Jogja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .media-preview {
            max-width: 200px;
            max-height: 150px;
            object-fit: cover;
            border-radius: 8px;
        }
        .media-card {
            transition: transform 0.2s;
        }
        .media-card:hover {
            transform: translateY(-5px);
        }
        .category-badge {
            font-size: 0.75rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">Media Manager</span>
            <a href="../index.php" class="btn btn-outline-light btn-sm" target="_blank">
                <i class="fas fa-external-link-alt me-1"></i>Lihat Website
            </a>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Upload Form -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-upload me-2"></i>Upload Gambar Baru</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="upload">
                            
                            <div class="mb-3">
                                <label class="form-label">Media Key <span class="text-danger">*</span></label>
                                <select name="media_key" class="form-select" required>
                                    <option value="">Pilih atau ketik baru</option>
                                    <?php foreach ($predefined_keys as $key => $info): ?>
                                        <option value="<?php echo htmlspecialchars($key); ?>">
                                            <?php echo htmlspecialchars($info['name'] . ' (' . $key . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="text" name="media_key_custom" class="form-control mt-2" placeholder="Atau ketik media key baru">
                                <small class="text-muted">Key unik untuk identifikasi gambar (contoh: logo_navbar, hero_image)</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Kategori</label>
                                <select name="category" class="form-select">
                                    <option value="logo">Logo</option>
                                    <option value="hero">Hero</option>
                                    <option value="armada">Armada</option>
                                    <option value="gallery">Gallery</option>
                                    <option value="icon">Icon</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">File Gambar <span class="text-danger">*</span></label>
                                <input type="file" name="media_file" class="form-control" accept="image/*" required>
                                <small class="text-muted">Format: JPG, PNG, GIF, WebP. Max: 5MB</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Alt Text (untuk SEO)</label>
                                <input type="text" name="alt_text" class="form-control" placeholder="Deskripsi gambar">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-control" placeholder="Title attribute">
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-upload me-2"></i>Upload Gambar
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Media List -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-images me-2"></i>Daftar Gambar</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($all_media)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>Belum ada gambar yang diupload.
                            </div>
                        <?php else: ?>
                            <div class="row g-3">
                                <?php foreach ($all_media as $media): ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card media-card h-100">
                                            <img src="../<?php echo htmlspecialchars($media['file_path']); ?>" 
                                                 class="card-img-top media-preview" 
                                                 alt="<?php echo htmlspecialchars($media['alt_text'] ?? ''); ?>">
                                            <div class="card-body">
                                                <h6 class="card-title">
                                                    <?php echo htmlspecialchars($media['media_key']); ?>
                                                    <span class="badge bg-<?php echo $media['is_active'] ? 'success' : 'secondary'; ?> category-badge ms-2">
                                                        <?php echo $media['is_active'] ? 'Aktif' : 'Nonaktif'; ?>
                                                    </span>
                                                </h6>
                                                <p class="card-text small text-muted mb-2">
                                                    <span class="badge bg-info category-badge"><?php echo htmlspecialchars($media['category'] ?? 'other'); ?></span>
                                                    <?php if ($media['width'] && $media['height']): ?>
                                                        <span class="ms-2"><?php echo $media['width']; ?>x<?php echo $media['height']; ?></span>
                                                    <?php endif; ?>
                                                </p>
                                                <p class="card-text small">
                                                    <strong>Alt:</strong> <?php echo htmlspecialchars($media['alt_text'] ?? '-'); ?><br>
                                                    <strong>File:</strong> <?php echo htmlspecialchars($media['file_name']); ?>
                                                </p>
                                                
                                                <div class="btn-group w-100" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editModal<?php echo $media['id']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="confirmDelete('<?php echo htmlspecialchars($media['media_key']); ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editModal<?php echo $media['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Gambar</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <input type="hidden" name="action" value="update">
                                                    <input type="hidden" name="media_key" value="<?php echo htmlspecialchars($media['media_key']); ?>">
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Media Key</label>
                                                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($media['media_key']); ?>" disabled>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Kategori</label>
                                                            <select name="category" class="form-select">
                                                                <option value="logo" <?php echo ($media['category'] ?? '') == 'logo' ? 'selected' : ''; ?>>Logo</option>
                                                                <option value="hero" <?php echo ($media['category'] ?? '') == 'hero' ? 'selected' : ''; ?>>Hero</option>
                                                                <option value="armada" <?php echo ($media['category'] ?? '') == 'armada' ? 'selected' : ''; ?>>Armada</option>
                                                                <option value="gallery" <?php echo ($media['category'] ?? '') == 'gallery' ? 'selected' : ''; ?>>Gallery</option>
                                                                <option value="icon" <?php echo ($media['category'] ?? '') == 'icon' ? 'selected' : ''; ?>>Icon</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Alt Text</label>
                                                            <input type="text" name="alt_text" class="form-control" value="<?php echo htmlspecialchars($media['alt_text'] ?? ''); ?>">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Title</label>
                                                            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($media['title'] ?? ''); ?>">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Sort Order</label>
                                                            <input type="number" name="sort_order" class="form-control" value="<?php echo $media['sort_order'] ?? 0; ?>">
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="is_active" id="active<?php echo $media['id']; ?>" 
                                                                   <?php echo ($media['is_active'] ?? 1) ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="active<?php echo $media['id']; ?>">
                                                                Aktif
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Form -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="media_key" id="deleteMediaKey">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(mediaKey) {
            if (confirm('Apakah Anda yakin ingin menghapus gambar ini? Tindakan ini tidak dapat dibatalkan.')) {
                document.getElementById('deleteMediaKey').value = mediaKey;
                document.getElementById('deleteForm').submit();
            }
        }

        // Auto-fill custom media key
        document.querySelector('select[name="media_key"]').addEventListener('change', function() {
            const customInput = document.querySelector('input[name="media_key_custom"]');
            if (this.value) {
                customInput.value = '';
            }
        });

        document.querySelector('input[name="media_key_custom"]').addEventListener('input', function() {
            const select = document.querySelector('select[name="media_key"]');
            if (this.value) {
                select.value = '';
            }
        });

        // Update form submission to use custom key if provided
        document.querySelector('form[method="POST"]').addEventListener('submit', function(e) {
            const select = document.querySelector('select[name="media_key"]');
            const customInput = document.querySelector('input[name="media_key_custom"]');
            
            if (!select.value && customInput.value) {
                // Create hidden input with custom key
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'media_key';
                hiddenInput.value = customInput.value;
                this.appendChild(hiddenInput);
                select.disabled = true;
            }
        });
    </script>
</body>
</html>

