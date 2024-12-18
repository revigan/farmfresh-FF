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
    $cart_item_id = $data['cart_item_id'];
    $action = $data['action'];
    
    // Get current cart item
    $stmt = $pdo->prepare("SELECT * FROM cart_items WHERE id = ? AND user_id = ?");
    $stmt->execute([$cart_item_id, $_SESSION['user_id']]);
    $cart_item = $stmt->fetch();
    
    if (!$cart_item) {
        throw new Exception('Item tidak ditemukan');
    }
    
    // Get product stock
    $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
    $stmt->execute([$cart_item['product_id']]);
    $product = $stmt->fetch();
    
    $new_quantity = $cart_item['quantity'];
    
    switch ($action) {
        case 'increase':
            if ($new_quantity >= $product['stock']) {
                throw new Exception('Stok tidak mencukupi');
            }
            $new_quantity++;
            break;
            
        case 'decrease':
            if ($new_quantity <= 1) {
                throw new Exception('Jumlah minimum adalah 1');
            }
            $new_quantity--;
            break;
            
        case 'set':
            $new_quantity = $data['quantity'];
            if ($new_quantity < 1) {
                throw new Exception('Jumlah minimum adalah 1');
            }
            if ($new_quantity > $product['stock']) {
                throw new Exception('Stok tidak mencukupi');
            }
            break;
            
        default:
            throw new Exception('Aksi tidak valid');
    }
    
    // Update quantity
    $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$new_quantity, $cart_item_id, $_SESSION['user_id']]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Keranjang berhasil diupdate',
        'new_quantity' => $new_quantity
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
