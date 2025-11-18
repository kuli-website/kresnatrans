<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/auth_helper.php';
require_once __DIR__ . '/../includes/media_helper.php';

requireLogin();

$page_title = 'Kelola Armada Bus';
$message = '';
$message_type = ''; 

// Handle form actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action == 'create' || $action == 'update') {
            $id = intval($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $capacity = trim($_POST['capacity'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $features = $_POST['features'] ?? [];
            $features_json = json_encode(array_filter(array_map('trim', $features)));
            $sort_order = intval($_POST['sort_order'] ?? 0);
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $media_key = trim($_POST['media_key'] ?? '');
            
            // Handle image upload
            $image_path = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                if (empty($media_key)) {
                    $media_key = 'armada_' . strtolower(str_replace(' ', '_', $name)) . '_' . time();
                }
                
                // Debug log sebelum upload
                error_log("Starting upload - File: " . $_FILES['image']['name'] . ", Size: " . $_FILES['image']['size'] . ", Error: " . $_FILES['image']['error']);
                error_log("Media key: " . $media_key);
                
                $result = uploadMedia($_FILES['image'], $media_key, 'armada', $name . ' - ' . $capacity, $name, $_SESSION['admin_id']);
                
                // Debug log setelah upload
                error_log("Upload result: " . ($result ? 'SUCCESS' : 'FAILED'));
                if ($result) {
                    error_log("Upload result data: " . print_r($result, true));
                }
                
                if ($result && is_array($result) && !empty($result['file_path'])) {
                    // Gunakan file_path dari result uploadMedia
                    $image_path = $result['file_path'];
                    error_log("Using file_path from result: " . $image_path);
                } else {
                    // Fallback: coba ambil dari getMediaByKey
                    $media = getMediaByKey($media_key);
                    if ($media && !empty($media['file_path'])) {
                        $image_path = $media['file_path'];
                        error_log("Using file_path from getMediaByKey: " . $image_path);
                    }
                }
                
                // Log untuk debugging
                if (empty($image_path)) {
                    error_log("Warning: Upload berhasil tapi image_path kosong untuk media_key: " . $media_key);
                    $message = 'Gagal upload gambar! Silakan cek error log atau akses debug_upload.php untuk detail.';
                    $message_type = 'danger';
                } else {
                    error_log("Final image_path: " . $image_path);
                }
            } elseif (!empty($media_key)) {
                // Use existing media (jika tidak ada upload baru)
                $media = getMediaByKey($media_key);
                if ($media && !empty($media['file_path'])) {
                    $image_path = $media['file_path'];
                }
            }
            
            // Jika edit dan tidak ada upload baru, pertahankan image_path yang ada
            if ($action == 'update' && empty($image_path) && !empty($_POST['id'])) {
                $id = intval($_POST['id']);
                try {
                    $stmt = $conn->prepare("SELECT image_path FROM armada WHERE id = ?");
                    $stmt->execute([$id]);
                    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($existing && !empty($existing['image_path'])) {
                        $image_path = $existing['image_path'];
                    }
                } catch(PDOException $e) {
                    error_log("Error getting existing image_path: " . $e->getMessage());
                }
            }
            
            if ($action == 'create') {
                // Create slug if empty
                if (empty($slug)) {
                    $slug = strtolower(str_replace(' ', '-', $name)) . '-' . strtolower(str_replace(' ', '-', $capacity));
                    $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
                }
                
                // Check if slug exists
                $checkSlug = $conn->prepare("SELECT id FROM armada WHERE slug = ?");
                $checkSlug->execute([$slug]);
                if ($checkSlug->rowCount() > 0) {
                    $slug .= '-' . time();
                }
                
                $stmt = $conn->prepare("INSERT INTO armada (name, capacity, slug, image_path, media_key, features, description, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $capacity, $slug, $image_path, $media_key, $features_json, $description, $sort_order, $is_active]);
                $message = 'Armada berhasil ditambahkan!';
                $message_type = 'success';
            } else {
                // Update
                $stmt = $conn->prepare("UPDATE armada SET name = ?, capacity = ?, slug = ?, image_path = ?, media_key = ?, features = ?, description = ?, sort_order = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$name, $capacity, $slug, $image_path, $media_key, $features_json, $description, $sort_order, $is_active, $id]);
                $message = 'Armada berhasil diupdate!';
                $message_type = 'success';
            }
        } elseif ($action == 'delete') {
            $id = intval($_POST['id'] ?? 0);
            $stmt = $conn->prepare("DELETE FROM armada WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'Armada berhasil dihapus!';
            $message_type = 'success';
        }
    } catch(PDOException $e) {
        $message = 'Error: ' . $e->getMessage();
        $message_type = 'danger';
    }
}

// Get all armada
try {
    $armada_list = $conn->query("SELECT * FROM armada ORDER BY sort_order ASC, name ASC")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($armada_list as &$armada) {
        $armada['features'] = json_decode($armada['features'] ?? '[]', true) ?: [];
    }
} catch(PDOException $e) {
    $armada_list = [];
    if (strpos($e->getMessage(), "doesn't exist") !== false) {
        $message = 'Tabel armada belum dibuat. <a href="create_armada_table.php">Klik di sini untuk membuat tabel</a>';
        $message_type = 'warning';
    }
}

// Get edit data
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    try {
        $stmt = $conn->prepare("SELECT * FROM armada WHERE id = ?");
        $stmt->execute([$edit_id]);
        $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($edit_data) {
            $edit_data['features'] = json_decode($edit_data['features'] ?? '[]', true) ?: [];
        }
    } catch(PDOException $e) {
        // Error
    }
}

include __DIR__ . '/includes/header.php';
?>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>Kelola Armada Bus</h2>
            <p>Kelola foto dan informasi armada bus yang ditampilkan di website</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#armadaModal" onclick="resetForm()">
            <i class="fas fa-plus me-2"></i>Tambah Armada
        </button>
    </div>
</div>

<!-- Armada List -->
<div class="card">
    <div class="card-body">
        <?php if (empty($armada_list)): ?>
            <div class="text-center py-5">
                <i class="fas fa-bus fa-3x text-muted mb-3"></i>
                <p class="text-muted">Belum ada armada. Klik tombol "Tambah Armada" untuk menambahkan.</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($armada_list as $armada): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm" style="border: 1px solid #e5e7eb;">
                            <?php 
                            $image_src = null;
                            if (!empty($armada['image_path'])) {
                                // Cek file dengan path relatif dari admin folder
                                $rel_path = '../' . ltrim($armada['image_path'], '/');
                                $abs_path = __DIR__ . '/../' . ltrim($armada['image_path'], '/');
                                
                                if (file_exists($rel_path)) {
                                    $image_src = $rel_path;
                                } elseif (file_exists($abs_path)) {
                                    $image_src = $rel_path; // Tetap gunakan relative untuk display
                                } elseif (file_exists($armada['image_path'])) {
                                    $image_src = '../' . ltrim($armada['image_path'], '/');
                                }
                            }
                            // Jika tidak ada image_path tapi ada media_key, coba ambil dari media
                            if (!$image_src && !empty($armada['media_key'])) {
                                $media = getMediaByKey($armada['media_key']);
                                if ($media && !empty($media['file_path'])) {
                                    $rel_path = '../' . ltrim($media['file_path'], '/');
                                    if (file_exists($rel_path) || file_exists(__DIR__ . '/../' . ltrim($media['file_path'], '/'))) {
                                        $image_src = $rel_path;
                                    }
                                }
                            }
                            ?>
                            <?php if ($image_src): ?>
                                <img src="<?php echo htmlspecialchars($image_src); ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($armada['name']); ?>"
                                     style="height: 200px; object-fit: cover;"
                                     onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'400\' height=\'200\'%3E%3Crect fill=\'%23e5e7eb\' width=\'400\' height=\'200\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%23999\' font-family=\'sans-serif\' font-size=\'14\'%3EGambar tidak ditemukan%3C/text%3E%3C/svg%3E';">
                            <?php else: ?>
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="fas fa-bus fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title mb-0"><?php echo htmlspecialchars($armada['name']); ?></h5>
                                    <span class="badge bg-<?php echo $armada['is_active'] ? 'success' : 'secondary'; ?>">
                                        <?php echo $armada['is_active'] ? 'Aktif' : 'Nonaktif'; ?>
                                    </span>
                                </div>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-users me-1"></i>
                                    <?php echo htmlspecialchars($armada['capacity']); ?>
                                </p>
                                <?php if (!empty($armada['features'])): ?>
                                    <div class="mb-3">
                                        <small class="text-muted d-block mb-1">Fasilitas:</small>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php foreach (array_slice($armada['features'], 0, 4) as $feature): ?>
                                                <span class="badge bg-info"><?php echo htmlspecialchars($feature); ?></span>
                                            <?php endforeach; ?>
                                            <?php if (count($armada['features']) > 4): ?>
                                                <span class="badge bg-secondary">+<?php echo count($armada['features']) - 4; ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <div class="btn-group w-100">
                                    <a href="?edit=<?php echo $armada['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?php echo $armada['id']; ?>, '<?php echo htmlspecialchars($armada['name'], ENT_QUOTES); ?>')">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="armadaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <?php echo $edit_data ? 'Edit Armada' : 'Tambah Armada Baru'; ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data" id="armadaForm">
                <input type="hidden" name="action" value="<?php echo $edit_data ? 'update' : 'create'; ?>">
                <?php if ($edit_data): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                <?php endif; ?>
                
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Armada <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required
                                   value="<?php echo htmlspecialchars($edit_data['name'] ?? ''); ?>"
                                   placeholder="Contoh: Big Bus">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Kapasitas <span class="text-danger">*</span></label>
                            <input type="text" name="capacity" class="form-control" required
                                   value="<?php echo htmlspecialchars($edit_data['capacity'] ?? ''); ?>"
                                   placeholder="Contoh: 59 Kursi">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Slug (URL)</label>
                            <input type="text" name="slug" class="form-control"
                                   value="<?php echo htmlspecialchars($edit_data['slug'] ?? ''); ?>"
                                   placeholder="Akan dibuat otomatis jika kosong">
                            <small class="text-muted">Format: big-bus-59-kursi</small>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Urutan Tampil</label>
                            <input type="number" name="sort_order" class="form-control"
                                   value="<?php echo $edit_data['sort_order'] ?? 0; ?>" min="0">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Media Key</label>
                            <input type="text" name="media_key" class="form-control"
                                   value="<?php echo htmlspecialchars($edit_data['media_key'] ?? ''); ?>"
                                   placeholder="Contoh: armada_big_bus">
                            <small class="text-muted">Key untuk referensi media</small>
                        </div>
                        
                        <div class="col-md-12">
                            <label class="form-label">Gambar Armada</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <?php if ($edit_data && !empty($edit_data['image_path'])): 
                                $edit_img_src = null;
                                $rel_path = '../' . ltrim($edit_data['image_path'], '/');
                                $abs_path = __DIR__ . '/../' . ltrim($edit_data['image_path'], '/');
                                
                                if (file_exists($rel_path)) {
                                    $edit_img_src = $rel_path;
                                } elseif (file_exists($abs_path)) {
                                    $edit_img_src = $rel_path;
                                } elseif (file_exists($edit_data['image_path'])) {
                                    $edit_img_src = '../' . ltrim($edit_data['image_path'], '/');
                                }
                            ?>
                                <small class="text-muted d-block mt-1">
                                    Gambar saat ini: <code><?php echo htmlspecialchars($edit_data['image_path']); ?></code>
                                    <?php if ($edit_img_src): ?>
                                        <a href="<?php echo htmlspecialchars($edit_img_src); ?>" target="_blank" class="ms-2">Lihat</a>
                                    <?php else: ?>
                                        <span class="text-danger ms-2">(File tidak ditemukan)</span>
                                    <?php endif; ?>
                                </small>
                                <?php if ($edit_img_src): ?>
                                    <img src="<?php echo htmlspecialchars($edit_img_src); ?>" 
                                         class="img-thumbnail mt-2" 
                                         style="max-width: 200px; max-height: 150px;"
                                         onerror="this.style.display='none';">
                                <?php endif; ?>
                            <?php endif; ?>
                            <small class="text-muted d-block mt-1">Format: JPG, PNG, GIF, WebP. Max: 5MB</small>
                        </div>
                        
                        <div class="col-md-12">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="3"
                                      placeholder="Deskripsi armada..."><?php echo htmlspecialchars($edit_data['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="col-md-12">
                            <label class="form-label">Fasilitas</label>
                            <div id="featuresContainer">
                                <?php 
                                $features = $edit_data['features'] ?? [];
                                if (empty($features)) {
                                    $features = [''];
                                }
                                foreach ($features as $index => $feature): 
                                ?>
                                    <div class="input-group mb-2 feature-item">
                                        <input type="text" name="features[]" class="form-control" 
                                               value="<?php echo htmlspecialchars($feature); ?>"
                                               placeholder="Contoh: AC Dingin">
                                        <button type="button" class="btn btn-outline-danger" onclick="removeFeature(this)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addFeature()">
                                <i class="fas fa-plus me-1"></i>Tambah Fasilitas
                            </button>
                        </div>
                        
                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" 
                                       <?php echo ($edit_data['is_active'] ?? 1) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_active">
                                    Aktif (Tampilkan di website)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Form -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="deleteId">
</form>

<?php if ($edit_data): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = new bootstrap.Modal(document.getElementById('armadaModal'));
        modal.show();
    });
</script>
<?php endif; ?>

<script>
function resetForm() {
    document.getElementById('armadaForm').reset();
    document.querySelector('input[name="action"]').value = 'create';
    const featuresContainer = document.getElementById('featuresContainer');
    featuresContainer.innerHTML = '<div class="input-group mb-2 feature-item"><input type="text" name="features[]" class="form-control" placeholder="Contoh: AC Dingin"><button type="button" class="btn btn-outline-danger" onclick="removeFeature(this)"><i class="fas fa-times"></i></button></div>';
}

function addFeature() {
    const container = document.getElementById('featuresContainer');
    const newFeature = document.createElement('div');
    newFeature.className = 'input-group mb-2 feature-item';
    newFeature.innerHTML = '<input type="text" name="features[]" class="form-control" placeholder="Contoh: AC Dingin"><button type="button" class="btn btn-outline-danger" onclick="removeFeature(this)"><i class="fas fa-times"></i></button>';
    container.appendChild(newFeature);
}

function removeFeature(btn) {
    const featureItems = document.querySelectorAll('.feature-item');
    if (featureItems.length > 1) {
        btn.closest('.feature-item').remove();
    } else {
        btn.closest('.feature-item').querySelector('input').value = '';
    }
}

function confirmDelete(id, name) {
    if (confirm('Apakah Anda yakin ingin menghapus armada "' + name + '"? Tindakan ini tidak dapat dibatalkan.')) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteForm').submit();
    }
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

