<?php
session_start(); // Make sure the user is logged in

require('../assets/fpdf/fpdf.php');
include('../conn.php');

$record_id = $_GET['record_id'] ?? 0;

if (!$record_id) {
    die("No record ID provided.");
}

// Fetch medical record and pet/owner info
$query = "
    SELECT 
        mr.*, 
        p.name AS pet_name, p.breed, p.species, p.color, p.sex, p.birthdate, p.markings,
        po.first_name, po.middle_name, po.last_name, po.mobile_number
    FROM medical_records mr
    LEFT JOIN pet_records p ON mr.pet_id = p.pet_id
    LEFT JOIN pet_owner_records po ON mr.owner_id = po.owner_id
    WHERE mr.medical_record_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $record_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    die("Medical record not found.");
}

// Calculate totals
$treat_charge = floatval($data['treatment_charge']);
$rx_charge = floatval($data['prescription_charge']);
$other_charge = floatval($data['others_charge']);
$total = $treat_charge + $rx_charge + $other_charge;

// Format owner name
$owner = $data['first_name'];
if (!empty($data['middle_name'])) {
    $owner .= ' ' . $data['middle_name'][0] . '.';
}
$owner .= ' ' . $data['last_name'];
$owner = trim($owner);

// Prepared By (from session)
$staff = $_SESSION['first_name'] ?? '';
if (!empty($_SESSION['middle_name'])) {
    $staff .= ' ' . $_SESSION['middle_name'][0] . '.';
}
$staff .= ' ' . ($_SESSION['last_name'] ?? '');
$staff = trim($staff);

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// Header section: LOGO on left, CLINIC INFO right-aligned
$pdf->SetFont('Arial', 'B', 10);

// Logo on left
$pdf->Image('../assets/images/logo.jpg', 10, 7, 25);

// Right-align clinic details
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetXY(140, 10); // Adjust X/Y as needed for spacing
$pdf->Cell(0, 6, 'Pet Haus Animal Clinic & Supplies', 0, 1, 'R');

$pdf->SetFont('Arial', '', 10);
$pdf->SetX(140);
$pdf->Cell(0, 5, 'R.B. Castillo St. Mangagoy, Bislig City, Surigao del Sur', 0, 1, 'R');
$pdf->SetX(140);
$pdf->Cell(0, 5, 'Contact #: 0912-345-6789 / 0999-123-4567', 0, 1, 'R');
$pdf->SetX(140);
$pdf->Cell(0, 5, 'FB Page: Pet Haus Animal Clinic & Supplies', 0, 1, 'R');

$pdf->Ln(10);

// Owner and Date - aligned left and right
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(95, 8, 'Owner: ' . $owner, 0, 0, 'L'); // left-aligned
$pdf->Cell(95, 8, 'Date: ' . date('m/d/Y'), 0, 1, 'R'); // right-aligned

// Pet Name
$pdf->Cell(95, 8, 'Pet Name: ' . $data['pet_name'], 0, 1, 'L');

// Section Title
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 10, 'Pet Haus Medical Bill Breakdown', 0, 1, 'C');

// Table Header
$pdf->Ln(2);
$pdf->SetFillColor(230);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(35, 8, 'DATE', 1, 0, 'C', true);
$pdf->Cell(55, 8, 'ITEMS', 1, 0, 'C', true);
$pdf->Cell(30, 8, 'QUANTITY', 1, 0, 'C', true);
$pdf->Cell(30, 8, 'UNIT PRICE', 1, 0, 'C', true);
$pdf->Cell(35, 8, 'TOTAL', 1, 1, 'C', true);

// Table Body
$pdf->SetFont('Arial', '', 11);
$hasItems = false;

// Treatment
if (!empty($data['treatment_name'])) {
    $pdf->Cell(35, 8, $data['treatment_date'], 1);
    $pdf->Cell(55, 8, $data['treatment_name'], 1);
    $pdf->Cell(30, 8, 'N/A', 1);
    $pdf->Cell(30, 8, 'N/A', 1);
    $pdf->Cell(35, 8, 'PHP ' . number_format($treat_charge, 2), 1, 1);
    $hasItems = true;
}

// Prescription
if (!empty($data['prescription_name'])) {
    $pdf->Cell(35, 8, $data['prescription_date'], 1);
    $pdf->Cell(55, 8, $data['prescription_name'], 1);
    $pdf->Cell(30, 8, 'N/A', 1);
    $pdf->Cell(30, 8, 'N/A', 1);
    $pdf->Cell(35, 8, 'PHP ' . number_format($rx_charge, 2), 1, 1);
    $hasItems = true;
}

// Others
if (!empty($data['others_name'])) {
    $pdf->Cell(35, 8, $data['others_date'], 1);
    $pdf->Cell(55, 8, $data['others_name'], 1);
    $pdf->Cell(30, 8, $data['others_quantity'], 1);
    $pdf->Cell(30, 8, 'N/A', 1);
    $pdf->Cell(35, 8, 'PHP ' . number_format($other_charge, 2), 1, 1);
    $hasItems = true;
}

// If no items at all
if (!$hasItems) {
    $pdf->Cell(185, 8, 'No charges recorded for this medical record.', 1, 1, 'C');
}

// Total
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(150, 8, 'TOTAL BILLING', 1);
$pdf->Cell(35, 8, 'PHP ' . number_format($total, 2), 1, 1);

$pdf->Ln(10);
$pdf->SetFont('Arial', '', 11);

// Right-aligned Billing Summary
$pdf->Ln(10);
$pdf->SetFont('Arial', '', 11);

// Set starting X position (adjust 110–140 based on layout)
$pdf->SetX(120);
$pdf->Cell(50, 8, 'Initial Billing:', 0, 0, 'R');
$pdf->Cell(30, 8, 'PHP ' . number_format($total, 2), 0, 1, 'R');

$pdf->SetX(120);
$pdf->Cell(50, 8, 'Amount Deposit:', 0, 0, 'R');
$pdf->Cell(30, 8, 'PHP 0.00', 0, 1, 'R');

$pdf->SetX(120);
$pdf->Cell(50, 8, 'Total Amount Balance:', 0, 0, 'R');
$pdf->Cell(30, 8, 'PHP ' . number_format($total, 2), 0, 1, 'R');
// Prepared By
$pdf->Ln(5);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 8, 'Prepared By: ' . $staff, 0, 1, 'L');

// Output PDF
$pdf->Output('I', 'medical_bill_' . $record_id . '.pdf');
