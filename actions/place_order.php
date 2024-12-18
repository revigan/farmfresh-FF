<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: ../cart.php');
    exit();
}

try {
    // Simpan data checkout ke session
    $_SESSION['checkout_data'] = [
        'recipient_name' => $_POST['recipient_name'],
        'phone' => $_POST['phone'],
        'address' => $_POST['address'],
        'payment_method_id' => $_POST['payment_method']
    ];

    // Simpan data cart ke session
    $stmt = $pdo->prepare("
        SELECT cart_items.*, products.price, products.name 
        FROM cart_items 
        JOIN products ON cart_items.product_id = products.id 
        WHERE cart_items.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $_SESSION['cart_data'] = $stmt->fetchAll();

    // Hitung total
    $total = 0;
    foreach ($_SESSION['cart_data'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    $_SESSION['total_amount'] = $total;

    // Redirect ke halaman pembayaran
    header('Location: ../payment.php');
    exit();
    
} catch (Exception $e) {
    $_SESSION['error_message'] = $e->getMessage();
    header('Location: ../checkout.php');
    exit();
}
?>
