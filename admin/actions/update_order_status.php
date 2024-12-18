<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if (!isset($_POST['order_id']) || !isset($_POST['status'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Order ID and status are required']);
    exit();
}

try {
    $pdo->beginTransaction();
    
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    
    // Validate status
    $valid_statuses = ['pending', 'processing', 'shipped', 'completed'];
    if (!in_array($status, $valid_statuses)) {
        throw new Exception('Status tidak valid');
    }
    
    // Verifikasi order exists
    $check = $pdo->prepare("SELECT id FROM orders WHERE id = ?");
    $check->execute([$order_id]);
    if (!$check->fetch()) {
        throw new Exception('Order tidak ditemukan');
    }
    
    $stmt = $pdo->prepare("
        UPDATE orders 
        SET order_status = ?
        WHERE id = ?
    ");
    
    if (!$stmt->execute([$status, $order_id])) {
        throw new Exception('Gagal memperbarui status pesanan');
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Status pesanan berhasil diperbarui']);
    
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
