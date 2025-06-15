<?php
include '../conn.php';

$insertSale = mysqli_query($conn, "INSERT INTO sales (others_date) VALUES (NOW())");

if (!$insertSale) {
    die("Failed to create sale record: " . mysqli_error($conn));
}

$sale_id = mysqli_insert_id($conn);

$update = mysqli_query($conn, "UPDATE product_sale SET sale_id = $sale_id WHERE sale_id IS NULL");

if (!$update) {
    die("Failed to update product sales: " . mysqli_error($conn));
}

header("Location: receipt.php?sale_id=" . $sale_id);
exit();
