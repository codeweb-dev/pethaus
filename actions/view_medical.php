<?php
session_start(); // Make sure the user is logged in

require('../assets/fpdf/fpdf.php');
include('../conn.php');

$record_id = $_GET['id'] ?? 0;
if (!$record_id) {
    die("No medical record ID provided.");
}

// 1) Fetch main medical record + pet + owner
$sql = "
  SELECT 
    mr.*, 
    p.name AS pet_name, p.breed, p.species, p.color, p.sex, p.birthdate, p.markings,
    po.first_name, po.middle_name, po.last_name, po.mobile_number
  FROM medical_records mr
  LEFT JOIN pet_records p  ON mr.pet_id   = p.pet_id
  LEFT JOIN pet_owner_records po ON mr.owner_id = po.owner_id
  WHERE mr.medical_record_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $record_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
if (!$data) {
    die("Record not found.");
}

// 2) Fetch all treatments
$treatStmt = $conn->prepare("
  SELECT treatment_date, treatment_name, treatment_test, treatment_remarks, treatment_charge
  FROM medical_treatments
  WHERE medical_record_id = ?
  ORDER BY treatment_date
");
$treatStmt->bind_param("i", $record_id);
$treatStmt->execute();
$treatments = $treatStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// 3) Fetch all prescriptions
$rxStmt = $conn->prepare(" 
  SELECT prescription_date, prescription_name, prescription_description, prescription_sig, prescription_remarks, prescription_charge
  FROM medical_prescriptions
  WHERE medical_record_id = ? 
  ORDER BY prescription_date
");
$rxStmt->bind_param("i", $record_id);
$rxStmt->execute();
$prescriptions = $rxStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Format owner and staff
$owner = $data['first_name']
    . (!empty($data['middle_name']) ? ' ' . $data['middle_name'][0] . '.' : '')
    . ' ' . $data['last_name'];
$staff = ($_SESSION['first_name'] ?? '')
    . (!empty($_SESSION['middle_name']) ? ' ' . $_SESSION['middle_name'][0] . '.' : ' ')
    . ' ' . ($_SESSION['last_name'] ?? '');

// Compute total
$total = 0;
foreach ($treatments as $t) {
    $total += floatval($t['treatment_charge']);
}
foreach ($prescriptions as $r) {
    $total += floatval($r['prescription_charge']);
}
$total += floatval($data['others_charge'] ?? 0);

// --- Begin PDF ---
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 11);

// Clinic Header
$pdf->Image('../assets/images/logo.jpg', 10, 7, 25);
$pdf->SetXY(140, 10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 6, 'Pet Haus Animal Clinic & Supplies', 0, 1, 'R');
$pdf->SetFont('Arial', '', 10);
$pdf->SetX(140);
$pdf->Cell(0, 5, 'R.B. Castillo St. Mangagoy, Bislig City, Surigao del Sur', 0, 1, 'R');
$pdf->SetX(140);
$pdf->Cell(0, 5, 'Contact #: 0912-345-6789 / 0999-123-4567', 0, 1, 'R');
$pdf->SetX(140);
$pdf->Cell(0, 5, 'FB Page: Pet Haus Animal Clinic & Supplies', 0, 1, 'R');

$pdf->Ln(15);

// Pet & Owner Info
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(90, 6, 'Pet Name: ' . $data['pet_name'], 0);
$pdf->Cell(90, 6, 'Birthdate: ' . $data['birthdate'], 0, 1);
$pdf->Cell(90, 6, 'Breed: ' . $data['breed'], 0);
$pdf->Cell(90, 6, 'Species: ' . $data['species'], 0, 1);
$pdf->Cell(90, 6, 'Color: ' . $data['color'], 0);
$pdf->Cell(90, 6, 'Sex: ' . $data['sex'], 0, 1);
$pdf->Cell(90, 6, 'Markings: ' . $data['markings'], 0);
$pdf->Cell(90, 6, 'Contact Info: ' . $data['mobile_number'], 0, 1);
$pdf->Cell(90, 6, 'Pet Owner: ' . $owner, 0);
$pdf->Cell(90, 6, 'Date Billed: ' . $data['date_started'], 0, 1);
$pdf->Cell(90, 6, 'Staff: ' . $staff, 0);
$pdf->Cell(90, 6, 'Bill No.: #' . str_pad($data['medical_record_id'], 5, '0', STR_PAD_LEFT), 0, 1);

$pdf->Ln(5);

// Case Summary
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(50, 8, 'Type', 1);
$pdf->Cell(50, 8, 'Attending Vet', 1);
$pdf->Cell(45, 8, 'Start Date', 1);
$pdf->Cell(45, 8, 'End Date', 1, 1);
$pdf->SetFont('Arial', '', 10);
$typeText = $data['type'];
if (mb_strlen($typeText) > 20) {
    $typeText = mb_substr($typeText, 0, 20) . '...';
}
$pdf->Cell(50, 8, $typeText, 1);
$pdf->Cell(50, 8, $data['attending_vet'], 1);
$pdf->Cell(45, 8, $data['date_started'], 1);
$pdf->Cell(45, 8, $data['date_ended'], 1, 1);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(90, 8, 'Description', 1);
$pdf->Cell(50, 8, 'Weight', 1);
$pdf->Cell(50, 8, 'Temperature', 1, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(90, 8, $data['description'], 1);
$pdf->Cell(50, 8, $data['weight'], 1);
$pdf->Cell(50, 8, $data['temperature'] . ' °C', 1, 1);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(190, 8, 'Complaint', 1, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->MultiCell(190, 8, $data['complaint'], 1);
$pdf->Ln(5);

// --- Treatments Table (with individual total) ---
if (count($treatments) > 0) {
    // Header (total width = 190)
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(30, 8, 'Date', 1);
    $pdf->Cell(60, 8, 'Case', 1);
    $pdf->Cell(30, 8, 'Test', 1);
    $pdf->Cell(40, 8, 'Charge (PHP)', 1);
    $pdf->Cell(30, 8, 'Remarks', 1, 1);

    // Data rows
    $pdf->SetFont('Arial', '', 10);
    $treatment_total = 0; // Initialize total for treatments
    foreach ($treatments as $t) {
        $pdf->Cell(30, 6, $t['treatment_date'], 1);
        $pdf->Cell(60, 6, $t['treatment_name'], 1);
        $pdf->Cell(30, 6, $t['treatment_test'], 1);
        $pdf->Cell(40, 6, 'PHP ' . number_format($t['treatment_charge'], 2), 1);
        // MultiCell for Remarks to handle overflow
        $pdf->MultiCell(30, 6, $t['treatment_remarks'], 1);

        // Add to total treatment charge
        $treatment_total += floatval($t['treatment_charge']);
    }
    
    // Display the total for treatments
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(160, 8, 'Total Treatment Charges:', 1);
    $pdf->Cell(30, 8, 'PHP ' . number_format($treatment_total, 2), 1, 1);
    $pdf->Ln(5);
}

// --- Prescriptions Table (with individual total) ---
if (count($prescriptions) > 0) {
    // Header (total width = 190)
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(25, 8, 'Date', 1);
    $pdf->Cell(50, 8, 'Medication', 1);
    $pdf->Cell(20, 8, 'Dose', 1);
    $pdf->Cell(20, 8, 'Sig', 1);
    $pdf->Cell(30, 8, 'Charge (PHP)', 1);
    $pdf->Cell(45, 8, 'Remarks', 1, 1);

    // Data rows
    $pdf->SetFont('Arial', '', 10);
    $prescription_total = 0; // Initialize total for prescriptions
    foreach ($prescriptions as $r) {
        // Truncate Remarks if too long
        $remarks = $r['prescription_remarks'];
        if (mb_strlen($remarks) > 20) {
            $remarks = mb_substr($remarks, 0, 20) . '...';
        }

        // Date, Medication, Dose, Sig, Charge
        $pdf->Cell(25, 6, $r['prescription_date'], 1);
        $pdf->Cell(50, 6, $r['prescription_name'], 1);
        $pdf->Cell(20, 6, $r['prescription_description'], 1);
        $pdf->Cell(20, 6, $r['prescription_sig'], 1);
        $pdf->Cell(30, 6, 'PHP ' . number_format($r['prescription_charge'], 2), 1);

        // MultiCell for Remarks
        $pdf->MultiCell(45, 6, $remarks, 1);

        // Add to total prescription charge
        $prescription_total += floatval($r['prescription_charge']);
    }
    
    // Display the total for prescriptions
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(160, 8, 'Total Prescription Charges:', 1);
    $pdf->Cell(30, 8, 'PHP ' . number_format($prescription_total, 2), 1, 1);
    $pdf->Ln(5);
}

// --- Other Charges Table (with individual total) ---
if (!empty($data['others_name'])) {
    $pdf->SetFont('Arial', 'B', 10);

    // Adjust column widths for better alignment
    $pdf->Cell(35, 8, 'Date', 1);
    $pdf->Cell(60, 8, 'Name', 1);
    $pdf->Cell(30, 8, 'Quantity', 1);
    $pdf->Cell(40, 8, 'Charge', 1);
    $pdf->Cell(25, 8, 'Remarks', 1, 1);

    $pdf->SetFont('Arial', '', 10);
    $others_total = 0; // Initialize total for other charges
    $pdf->Cell(35, 8, $data['others_date'], 1);
    $pdf->Cell(60, 8, $data['others_name'], 1);
    $pdf->Cell(30, 8, $data['others_quantity'], 1);
    $pdf->Cell(40, 8, 'PHP ' . number_format($data['others_charge'], 2), 1);

    // MultiCell for remarks to handle overflow
    $pdf->MultiCell(25, 8, $data['others_remarks'], 1);

    // Add to total others charge
    $others_total += floatval($data['others_charge']);
    
    // Display the total for other charges
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(160, 8, 'Total Other Charges:', 1);
    $pdf->Cell(30, 8, 'PHP ' . number_format($others_total, 2), 1, 1);
    $pdf->Ln(5); // Adding space after the row
}

// --- Grand Total ---
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(160, 8, 'Grand Total:', 1);
$pdf->Cell(30, 8, 'PHP ' . number_format($total, 2), 1, 1);

$pdf->Ln(5);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'Prepared By: ' . $staff, 0, 1, 'L');

$pdf->Output('I', 'medical_record_' . $record_id . '.pdf');
