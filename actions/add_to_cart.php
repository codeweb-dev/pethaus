<?php
include('../conn.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = (int) ($_POST['product_id'] ?? 0);
    $sale_quantity = (int) ($_POST['sale_quantity'] ?? 0);
    $unit_of_measure = trim($_POST['unit_of_measure'] ?? '');
    $actual_price = (float) ($_POST['actual_price'] ?? 0);

    $productQuery = mysqli_query($conn, "SELECT * FROM products WHERE product_id = $product_id");
    if (!$productQuery || mysqli_num_rows($productQuery) == 0) {
        header('Location: ../admin/product-inventory.php?error=Product not found.');
        exit;
    }

    $product = mysqli_fetch_assoc($productQuery);
    $available_quantity = (int) $product['quantity'];

    if ($sale_quantity <= 0 || $sale_quantity > $available_quantity) {
        header('Location: ../admin/product-inventory.php?error=Invalid quantity selected.');
        exit;
    }

    $sale_price = (float) $product['price'];
    $total_amount = $sale_price * $sale_quantity;

    $saleInsert = mysqli_prepare($conn, "INSERT INTO product_sale (product_id, sale_price, sale_quantity, unit_of_measure, total_amount) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($saleInsert, "iddsd", $product_id, $sale_price, $sale_quantity, $unit_of_measure, $total_amount);
    $success = mysqli_stmt_execute($saleInsert);
    mysqli_stmt_close($saleInsert);

    if (!$success) {
        header('Location: ../admin/product-inventory.php?error=Failed to save sale details.');
        exit;
    }

    $new_quantity = $available_quantity - $sale_quantity;

    if ($new_quantity == 0) {
        $stock = 'Out of Stock';
    } elseif ($new_quantity <= 10) {
        $stock = 'Low Stock';
    } else {
        $stock = 'In Stock';
    }

    mysqli_query($conn, "UPDATE products SET quantity = $new_quantity, stock = '$stock' WHERE product_id = $product_id");

    header('Location: ../admin/product-inventory.php?success=Product added to cart and inventory updated.');
    exit;
}

header('Location: ../admin/product-inventory.php?error=Invalid request.');
exit;
