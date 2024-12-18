<?php
require_once 'config/database.php';

try {
    // Create cart_items table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS cart_items (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )
    ");

    echo "Table cart_items created successfully!";
} catch(PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
} 