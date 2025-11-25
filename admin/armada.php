<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/auth_helper.php';

requireLogin();

$page_title = 'Kelola Armada Bus';
$message = '';
$message_type = '';

// Helper function sederhana untuk mendapatkan image src
function getArmadaImageSrc($image_path) {
    if (empty($image_path)) {
        return null;
    }
    
    // Normalize path
    $image_path = ltrim($image_path, '/');
    
    // Return relative path untuk display dari admin folder
    return '../' . $image_path;
} 

// Fungsi sederhana untuk upload gambar langsung ke folder
function uploadArmadaImage($file) {
    // Tentukan folder upload (absolute path)
    $root_dir = dirname(__DIR__);
    $upload_dir = $root_dir . '/uploads/media/';
    
    // Buat folder jika belum ada
    if (!is_dir($upload_dir)) {
        @mkdir($upload_dir, 0777, true);
    }
    
    // Cek error upload
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    // Validasi tipe file
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_extensions)) {
        return null;
    }
    
    // Validasi ukuran (max 5MB)
    if ($file['size'] > 5242880) {
        return null;
    }
    
    // Generate nama file unik
    $file_name = 'armada_' . time() . '_' . uniqid() . '.' . $file_extension;
    $file_path = $upload_dir . $file_name;
    
    // Pindahkan file
    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        // Return relative path untuk disimpan di database
        return 'uploads/media/' . $file_name;
    }
    
    return null;
}

