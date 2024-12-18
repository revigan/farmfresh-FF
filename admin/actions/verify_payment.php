<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if (!isset($_POST['order_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Order ID is required']);
    exit();
}

try {
    $pdo->beginTransaction();
    
    $order_id = $_POST['order_id'];
    
    // Verifikasi order exists
    $check = $pdo->prepare("SELECT id FROM orders WHERE id = ?");
    $check->execute([$order_id]);
    if (!$check->fetch()) {
        throw new Exception('Order tidak ditemukan');
    }
    
    $stmt = $pdo->prepare("
        UPDATE orders 
        SET payment_status = 'paid'
        WHERE id = ?
    ");
    
    if (!$stmt->execute([$order_id])) {
        throw new Exception('Gagal memperbarui status pembayaran');
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Pembayaran berhasil diverifikasi']);
    
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
