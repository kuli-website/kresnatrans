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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        
        body {
            background: #f5f7fa;
            overflow-x: hidden;
        }
        
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            position: fixed;
            width: 260px;
            z-index: 1000;
        }
        
        .sidebar .logo-section {
            padding: 25px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        
        .sidebar .logo-section h4 {
            color: #fff;
            font-weight: 700;
            font-size: 1.25rem;
            margin: 0;
            display: flex;
            align-items: center;
        }
        
        .sidebar .logo-section h4 i {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-right: 10px;
            font-size: 1.5rem;
        }
        
        .sidebar .nav {
            padding: 0 15px;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.7);
            padding: 12px 15px;
            border-radius: 10px;
            margin: 5px 0;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
        }
        
        .sidebar .nav-link i {
            width: 22px;
            margin-right: 12px;
            text-align: center;
            font-size: 1.1rem;
        }
        
        .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.08);
            color: #fff;
            transform: translateX(5px);
        }
        
        .sidebar .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .sidebar .nav-link .badge {
            margin-left: auto;
            font-size: 0.7rem;
            padding: 4px 8px;
        }
        
        .sidebar hr {
            border-color: rgba(255,255,255,0.1);
            margin: 20px 15px;
        }
        
        .main-content {
            margin-left: 260px;
            min-height: 100vh;
            background: #f5f7fa;
        }
        
        .top-navbar {
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            padding: 15px 30px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .top-navbar .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: #1e293b;
            margin: 0;
        }
        
        .top-navbar .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .top-navbar .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .page-content {
            padding: 30px;
        }
        
        .page-header {
            margin-bottom: 30px;
        }
        
        .page-header h2 {
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 5px 0;
            font-size: 1.75rem;
        }
        
        .page-header p {
            color: #64748b;
            margin: 0;
            font-size: 0.95rem;
        }
        
        .stat-card {
            background: #fff;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            border: 1px solid #e5e7eb;
            height: 100%;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .stat-card .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }
        
        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }
        
        .stat-card .stat-label {
            color: #64748b;
            font-size: 0.9rem;
            font-weight: 500;
            margin: 5px 0 0 0;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border: none;
            padding: 20px 25px;
            font-weight: 600;
        }
        
        .card-body {
            padding: 25px;
        }
        
        .btn {
            border-radius: 10px;
            font-weight: 500;
            padding: 10px 20px;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table thead {
            background: #f8f9fa;
        }
        
        .table thead th {
            font-weight: 600;
            color: #1e293b;
            border: none;
            padding: 15px;
        }
        
        .table tbody td {
            padding: 15px;
            vertical-align: middle;
        }
        
        .badge {
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.8rem;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
                <div class="logo-section">
                    <h4>
                        <i class="fas fa-bus"></i>
                        Admin Panel
                    </h4>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>" href="index.php">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'messages.php') ? 'active' : ''; ?>" href="messages.php">
                        <i class="fas fa-envelope"></i>
                        <span>Pesan</span>
                        <?php
                        // Count unread messages
                        if (isset($conn) && $conn !== null) {
                            try {
                                $unreadCount = $conn->query("SELECT COUNT(*) as count FROM messages WHERE status = 'new'")->fetch(PDO::FETCH_ASSOC);
                                if ($unreadCount['count'] > 0) {
                                    echo '<span class="badge bg-danger">' . $unreadCount['count'] . '</span>';
                                }
                            } catch(PDOException $e) {}
                        }
                        ?>
                    </a>
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'media.php') ? 'active' : ''; ?>" href="media.php">
                        <i class="fas fa-images"></i>
                        <span>Media/Foto</span>
                    </a>
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'articles.php') ? 'active' : ''; ?>" href="articles.php">
                        <i class="fas fa-newspaper"></i>
                        <span>Artikel/Berita</span>
                    </a>
                    <?php if (isSuperAdmin()): ?>
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'add_admin.php') ? 'active' : ''; ?>" href="add_admin.php">
                        <i class="fas fa-user-plus"></i>
                        <span>Tambah Admin</span>
                    </a>
                    <?php endif; ?>
                    <hr>
                    <a class="nav-link" href="../index.php" target="_blank">
                        <i class="fas fa-external-link-alt"></i>
                        <span>Lihat Website</span>
                    </a>
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="main-content">
                <!-- Top Navbar -->
                <div class="top-navbar">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <h1 class="navbar-brand mb-0"><?php echo htmlspecialchars($page_title); ?></h1>
                        <div class="user-info">
                            <div class="user-avatar">
                                <?php 
                                $name = $current_admin['full_name'] ?: $current_admin['username'];
                                echo strtoupper(substr($name, 0, 1)); 
                                ?>
                            </div>
                            <div>
                                <div class="fw-semibold" style="color: #1e293b; font-size: 0.9rem;">
                                    <?php echo htmlspecialchars($name); ?>
                                </div>
                                <small class="text-muted" style="font-size: 0.8rem;">
                                    <?php echo htmlspecialchars($current_admin['role'] == 'super_admin' ? 'Super Admin' : 'Admin'); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Page Content -->
                <div class="page-content">

