<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu']);
    exit();
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['cart_item_id'])) {
        throw new Exception('ID item tidak valid');
    }
    
    // Verify cart item belongs to user
    $stmt = $pdo->prepare("
        SELECT id 
        FROM cart_items 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$data['cart_item_id'], $_SESSION['user_id']]);
    
    if (!$stmt->fetch()) {
        throw new Exception('Item tidak ditemukan');
    }
    
    // Delete cart item
    $stmt = $pdo->prepare("
        DELETE FROM cart_items 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$data['cart_item_id'], $_SESSION['user_id']]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Item berhasil dihapus dari keranjang'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
