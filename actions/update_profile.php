<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

try {
    // Prepare base update query and parameters
    $updateFields = [];
    $params = [];

    // Handle name update
    if (isset($_POST['name']) && !empty($_POST['name'])) {
        $updateFields[] = "name = ?";
        $params[] = trim($_POST['name']);
    }

    // Handle phone update
    if (isset($_POST['phone'])) {
        $updateFields[] = "phone = ?";
        $params[] = !empty($_POST['phone']) ? trim($_POST['phone']) : null;
    }

    // Handle address update
    if (isset($_POST['address'])) {
        $updateFields[] = "address = ?";
        $params[] = !empty($_POST['address']) ? trim($_POST['address']) : null;
    }

    // Handle profile image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        if (!in_array($_FILES['profile_image']['type'], $allowedTypes)) {
            throw new Exception('Format file tidak didukung. Gunakan JPG, JPEG, atau PNG.');
        }

        if ($_FILES['profile_image']['size'] > $maxSize) {
            throw new Exception('Ukuran file terlalu besar. Maksimal 2MB.');
        }

        // Generate unique filename
        $extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $uploadPath = '../assets/uploads/profiles/' . $filename;

        // Create directory if it doesn't exist
        if (!file_exists('../assets/uploads/profiles/')) {
            mkdir('../assets/uploads/profiles/', 0777, true);
        }

        // Delete old profile image if exists
        $stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $oldImage = $stmt->fetchColumn();
        
        if ($oldImage && file_exists('../assets/uploads/profiles/' . $oldImage)) {
            unlink('../assets/uploads/profiles/' . $oldImage);
        }

        // Upload new image
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath)) {
            $updateFields[] = "profile_image = ?";
            $params[] = $filename;
        } else {
            throw new Exception('Gagal mengupload file.');
        }
    }

    // If there are fields to update
    if (!empty($updateFields)) {
        // Add user_id to params
        $params[] = $_SESSION['user_id'];

        // Construct and execute update query
        $query = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        if ($stmt->rowCount() > 0) {
            $_SESSION['success_message'] = 'Profil berhasil diperbarui';
        } else {
            $_SESSION['error_message'] = 'Tidak ada perubahan yang disimpan';
        }
    } else {
        $_SESSION['error_message'] = 'Tidak ada data yang diperbarui';
    }

} catch (Exception $e) {
    $_SESSION['error_message'] = 'Error: ' . $e->getMessage();
}

// Redirect back to profile page
header('Location: ../profile.php');
exit();
?>
