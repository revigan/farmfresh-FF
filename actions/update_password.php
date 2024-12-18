<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];

    try {
        // Validasi input
        if (empty($old_password) || empty($new_password) || empty($confirm_new_password)) {
            throw new Exception('Semua field harus diisi');
        }

        if ($new_password !== $confirm_new_password) {
            throw new Exception('Konfirmasi password tidak sesuai');
        }

        if (strlen($new_password) < 6) {
            throw new Exception('Password baru minimal 6 karakter');
        }

        // Cek password lama
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if (!password_verify($old_password, $user['password'])) {
            throw new Exception('Password lama tidak sesuai');
        }

        // Update password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $result = $stmt->execute([$hashed_password, $_SESSION['user_id']]);

        if ($result) {
            $_SESSION['success_message'] = 'Password berhasil diperbarui';
        } else {
            throw new Exception('Gagal memperbarui password');
        }

    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
}

header('Location: ../profile.php');
exit();
?> 