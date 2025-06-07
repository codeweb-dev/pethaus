<?php
include('../conn.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id']);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $unit_of_measure = trim($_POST['unit_of_measure']);
    $category = trim($_POST['category']);
    $quantity = intval($_POST['quantity']);

    if ($quantity == 0) {
        $stock = 'Out of Stock';
    } elseif ($quantity <= 10) {
        $stock = 'Low Stock';
    } else {
        $stock = 'In Stock';
    }

    $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, unit_of_measure=?, category=?, quantity=?, stock=? WHERE product_id=?");
    $stmt->bind_param("ssdssisi", $name, $description, $price, $unit_of_measure, $category, $quantity, $stock, $product_id);

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: ../admin/product-inventory.php?success=Product updated successfully.");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