// Handle form actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action == 'create' || $action == 'update') {
            // Debug: Log semua POST data
            error_log("=== ARMADA FORM SUBMIT DEBUG ===");
            error_log("Action: " . $action);
            error_log("POST data: " . print_r($_POST, true));
            error_log("POST id: " . ($_POST['id'] ?? 'NOT SET'));
            
            // PENTING: Ambil ID dari POST (bisa kosong untuk create)
            $id = isset($_POST['id']) && !empty($_POST['id']) ? intval($_POST['id']) : 0;
            
            error_log("POST id value: " . var_export($_POST['id'] ?? 'NOT SET', true));
            error_log("Parsed ID: " . $id);
            error_log("Action: " . $action);
            
            // Validasi berdasarkan action
            if ($action == 'update') {
                if (empty($id) || $id <= 0) {
                    $message = 'Error: ID tidak valid untuk update. Pastikan ID terkirim dari form.';
                    $message_type = 'danger';
                    error_log("ERROR: Update but no valid ID");
                    // Stop execution untuk update tanpa ID
                    $action = ''; // Prevent further processing
                }
            } elseif ($action == 'create') {
                // CREATE - pastikan ID kosong atau 0
                if (!empty($id) && $id > 0) {
                    error_log("WARNING: Create action but ID is set: " . $id . ". Ignoring ID.");
                    $id = 0; // Force ID to 0 for create
                }
            }
            
            error_log("Final ID to use after validation: " . $id);
            
            $name = trim($_POST['name'] ?? '');
            $capacity = trim($_POST['capacity'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $features = $_POST['features'] ?? [];
            $features_json = json_encode(array_filter(array_map('trim', $features)));
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            // Handle image upload - SIMPLE: langsung ke folder
            $image_path = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploaded_path = uploadArmadaImage($_FILES['image']);
                if ($uploaded_path) {
                    $image_path = $uploaded_path;
                } else {
                    $message = 'Gagal upload gambar! Pastikan file adalah gambar valid (JPG, PNG, GIF, WebP) dan ukuran maksimal 5MB.';
                    $message_type = 'danger';
                }
            } elseif ($action == 'update' && $id > 0) {
                // Jika edit dan tidak ada upload baru, pertahankan image_path yang ada
                try {
                    $stmt = $conn->prepare("SELECT image_path FROM armada WHERE id = ?");
                    $stmt->execute([$id]);
                    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($existing && !empty($existing['image_path'])) {
                        $image_path = $existing['image_path'];
                    }
                } catch(PDOException $e) {
                    // Ignore error
                }
            }
            
            // Generate slug otomatis (untuk kompatibilitas database)
            $slug = strtolower(str_replace(' ', '-', $name)) . '-' . strtolower(str_replace(' ', '-', $capacity));
            $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
            
            if ($action == 'create') {
                // CREATE - Pastikan tidak ada ID
                if (!empty($_POST['id'])) {
                    // Jika ada ID, ini seharusnya update, bukan create
                    $message = 'Error: Form dalam mode create tidak boleh memiliki ID.';
                    $message_type = 'danger';
                } else {
                    // Check if slug exists
                    $checkSlug = $conn->prepare("SELECT id FROM armada WHERE slug = ?");
                    $checkSlug->execute([$slug]);
                    if ($checkSlug->rowCount() > 0) {
                        $slug .= '-' . time();
                    }
                    
                    // Insert - sederhana: hanya kolom penting
                    try {
                        $stmt = $conn->prepare("INSERT INTO armada (name, capacity, slug, image_path, features, description, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, 0, ?)");
                        $stmt->execute([$name, $capacity, $slug, $image_path, $features_json, $description, $is_active]);
                        
                        $message = !empty($image_path) ? 'Armada berhasil ditambahkan!' : 'Armada berhasil ditambahkan! (Belum ada gambar)';
                        $message_type = 'success';
                        
                        // Redirect untuk refresh dan reset form
                        header("Location: armada.php?success=1");
                        exit;
                    } catch(PDOException $e) {
                        $message = 'Error: Gagal menyimpan data armada. ' . $e->getMessage();
                        $message_type = 'danger';
                    }
                }
            } elseif ($action == 'update') {
                // UPDATE - CRITICAL: Hanya update jika action benar-benar 'update' dan ID valid
                if (empty($id) || $id <= 0 || !is_numeric($id)) {
                    error_log("FATAL: UPDATE attempted without valid ID. ID = " . var_export($id, true));
                    error_log("POST id: " . var_export($_POST['id'] ?? 'NOT SET', true));
                    $message = 'Error: ID tidak valid untuk update. Tidak dapat melanjutkan.';
                    $message_type = 'danger';
                } else {
                    // Convert ID ke integer untuk memastikan
                    $id = intval($id);
                    
                    try {
                        // Verifikasi ID ada di database - hanya 1 record yang harus ditemukan
                        $checkId = $conn->prepare("SELECT id FROM armada WHERE id = ? LIMIT 1");
                        $checkId->execute([$id]);
                        $existing_record = $checkId->fetch(PDO::FETCH_ASSOC);
                        
                        if (!$existing_record || $existing_record['id'] != $id) {
                            error_log("ERROR: Record with ID $id not found in database");
                            $message = 'Error: Armada dengan ID ' . $id . ' tidak ditemukan.';
                            $message_type = 'danger';
                        } else {
                            // ID valid dan ada - lanjutkan update HANYA untuk ID ini
                            error_log("=== UPDATE ARMADA ===");
                            error_log("Target ID: " . $id);
                            error_log("Name: $name");
                            error_log("Capacity: $capacity");
                            error_log("Image path: " . ($image_path ?? 'NULL'));
                            
                            // Build UPDATE query - PASTIKAN ID DI AKHIR
                            $id_param = intval($id); // Pastikan integer
                            
                            if (!empty($image_path)) {
                                $update_sql = "UPDATE armada SET name = ?, capacity = ?, slug = ?, image_path = ?, features = ?, description = ?, is_active = ? WHERE id = ?";
                                $update_params = [$name, $capacity, $slug, $image_path, $features_json, $description, $is_active, $id_param];
                            } else {
                                // Tidak update image_path - ambil dari database
                                $update_sql = "UPDATE armada SET name = ?, capacity = ?, slug = ?, features = ?, description = ?, is_active = ? WHERE id = ?";
                                $update_params = [$name, $capacity, $slug, $features_json, $description, $is_active, $id_param];
                            }
                            
                            error_log("SQL: " . $update_sql);
                            error_log("Params: " . print_r($update_params, true));
                            error_log("ID param (last): " . end($update_params));
                            
                            // Final check: Pastikan ID di params valid
                            if (end($update_params) != $id || end($update_params) <= 0) {
                                error_log("FATAL: ID mismatch! Expected: $id, Got: " . end($update_params));
                                $message = 'Error: ID tidak sesuai.';
                                $message_type = 'danger';
                            } else {
                                // Execute UPDATE
                                $stmt = $conn->prepare($update_sql);
                                $stmt->execute($update_params);
                                
                                $rows_affected = $stmt->rowCount();
                                error_log("Rows affected: " . $rows_affected);
                                
                                if ($rows_affected > 1) {
                                    error_log("CRITICAL ERROR: " . $rows_affected . " rows updated instead of 1!");
                                    $message = 'Error: Lebih dari 1 record ter-update! Silakan hubungi administrator.';
                                    $message_type = 'danger';
                                } else {
                                    $message = 'Armada berhasil diupdate!';
                                    $message_type = 'success';
                                    header("Location: armada.php?success=1");
                                    exit;
                                }
                            }
                        }
                    } catch(PDOException $e) {
                        error_log("UPDATE ERROR: " . $e->getMessage());
                        $message = 'Error: Gagal mengupdate data armada. ' . $e->getMessage();
                        $message_type = 'danger';
                    }
                }
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

// Handle success message from redirect
if (isset($_GET['success'])) {
    $message = 'Operasi berhasil!';
    $message_type = 'success';
}

// Reset edit_data jika tidak ada parameter edit (penting untuk mencegah form masih dalam mode edit)
$edit_data = null;

// Get edit data only if edit parameter exists
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    try {
        $stmt = $conn->prepare("SELECT * FROM armada WHERE id = ?");
        $stmt->execute([$edit_id]);
        $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($edit_data) {
            $edit_data['features'] = json_decode($edit_data['features'] ?? '[]', true) ?: [];
        } else {
            // Jika ID tidak ditemukan, redirect
            header("Location: armada.php?error=not_found");
            exit;
        }
    } catch(PDOException $e) {
        // Error - redirect
        header("Location: armada.php?error=database");
        exit;
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
                            $image_src = getArmadaImageSrc($armada['image_path'] ?? '');
                            ?>
                            <?php if ($image_src): ?>
                                <img src="<?php echo htmlspecialchars($image_src); ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($armada['name']); ?>"
                                     style="height: 200px; object-fit: cover; width: 100%;"
                                     onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'card-img-top bg-light d-flex flex-column align-items-center justify-content-center text-center\' style=\'height: 200px; padding: 10px;\'><i class=\'fas fa-bus fa-3x text-muted mb-2\'></i><small class=\'text-danger\' style=\'font-size: 0.7rem;\'>Gambar tidak ditemukan<br>Path: <?php echo htmlspecialchars($image_src ?? $armada['image_path'] ?? 'N/A'); ?></small></div>';">
                            <?php else: ?>
                                <div class="card-img-top bg-light d-flex flex-column align-items-center justify-content-center text-center" style="height: 200px; padding: 10px;">
                                    <i class="fas fa-bus fa-3x text-muted mb-2"></i>
                                    <small class="text-muted">Tidak ada gambar</small>
                                    <?php if (!empty($armada['image_path']) || !empty($armada['media_key'])): ?>
                                        <small class="text-danger mt-1" style="font-size: 0.65rem;">
                                            Path: <?php echo htmlspecialchars($armada['image_path'] ?? $armada['media_key'] ?? 'N/A'); ?>
                                        </small>
                                    <?php endif; ?>
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
                                    <a href="?edit=<?php echo $armada['id']; ?>" class="btn btn-sm btn-outline-primary" data-edit-id="<?php echo $armada['id']; ?>">
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
            <form method="POST" enctype="multipart/form-data" id="armadaForm" onsubmit="return validateForm(event)">
                <input type="hidden" name="action" id="formAction" value="<?php echo $edit_data ? 'update' : 'create'; ?>">
                <input type="hidden" name="id" id="formId" value="<?php echo $edit_data ? intval($edit_data['id']) : ''; ?>">
                
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
                        
                        <div class="col-md-12">
                            <label class="form-label">Gambar Armada</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <?php if ($edit_data && !empty($edit_data['image_path'])): 
                                $edit_img_src = '../' . ltrim($edit_data['image_path'], '/');
                            ?>
                                <small class="text-muted d-block mt-2">
                                    Gambar saat ini:
                                </small>
                                <img src="<?php echo htmlspecialchars($edit_img_src); ?>" 
                                     class="img-thumbnail mt-2" 
                                     style="max-width: 200px; max-height: 150px; object-fit: cover;"
                                     onerror="this.style.display='none';">
                                <small class="text-muted d-block mt-1">
                                    Upload file baru untuk mengganti gambar
                                </small>
                            <?php else: ?>
                                <small class="text-muted d-block mt-1">Format: JPG, PNG, GIF, WebP. Maksimal 5MB</small>
                            <?php endif; ?>
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

<script>
    // Event listener untuk reset form saat modal dibuka untuk tambah baru
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('armadaModal');
        if (modal) {
            modal.addEventListener('show.bs.modal', function(event) {
                // Jika modal dibuka dari button "Tambah Armada" (bukan dari edit)
                const button = event.relatedTarget;
                // Cek apakah ini button tambah baru atau link edit
                const isEditLink = window.location.search.includes('edit=');
                if (!isEditLink && (!button || !button.getAttribute('data-edit-id'))) {
                    // Hanya reset jika benar-benar tambah baru
                    resetForm();
                }
            });
            
            // JANGAN reset form saat modal ditutup karena bisa mengganggu edit
            // Form akan di-reset saat redirect setelah submit
        }
        
        // Auto-show modal jika ada edit_data (dari URL parameter edit=)
        <?php if ($edit_data): ?>
        try {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const modalElement = document.getElementById('armadaModal');
                if (modalElement) {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                }
            }
        } catch (e) {
            console.error('Error showing modal:', e);
        }
        <?php endif; ?>
    });
