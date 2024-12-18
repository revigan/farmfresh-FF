<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = isset($_POST['product_id']) ? $_POST['product_id'] : null;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    try {
        // Check if product exists and has enough stock
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND status = 'available'");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Produk tidak tersedia']);
            exit();
        }

        if ($product['stock'] < $quantity) {
            echo json_encode(['success' => false, 'message' => 'Stok produk tidak mencukupi']);
            exit();
        }

        // Check if item already in cart
        $stmt = $pdo->prepare("SELECT * FROM cart_items WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$_SESSION['user_id'], $product_id]);
        $cart_item = $stmt->fetch();

        if ($cart_item) {
            // Update quantity if already in cart
            $new_quantity = $cart_item['quantity'] + $quantity;
            if ($new_quantity > $product['stock']) {
                echo json_encode(['success' => false, 'message' => 'Stok produk tidak mencukupi']);
                exit();
            }

            $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
            $stmt->execute([$new_quantity, $cart_item['id']]);
        } else {
            // Add new item to cart
            $stmt = $pdo->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $product_id, $quantity]);
        }

        // Get updated cart count
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM cart_items WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $cart_count = $stmt->fetchColumn();
        $_SESSION['cart_count'] = $cart_count;

        echo json_encode([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan ke keranjang',
            'cart_count' => $cart_count
        ]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Metode request tidak valid']);
}