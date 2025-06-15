<?php
include '../conn.php';
$id = $_POST['product_sale_id'];
mysqli_query($conn, "DELETE FROM product_sale WHERE product_sale_id = $id");
header("Location: ../admin/open-cart.php?success=Item deleted succesfully.");
