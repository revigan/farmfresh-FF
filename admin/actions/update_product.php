<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../products.php');
    exit();
}

try {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $unit = $_POST['unit'];
    $description = $_POST['description'];
    $status = $_POST['status'];

    // Start transaction
    $pdo->beginTransaction();

    // Check if image is uploaded
    if (!empty($_FILES['image']['name'])) {
        $image = $_FILES['image'];
        $image_name = time() . '_' . $image['name'];
        $target_path = "../../assets/uploads/products/" . $image_name;

        // Get old image
        $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $old_image = $stmt->fetch()['image'];

        // Upload new image
        if (move_uploaded_file($image['tmp_name'], $target_path)) {
            // Delete old image if exists
            if ($old_image && file_exists("../../assets/uploads/products/" . $old_image)) {
                unlink("../../assets/uploads/products/" . $old_image);
            }

            // Update with new image
            $stmt = $pdo->prepare("
                UPDATE products 
                SET name = ?, category_id = ?, price = ?, stock = ?, 
                    unit = ?, description = ?, status = ?, image = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $category_id, $price, $stock, $unit, $description, $status, $image_name, $id]);
        } else {
            throw new Exception('Gagal mengupload gambar');
        }
    } else {
        // Update without changing image
        $stmt = $pdo->prepare("
            UPDATE products 
            SET name = ?, category_id = ?, price = ?, stock = ?, 
                unit = ?, description = ?, status = ?
            WHERE id = ?
        ");
        $stmt->execute([$name, $category_id, $price, $stock, $unit, $description, $status, $id]);
    }

    $pdo->commit();
    $_SESSION['success'] = 'Produk berhasil diperbarui';
    
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = $e->getMessage();
}

header('Location: ../edit_product.php?id=' . $id);
exit();