</script>

<script>
function validateForm(event) {
    const action = document.getElementById('formAction')?.value;
    const id = document.getElementById('formId')?.value;
    
    console.log('Form submit - Action:', action, 'ID:', id);
    
    // Jika update, pastikan ID ada
    if (action === 'update') {
        if (!id || id === '' || id === '0') {
            alert('Error: ID tidak ditemukan. Silakan refresh halaman dan coba lagi.');
            event.preventDefault();
            return false;
        }
    }
    
    // Jika create, pastikan ID kosong
    if (action === 'create') {
        if (id && id !== '' && id !== '0') {
            console.warn('Warning: Create action but ID is set:', id);
            // Clear ID untuk create
            document.getElementById('formId').value = '';
        }
    }
    
    return true;
}

function resetForm() {
    try {
        const form = document.getElementById('armadaForm');
        if (form) {
            // Reset semua field
            form.reset();
            
            // Pastikan action = create
            const actionInput = document.getElementById('formAction');
            if (actionInput) {
                actionInput.value = 'create';
            }
            
            // PENTING: HAPUS atau kosongkan hidden input id
            const idInput = document.getElementById('formId');
            if (idInput) {
                idInput.value = '';
                idInput.removeAttribute('value'); // Pastikan benar-benar kosong
            }
            
            // Reset semua text inputs ke empty
            const textInputs = form.querySelectorAll('input[type="text"], textarea');
            textInputs.forEach(input => {
                if (input.name !== 'action' && input.name !== 'id') {
                    input.value = '';
                }
            });
            
            // Reset checkbox
            const checkbox = form.querySelector('input[type="checkbox"][name="is_active"]');
            if (checkbox) {
                checkbox.checked = true; // Default aktif
            }
            
            // Reset features container
            const featuresContainer = document.getElementById('featuresContainer');
            if (featuresContainer) {
                featuresContainer.innerHTML = '<div class="input-group mb-2 feature-item"><input type="text" name="features[]" class="form-control" placeholder="Contoh: AC Dingin"><button type="button" class="btn btn-outline-danger" onclick="removeFeature(this)"><i class="fas fa-times"></i></button></div>';
            }
            
            // Reset file input
            const fileInput = form.querySelector('input[type="file"]');
            if (fileInput) {
                fileInput.value = '';
            }
            
            // Hapus preview gambar jika ada
            const imgPreview = form.querySelector('.img-thumbnail');
            if (imgPreview) {
                imgPreview.style.display = 'none';
            }
            
            // Update modal title
            const modalTitle = document.querySelector('#armadaModal .modal-title');
            if (modalTitle) {
                modalTitle.textContent = 'Tambah Armada Baru';
            }
            
            // Log untuk debug
            console.log('Form reset - ID cleared:', document.getElementById('formId')?.value);
        }
    } catch (e) {
        console.error('Error resetting form:', e);
    }
}

