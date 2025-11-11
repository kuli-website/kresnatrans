<?php
if (!isset($page_title)) {
    $page_title = 'Admin Panel';
}
$current_admin = getCurrentAdmin();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Sewa Bus Jogja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #343a40;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-radius: 5px;
            margin: 5px 10px;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: #fff;
        }
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        .main-content {
            background: #f8f9fa;
            min-height: 100vh;
        }
        .navbar-brand {
            font-weight: bold;
        }
        .stat-card {
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-0">
                <div class="p-3">
                    <h4 class="text-white mb-4">
                        <i class="fas fa-bus me-2"></i>Admin Panel
                    </h4>
                    <nav class="nav flex-column">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>" href="index.php">
                            <i class="fas fa-home"></i>Dashboard
                        </a>
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'messages.php') ? 'active' : ''; ?>" href="messages.php">
                            <i class="fas fa-envelope"></i>Pesan
                            <?php
                            // Count unread messages
                            if (isset($conn) && $conn !== null) {
                                try {
                                    $unreadCount = $conn->query("SELECT COUNT(*) as count FROM messages WHERE status = 'new'")->fetch(PDO::FETCH_ASSOC);
                                    if ($unreadCount['count'] > 0) {
                                        echo '<span class="badge bg-danger ms-2">' . $unreadCount['count'] . '</span>';
                                    }
                                } catch(PDOException $e) {}
                            }
                            ?>
                        </a>
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'media.php') ? 'active' : ''; ?>" href="media.php">
                            <i class="fas fa-images"></i>Media/Foto
                        </a>
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'articles.php') ? 'active' : ''; ?>" href="articles.php">
                            <i class="fas fa-newspaper"></i>Artikel/Berita
                        </a>
                        <?php if (isSuperAdmin()): ?>
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'add_admin.php') ? 'active' : ''; ?>" href="add_admin.php">
                            <i class="fas fa-user-plus"></i>Tambah Admin
                        </a>
                        <?php endif; ?>
                        <hr class="text-white">
                        <a class="nav-link" href="../index.php" target="_blank">
                            <i class="fas fa-external-link-alt"></i>Lihat Website
                        </a>
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i>Logout
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 main-content p-0">
                <!-- Top Navbar -->
                <nav class="navbar navbar-light bg-white border-bottom shadow-sm">
                    <div class="container-fluid">
                        <span class="navbar-brand mb-0 h1"><?php echo htmlspecialchars($page_title); ?></span>
                        <div class="d-flex align-items-center">
                            <span class="text-muted me-3">
                                <i class="fas fa-user me-2"></i>
                                <?php echo htmlspecialchars($current_admin['full_name'] ?: $current_admin['username']); ?>
                            </span>
                        </div>
                    </div>
                </nav>
                
                <!-- Page Content -->
                <div class="p-4">

