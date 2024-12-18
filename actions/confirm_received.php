<?php
session_start();
require_once '../config/database.php';

// Terima data JSON
$data = json_decode(file_get_contents('php://input'), true);

// Validasi order_id
if (!isset($data['order_id']) || empty($data['order_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Order ID is required'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE orders SET order_status = 'completed' WHERE id = ? AND user_id = ?");
    $result = $stmt->execute([$data['order_id'], $_SESSION['user_id']]);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Order status updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update order status'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
