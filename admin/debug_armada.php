<?php
/**
 * Script untuk debug data armada di database
 */
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/auth_helper.php';

requireLogin();

$page_title = 'Debug Armada Data';
include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h2>Debug Data Armada</h2>
    <p>Lihat semua data armada di database untuk debugging</p>
</div>

<div class="card">
    <div class="card-body">
        <?php
        try {
            $stmt = $conn->query("SELECT * FROM armada ORDER BY id ASC");
            $all_armada = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<h5>Total Armada: " . count($all_armada) . "</h5>";
            echo "<table class='table table-bordered table-striped'>";
            echo "<thead><tr>";
            echo "<th>ID</th><th>Name</th><th>Capacity</th><th>Slug</th><th>Image Path</th><th>Media Key</th><th>Is Active</th><th>Features</th>";
            echo "</tr></thead><tbody>";
            
            foreach ($all_armada as $armada) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($armada['id']) . "</td>";
                echo "<td>" . htmlspecialchars($armada['name']) . "</td>";
                echo "<td>" . htmlspecialchars($armada['capacity']) . "</td>";
                echo "<td>" . htmlspecialchars($armada['slug'] ?? 'N/A') . "</td>";
                echo "<td>" . htmlspecialchars($armada['image_path'] ?? 'NULL') . "</td>";
                echo "<td>" . htmlspecialchars($armada['media_key'] ?? 'NULL') . "</td>";
                echo "<td>" . ($armada['is_active'] ? 'Aktif' : 'Nonaktif') . "</td>";
                echo "<td>" . htmlspecialchars($armada['features'] ?? '[]') . "</td>";
                echo "</tr>";
            }
            
            echo "</tbody></table>";
        } catch(PDOException $e) {
            echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
        ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

