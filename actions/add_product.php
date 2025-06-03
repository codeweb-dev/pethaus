<?php
session_start();
include('../conn.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    // Required fields
    $requiredFields = ['name', 'description', 'price', 'unit_of_measure', 'category', 'quantity'];
    foreach ($requiredFields as $field) {
        if (empty(trim($_POST[$field] ?? ''))) {
            $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . " is required.";
        }
    }

    // Trim inputs
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $unit_of_measure = trim($_POST['unit_of_measure'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $quantity = trim($_POST['quantity'] ?? '');

    // Additional validations
    if ($price !== '' && (!is_numeric($price) || $price < 0)) {
        $errors['price'] = "Price must be a valid non-negative number.";
    }
    if ($quantity !== '' && (!ctype_digit($quantity) || (int)$quantity < 0)) {
        $errors['quantity'] = "Quantity must be a valid non-negative integer.";
    }

    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['old'] = $_POST;
        header("Location: ../admin/product-inventory.php?modal=add");
        exit();
    }

    $price = (float)$price;
    $quantity = (int)$quantity;

    $stmt = $conn->prepare("INSERT INTO products (name, description, price, unit_of_measure, category, quantity) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    $stmt->bind_param("ssdssi", $name, $description, $price, $unit_of_measure, $category, $quantity);

    if ($stmt->execute()) {
        $stmt->close();
        $_SESSION['success'] = "Successfully added new product.";
        header("Location: ../admin/product-inventory.php?success=Successfully added new product.");
        exit();
    } else {
        $stmt->close();
        $_SESSION['form_errors'] = ['general' => 'Failed to add new product. Please try again.'];
        $_SESSION['old'] = $_POST;
        header("Location: ../admin/product-inventory.php?modal=add");
        exit();
    }
} else {
    header("Location: ../admin/product-inventory.php");
    exit();
}
