<?php
session_start();
require('../assets/fpdf/fpdf.php');
include '../conn.php';

$sale_id = $_GET['sale_id'] ?? 0;

$saleQuery = mysqli_query($conn, "SELECT * FROM sales WHERE sale_id = $sale_id");
$sale = mysqli_fetch_assoc($saleQuery);
$date = date('F j, Y', strtotime($sale['others_date']));

$staff = $_SESSION['first_name'] ?? '';
$staff .= isset($_SESSION['middle_name']) ? ' ' . $_SESSION['middle_name'][0] . '.' : '';
$staff .= ' ' . ($_SESSION['last_name'] ?? '');

$items = mysqli_query($conn, "
    SELECT 
        p.name AS product_name,
        ps.sale_quantity,
        ps.sale_price,
        ps.total_amount
    FROM product_sale ps
    JOIN products p ON ps.product_id = p.product_id
    WHERE ps.sale_id = $sale_id
");

$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();

$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'PetHaus Veterinary Clinic', 0, 1, 'C');

$pdf->SetFont('Arial', '', 12);
$pdf->Ln(2);
$pdf->Cell(100, 8, 'Date: ' . $date, 0, 1);
$pdf->Cell(100, 8, 'Prepared by: ' . $staff , 0, 1);
$pdf->Cell(100, 8, 'Sale ID: ' . $sale_id, 0, 1);
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(80, 10, 'Product', 1);
$pdf->Cell(25, 10, 'Qty', 1);
$pdf->Cell(30, 10, 'Price (PHP)', 1);
$pdf->Cell(40, 10, 'Subtotal (PHP)', 1);
$pdf->Ln();

$total = 0;

$pdf->SetFont('Arial', '', 12);
while ($row = mysqli_fetch_assoc($items)) {
    $pdf->Cell(80, 10, $row['product_name'], 1);
    $pdf->Cell(25, 10, $row['sale_quantity'], 1, 0, 'C');
    $pdf->Cell(30, 10, number_format($row['sale_price'], 2), 1, 0, 'R');
    $pdf->Cell(40, 10, number_format($row['total_amount'], 2), 1, 0, 'R');
    $pdf->Ln();
    $total += $row['total_amount'];
}

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(135, 10, 'Total', 1);
$pdf->Cell(40, 10, 'PHP' . number_format($total, 2), 1, 0, 'R');

$pdf->Output("I", "Receipt_$sale_id.pdf");
?>
