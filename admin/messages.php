<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/auth_helper.php';

requireLogin();

$page_title = 'Kelola Pesan';
$message = '';
$message_type = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $message_id = intval($_POST['message_id'] ?? 0);
    
    try {
        if ($action == 'update_status') {
            $status = $_POST['status'] ?? 'new';
            $stmt = $conn->prepare("UPDATE messages SET status = ? WHERE id = ?");
            $stmt->execute([$status, $message_id]);
            $message = 'Status pesan berhasil diupdate!';
            $message_type = 'success';
        } elseif ($action == 'delete') {
            $stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
            $stmt->execute([$message_id]);
            $message = 'Pesan berhasil dihapus!';
            $message_type = 'success';
        }
    } catch(PDOException $e) {
        $message = 'Terjadi kesalahan: ' . $e->getMessage();
        $message_type = 'danger';
    }
}

// Get filter
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query
$where = [];
$params = [];

if ($filter != 'all') {
    $where[] = "status = ?";
    $params[] = $filter;
}

if (!empty($search)) {
    $where[] = "(name LIKE ? OR email LIKE ? OR message LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Get messages
$sql = "SELECT * FROM messages $whereClause ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$stats = [
    'all' => $conn->query("SELECT COUNT(*) as count FROM messages")->fetch(PDO::FETCH_ASSOC)['count'],
    'new' => $conn->query("SELECT COUNT(*) as count FROM messages WHERE status = 'new'")->fetch(PDO::FETCH_ASSOC)['count'],
    'read' => $conn->query("SELECT COUNT(*) as count FROM messages WHERE status = 'read'")->fetch(PDO::FETCH_ASSOC)['count'],
    'replied' => $conn->query("SELECT COUNT(*) as count FROM messages WHERE status = 'replied'")->fetch(PDO::FETCH_ASSOC)['count'],
];

include __DIR__ . '/includes/header.php';
?>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">Kelola Pesan</h2>
    </div>
    <div class="col-md-6 text-end">
        <form method="GET" class="d-inline-flex gap-2">
            <input type="text" name="search" class="form-control" placeholder="Cari pesan..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-outline-primary">
                <i class="fas fa-search"></i>
            </button>
            <?php if ($search): ?>
                <a href="messages.php" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i>
                </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Filter Tabs -->
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link <?php echo $filter == 'all' ? 'active' : ''; ?>" href="?filter=all">
            Semua (<?php echo $stats['all']; ?>)
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $filter == 'new' ? 'active' : ''; ?>" href="?filter=new">
            Baru (<?php echo $stats['new']; ?>)
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $filter == 'read' ? 'active' : ''; ?>" href="?filter=read">
            Dibaca (<?php echo $stats['read']; ?>)
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $filter == 'replied' ? 'active' : ''; ?>" href="?filter=replied">
            Dibalas (<?php echo $stats['replied']; ?>)
        </a>
    </li>
</ul>

<!-- Messages List -->
<div class="card shadow-sm">
    <div class="card-body">
        <?php if (empty($messages)): ?>
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">Tidak ada pesan</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Telepon</th>
                            <th>Pesan</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $msg): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($msg['name']); ?></strong></td>
                                <td>
                                    <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>">
                                        <?php echo htmlspecialchars($msg['email']); ?>
                                    </a>
                                </td>
                                <td>
                                    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $msg['phone']); ?>" target="_blank">
                                        <?php echo htmlspecialchars($msg['phone']); ?>
                                    </a>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-link p-0" data-bs-toggle="modal" data-bs-target="#messageModal<?php echo $msg['id']; ?>">
                                        <?php echo htmlspecialchars(substr($msg['message'], 0, 50)); ?>...
                                    </button>
                                </td>
                                <td><?php echo date('d M Y H:i', strtotime($msg['created_at'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $msg['status'] == 'new' ? 'danger' : 
                                            ($msg['status'] == 'read' ? 'info' : 
                                            ($msg['status'] == 'replied' ? 'success' : 'secondary')); 
                                    ?>">
                                        <?php echo ucfirst($msg['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#messageModal<?php echo $msg['id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="update_status">
                                                        <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                                        <input type="hidden" name="status" value="read">
                                                        <button type="submit" class="dropdown-item">Tandai Dibaca</button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="update_status">
                                                        <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                                        <input type="hidden" name="status" value="replied">
                                                        <button type="submit" class="dropdown-item">Tandai Dibalas</button>
                                                    </form>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus pesan ini?');">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                                        <button type="submit" class="dropdown-item text-danger">Hapus</button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Message Detail Modal -->
                            <div class="modal fade" id="messageModal<?php echo $msg['id']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Detail Pesan</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <strong>Nama:</strong> <?php echo htmlspecialchars($msg['name']); ?>
                                            </div>
                                            <div class="mb-3">
                                                <strong>Email:</strong> 
                                                <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>">
                                                    <?php echo htmlspecialchars($msg['email']); ?>
                                                </a>
                                            </div>
                                            <div class="mb-3">
                                                <strong>Telepon:</strong> 
                                                <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $msg['phone']); ?>" target="_blank">
                                                    <?php echo htmlspecialchars($msg['phone']); ?>
                                                </a>
                                            </div>
                                            <div class="mb-3">
                                                <strong>Tanggal:</strong> <?php echo date('d M Y H:i', strtotime($msg['created_at'])); ?>
                                            </div>
                                            <div class="mb-3">
                                                <strong>Status:</strong> 
                                                <span class="badge bg-<?php 
                                                    echo $msg['status'] == 'new' ? 'danger' : 
                                                        ($msg['status'] == 'read' ? 'info' : 
                                                        ($msg['status'] == 'replied' ? 'success' : 'secondary')); 
                                                ?>">
                                                    <?php echo ucfirst($msg['status']); ?>
                                                </span>
                                            </div>
                                            <div class="mb-3">
                                                <strong>Pesan:</strong>
                                                <div class="border p-3 rounded mt-2">
                                                    <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>" class="btn btn-primary">
                                                <i class="fas fa-envelope me-2"></i>Balas Email
                                            </a>
                                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $msg['phone']); ?>" target="_blank" class="btn btn-success">
                                                <i class="fab fa-whatsapp me-2"></i>WhatsApp
                                            </a>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

