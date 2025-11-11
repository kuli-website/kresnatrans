<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/auth_helper.php';

requireLogin();

$page_title = 'Dashboard';

// Get statistics
$stats = [
    'total_messages' => 0,
    'new_messages' => 0,
    'total_articles' => 0,
    'published_articles' => 0,
    'total_media' => 0,
    'total_admins' => 0
];

try {
    // Messages stats
    $stats['total_messages'] = $conn->query("SELECT COUNT(*) as count FROM messages")->fetch(PDO::FETCH_ASSOC)['count'];
    $stats['new_messages'] = $conn->query("SELECT COUNT(*) as count FROM messages WHERE status = 'new'")->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Articles stats
    $articlesTable = $conn->query("SHOW TABLES LIKE 'articles'")->rowCount();
    if ($articlesTable > 0) {
        $stats['total_articles'] = $conn->query("SELECT COUNT(*) as count FROM articles")->fetch(PDO::FETCH_ASSOC)['count'];
        $stats['published_articles'] = $conn->query("SELECT COUNT(*) as count FROM articles WHERE status = 'published'")->fetch(PDO::FETCH_ASSOC)['count'];
    }
    
    // Media stats
    $mediaTable = $conn->query("SHOW TABLES LIKE 'media'")->rowCount();
    if ($mediaTable > 0) {
        $stats['total_media'] = $conn->query("SELECT COUNT(*) as count FROM media")->fetch(PDO::FETCH_ASSOC)['count'];
    }
    
    // Admin stats
    $stats['total_admins'] = $conn->query("SELECT COUNT(*) as count FROM admin_users")->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Recent messages
    $recent_messages = $conn->query("SELECT * FROM messages ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    
    // Recent articles
    $recent_articles = [];
    if ($articlesTable > 0) {
        $recent_articles = $conn->query("SELECT * FROM articles ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    }
    
} catch(PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
}

include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h2>Dashboard</h2>
    <p>Selamat datang, <?php echo htmlspecialchars(getCurrentAdmin()['full_name'] ?: getCurrentAdmin()['username']); ?>! 👋</p>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff;">
                <i class="fas fa-envelope"></i>
            </div>
            <h3 class="stat-value"><?php echo number_format($stats['total_messages']); ?></h3>
            <p class="stat-label">Total Pesan</p>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: #fff;">
                <i class="fas fa-envelope-open"></i>
            </div>
            <h3 class="stat-value" style="color: #f5576c;"><?php echo number_format($stats['new_messages']); ?></h3>
            <p class="stat-label">Pesan Baru</p>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: #fff;">
                <i class="fas fa-newspaper"></i>
            </div>
            <h3 class="stat-value" style="color: #4facfe;"><?php echo number_format($stats['total_articles']); ?></h3>
            <p class="stat-label">Total Artikel</p>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: #fff;">
                <i class="fas fa-images"></i>
            </div>
            <h3 class="stat-value" style="color: #43e97b;"><?php echo number_format($stats['total_media']); ?></h3>
            <p class="stat-label">Total Media</p>
        </div>
    </div>
</div>

<!-- Recent Messages and Articles -->
<div class="row g-4">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-envelope me-2"></i>Pesan Terbaru</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recent_messages)): ?>
                    <p class="text-muted mb-0">Belum ada pesan</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recent_messages as $msg): ?>
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($msg['name']); ?></h6>
                                        <p class="mb-1 text-muted small"><?php echo htmlspecialchars(substr($msg['message'], 0, 100)); ?>...</p>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            <?php echo date('d M Y H:i', strtotime($msg['created_at'])); ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-<?php echo $msg['status'] == 'new' ? 'danger' : 'secondary'; ?>">
                                        <?php echo ucfirst($msg['status']); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-3">
                        <a href="messages.php" class="btn btn-sm btn-outline-primary w-100">Lihat Semua Pesan</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-newspaper me-2"></i>Artikel Terbaru</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recent_articles)): ?>
                    <p class="text-muted mb-0">Belum ada artikel</p>
                    <a href="articles.php?action=create" class="btn btn-sm btn-success mt-2">Buat Artikel Baru</a>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recent_articles as $article): ?>
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($article['title']); ?></h6>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            <?php echo date('d M Y H:i', strtotime($article['created_at'])); ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-<?php 
                                        echo $article['status'] == 'published' ? 'success' : 
                                            ($article['status'] == 'draft' ? 'warning' : 'secondary'); 
                                    ?>">
                                        <?php echo ucfirst($article['status']); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-3">
                        <a href="articles.php" class="btn btn-sm btn-outline-success w-100">Lihat Semua Artikel</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

