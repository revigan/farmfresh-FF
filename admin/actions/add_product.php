<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: ../../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $unit = $_POST['unit'];
    $description = $_POST['description'];
    $status = $_POST['status'];

    // Handle image upload
    $image = $_FILES['image'];
    $image_name = time() . '_' . $image['name'];
    $target_dir = "../../assets/uploads/products/";
    
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    if (move_uploaded_file($image['tmp_name'], $target_dir . $image_name)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO products (category_id, name, description, price, stock, unit, image, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $category_id, $name, $description, $price, $stock, $unit, $image_name, $status
            ]);

            header('Location: ../products.php?success=added');
            exit();
        } catch(PDOException $e) {
            header('Location: ../products.php?error=db');
            exit();
        }
    } else {
        header('Location: ../products.php?error=upload');
        exit();
    }
}
