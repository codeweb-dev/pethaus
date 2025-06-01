<?php
include('../conn.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $unit_of_measure = trim($_POST['unit_of_measure']);
    $category = trim($_POST['category']);
    $quantity = trim($_POST['quantity']);

    $stmt = $conn->prepare("INSERT INTO products (name, description, price, unit_of_measure, category, quantity) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdssi", $name, $description, $price, $unit_of_measure, $category, $quantity);

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: ../admin/product-inventory.php?success=Successfully added new product.");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
