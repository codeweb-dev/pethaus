<?php
include '../conn.php';

$id = $_POST['product_sale_id'];
$action = $_POST['action'];

if ($action === 'increase') {
    $sql = "UPDATE product_sale SET sale_quantity = sale_quantity + 1, 
            total_amount = sale_price * (sale_quantity + 1)
            WHERE product_sale_id = $id";
} elseif ($action === 'decrease') {
    $sql = "UPDATE product_sale SET sale_quantity = GREATEST(sale_quantity - 1, 1),
            total_amount = sale_price * GREATEST(sale_quantity - 1, 1)
            WHERE product_sale_id = $id";
}

mysqli_query($conn, $sql);
header("Location: ../admin/open-cart.php");
