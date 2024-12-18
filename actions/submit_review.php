<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    $order_id = $_POST['order_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    
    // Get order items
    $stmt = $pdo->prepare("SELECT product_id FROM order_items WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll();
    
    // Insert review for each product
    $stmt = $pdo->prepare("
        INSERT INTO reviews (user_id, product_id, order_id, rating, comment)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    foreach($items as $item) {
        $stmt->execute([
            $_SESSION['user_id'],
            $item['product_id'],
            $order_id,
            $rating,
            $comment
        ]);
    }
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