function addFeature() {
    try {
        const container = document.getElementById('featuresContainer');
        if (container) {
            const newFeature = document.createElement('div');
            newFeature.className = 'input-group mb-2 feature-item';
            newFeature.innerHTML = '<input type="text" name="features[]" class="form-control" placeholder="Contoh: AC Dingin"><button type="button" class="btn btn-outline-danger" onclick="removeFeature(this)"><i class="fas fa-times"></i></button>';
            container.appendChild(newFeature);
        }
    } catch (e) {
        console.error('Error adding feature:', e);
    }
}

function removeFeature(btn) {
    try {
        const featureItems = document.querySelectorAll('.feature-item');
        if (featureItems.length > 1) {
            const featureItem = btn.closest('.feature-item');
            if (featureItem) {
                featureItem.remove();
            }
        } else {
            const featureItem = btn.closest('.feature-item');
            if (featureItem) {
                const input = featureItem.querySelector('input');
                if (input) {
                    input.value = '';
                }
            }
        }
    } catch (e) {
        console.error('Error removing feature:', e);
    }
}

function confirmDelete(id, name) {
    try {
        if (confirm('Apakah Anda yakin ingin menghapus armada "' + name + '"? Tindakan ini tidak dapat dibatalkan.')) {
            const deleteIdInput = document.getElementById('deleteId');
            if (deleteIdInput) {
                deleteIdInput.value = id;
            }
            const deleteForm = document.getElementById('deleteForm');
            if (deleteForm) {
                deleteForm.submit();
            }
        }
    } catch (e) {
        console.error('Error deleting armada:', e);
        alert('Terjadi kesalahan saat menghapus armada. Silakan coba lagi.');
    }
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

