<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

try {
    $order_id = $_POST['order_id'];
    
    // Upload bukti pembayaran
    $target_dir = "../assets/uploads/payments/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($_FILES["proof_image"]["name"], PATHINFO_EXTENSION));
    $file_name = "payment_" . $order_id . "_" . time() . "." . $file_extension;
    $target_file = $target_dir . $file_name;
    
    // Validasi file
    $allowed_types = ['jpg', 'jpeg', 'png'];
    if (!in_array($file_extension, $allowed_types)) {
        throw new Exception('Format file tidak valid. Gunakan JPG, JPEG, atau PNG');
    }
    
    if (!move_uploaded_file($_FILES["proof_image"]["tmp_name"], $target_file)) {
        throw new Exception('Gagal mengupload file');
    }
    
    // Update order status
    $stmt = $pdo->prepare("
        UPDATE orders 
        SET payment_proof = ?, 
            payment_status = 'pending',
            updated_at = CURRENT_TIMESTAMP 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$file_name, $order_id, $_SESSION['user_id']]);
    
    $_SESSION['success_message'] = 'Bukti pembayaran berhasil diupload';
    header('Location: ../order_history.php');
    
} catch (Exception $e) {
    $_SESSION['error_message'] = $e->getMessage();
    header('Location: ../payment.php');
}
?>
