<?php
session_start();
include('../conn.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    // Required fields
    $requiredFields = ['name', 'description', 'price', 'unit_of_measure', 'category', 'quantity'];
    foreach ($requiredFields as $field) {
        if ($field === 'quantity') {
            if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                // This will allow "0" but not an empty string
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . " is required.";
            }
        } else {
            if (empty(trim($_POST[$field] ?? ''))) {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . " is required.";
            }
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

    if ($quantity == 0) {
        $stock = 'Out of Stock';
    } elseif ($quantity <= 10) {
        $stock = 'Low Stock';
    } else {
        $stock = 'In Stock';
    }

    $stmt = $conn->prepare("INSERT INTO products (name, description, price, unit_of_measure, category, quantity, stock) VALUES (?, ?, ?, ?, ?, ?, ?)");

    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    $stmt->bind_param("ssdssis", $name, $description, $price, $unit_of_measure, $category, $quantity, $stock);

    if ($stmt->execute()) {
        $inserted_id = $stmt->insert_id;
        $stmt->close();

        // Generate product code like 0001, 0002
        $product_code = str_pad($inserted_id, 4, '0', STR_PAD_LEFT);

        // Update product_code for that product
        $update_stmt = $conn->prepare("UPDATE products SET product_code = ? WHERE product_id = ?");
        $update_stmt->bind_param("si", $product_code, $inserted_id);
        $update_stmt->execute();
        $update_stmt->close();

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
