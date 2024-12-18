<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

// Pastikan user sudah login dan adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode([
        'success' => false, 
        'message' => 'Unauthorized access'
    ]);
    exit();
}

// Pastikan ID produk ada
if (!isset($_GET['id'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'ID produk tidak ditemukan'
    ]);
    exit();
}

try {
    $pdo->beginTransaction();
    
    $id = $_GET['id'];
    
    // Get product image name first
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        throw new Exception('Produk tidak ditemukan');
    }
    
    // Check if product is used in orders
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE product_id = ?");
    $stmt->execute([$id]);
    $orderCount = $stmt->fetchColumn();
    
    if ($orderCount > 0) {
        throw new Exception('Produk tidak dapat dihapus karena sudah ada dalam pesanan');
    }
    
    // Delete the product
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    if (!$stmt->execute([$id])) {
        throw new Exception('Gagal menghapus produk');
    }
    
    // Delete image file if exists
    if ($product['image'] && file_exists("../../assets/uploads/products/" . $product['image'])) {
        unlink("../../assets/uploads/products/" . $product['image']);
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Produk berhasil dihapus'
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}