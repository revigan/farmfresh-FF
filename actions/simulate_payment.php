<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['checkout_data']) || !isset($_SESSION['cart_data']) || !isset($_POST['confirm_payment'])) {
    header('Location: ../checkout.php');
    exit();
}

try {
    $pdo->beginTransaction();

    // Buat order baru
    $stmt = $pdo->prepare("
        INSERT INTO orders (
            user_id, 
            recipient_name, 
            phone, 
            address, 
            payment_method_id, 
            total_amount,
            payment_status,
            order_status,
            payment_proof,
            created_at
        ) VALUES (
            :user_id,
            :recipient_name,
            :phone,
            :address,
            :payment_method_id,
            :total_amount,
            'pending',
            'pending',
            'simulated_payment.jpg',
            CURRENT_TIMESTAMP
        )
    ");

    $stmt->execute([
        ':user_id' => $_SESSION['user_id'],
        ':recipient_name' => $_SESSION['checkout_data']['recipient_name'],
        ':phone' => $_SESSION['checkout_data']['phone'],
        ':address' => $_SESSION['checkout_data']['address'],
        ':payment_method_id' => $_SESSION['checkout_data']['payment_method_id'],
        ':total_amount' => $_SESSION['total_amount']
    ]);

    $order_id = $pdo->lastInsertId();

    // Insert order items
    $stmt = $pdo->prepare("
        INSERT INTO order_items (
            order_id, 
            product_id, 
            quantity, 
            price
        ) VALUES (?, ?, ?, ?)
    ");

    foreach ($_SESSION['cart_data'] as $item) {
        $stmt->execute([
            $order_id,
            $item['product_id'],
            $item['quantity'],
            $item['price']
        ]);

        // Update stok produk (opsional)
        $stmt_update = $pdo->prepare("
            UPDATE products 
            SET stock = stock - ? 
            WHERE id = ?
        ");
        $stmt_update->execute([$item['quantity'], $item['product_id']]);
    }

    // Hapus items dari cart
    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);

    // Clear session checkout data
    unset($_SESSION['checkout_data']);
    unset($_SESSION['cart_data']);
    unset($_SESSION['total_amount']);

    $pdo->commit();

    $_SESSION['success_message'] = 'Pesanan berhasil dibuat dan pembayaran sedang diverifikasi!';
    header('Location: ../orders.php');
    exit();
    
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error_message'] = $e->getMessage();
    header('Location: ../payment.php');
    exit();
}
?>
