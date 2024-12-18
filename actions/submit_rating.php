<?php
session_start();
require_once '../config/database.php';

if (!isset($_POST['order_item_id']) || !isset($_POST['rating'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Data rating tidak lengkap'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO product_ratings (order_item_id, rating, review) VALUES (?, ?, ?)");
    $result = $stmt->execute([
        $_POST['order_item_id'],
        $_POST['rating'],
        $_POST['review'] ?? null
    ]);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Rating berhasil disimpan'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal menyimpan rating'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
