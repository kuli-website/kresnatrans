<?php
/**
 * Script untuk test dan debug armada data
 */
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/auth_helper.php';

requireLogin();

echo "<h2>Debug Armada Data</h2>";
echo "<pre>";

try {
    // Test 1: Cek semua data armada
    echo "=== TEST 1: All Armada Data ===\n";
    $stmt = $conn->query("SELECT id, name, capacity, slug, image_path FROM armada ORDER BY id");
    $all = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($all);
    
    // Test 2: Cek apakah ada ID duplikat
    echo "\n=== TEST 2: Check for duplicate IDs ===\n";
    $ids = [];
    foreach ($all as $row) {
        if (in_array($row['id'], $ids)) {
            echo "DUPLICATE ID FOUND: " . $row['id'] . "\n";
        }
        $ids[] = $row['id'];
    }
    echo "Unique IDs: " . count($ids) . "\n";
    
    // Test 3: Cek apakah semua image_path sama
    echo "\n=== TEST 3: Check image_path uniqueness ===\n";
    $image_paths = [];
    foreach ($all as $row) {
        $img = $row['image_path'] ?? 'NULL';
        if (!isset($image_paths[$img])) {
            $image_paths[$img] = [];
        }
        $image_paths[$img][] = $row['id'];
    }
    
    foreach ($image_paths as $path => $ids) {
        if (count($ids) > 1) {
            echo "SAME IMAGE PATH for IDs: " . implode(', ', $ids) . " -> " . $path . "\n";
        }
    }
    
    // Test 4: Cek apakah semua name sama
    echo "\n=== TEST 4: Check name uniqueness ===\n";
    $names = [];
    foreach ($all as $row) {
        $name = $row['name'] ?? 'NULL';
        if (!isset($names[$name])) {
            $names[$name] = [];
        }
        $names[$name][] = $row['id'];
    }
    
    foreach ($names as $name => $ids) {
        if (count($ids) > 1) {
            echo "SAME NAME for IDs: " . implode(', ', $ids) . " -> " . $name . "\n";
        }
    }
    
} catch(PDOException $e) {
    echo "ERROR: " . $e->getMessage();
}

echo "</pre>";
?>

