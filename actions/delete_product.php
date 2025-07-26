<?php
include('../conn.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];

    $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);

    if ($stmt->execute()) {
        header("Location: ../admin/product-inventory.php?success=Product deleted successfully.");
    } else {
        header("Location: ../admin/product-inventory.php?success=Failed to delete product.");
    }

    $stmt->close();
}

header('Location: ../admin/product-inventory.php');
exit();
