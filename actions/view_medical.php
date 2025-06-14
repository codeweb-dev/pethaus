<?php
session_start(); // Make sure the user is logged in

require('../assets/fpdf/fpdf.php');
include('../conn.php');

$record_id = $_GET['id'] ?? 0;

if (!$record_id) {
    die("No medical record ID provided.");
}

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
    die("Record not found.");
}

$owner = $data['first_name'] . ' ';
$owner .= $data['middle_name'] ? $data['middle_name'][0] . '. ' : '';
$owner .= $data['last_name'];

$staff = $_SESSION['first_name'] ?? '';
$staff .= isset($_SESSION['middle_name']) ? ' ' . $_SESSION['middle_name'][0] . '.' : '';
$staff .= ' ' . ($_SESSION['last_name'] ?? '');

$total = floatval($data['treatment_charge']) + floatval($data['prescription_charge']) + floatval($data['others_charge']);

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 11);

// Title
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Medical Record Document', 0, 1, 'C');

// Logo + Clinic Info
$pdf->Image('../assets/images/pethaus_logo.png', 10, 20, 25);
$pdf->SetXY(140, 20);
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

// Pet and Owner Info
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(40, 8, 'Pet Name:', 0);         $pdf->Cell(50, 8, $data['pet_name'], 0);
$pdf->Cell(40, 8, 'Birthdate:', 0);        $pdf->Cell(60, 8, $data['birthdate'], 0, 1);

$pdf->Cell(40, 8, 'Breed:', 0);            $pdf->Cell(50, 8, $data['breed'], 0);
$pdf->Cell(40, 8, 'Species:', 0);          $pdf->Cell(60, 8, $data['species'], 0, 1);

$pdf->Cell(40, 8, 'Color:', 0);            $pdf->Cell(50, 8, $data['color'], 0);
$pdf->Cell(40, 8, 'Sex:', 0);              $pdf->Cell(60, 8, $data['sex'], 0, 1);

$pdf->Cell(40, 8, 'Markings:', 0);         $pdf->Cell(50, 8, $data['markings'], 0);
$pdf->Cell(40, 8, 'Contact Info:', 0);     $pdf->Cell(60, 8, $data['mobile_number'], 0, 1);

$pdf->Cell(40, 8, 'Pet Owner:', 0);        $pdf->Cell(50, 8, $owner, 0);
$pdf->Cell(40, 8, 'Date Billed:', 0);      $pdf->Cell(60, 8, $data['date_started'], 0, 1);

$pdf->Cell(40, 8, 'Staff:', 0);            $pdf->Cell(50, 8, $staff, 0);
$pdf->Cell(40, 8, 'Bill No.:', 0);         $pdf->Cell(60, 8, '#' . str_pad($data['medical_record_id'], 5, '0', STR_PAD_LEFT), 0, 1);

$pdf->Ln(5);

// Summary
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(60, 8, 'Type:', 1); $pdf->Cell(60, 8, 'Start Date:', 1); $pdf->Cell(70, 8, 'End Date:', 1, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(60, 8, $data['type'], 1); $pdf->Cell(60, 8, $data['date_started'], 1); $pdf->Cell(70, 8, $data['date_ended'], 1, 1);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(60, 8, 'Description:', 1); $pdf->Cell(60, 8, 'Weight:', 1); $pdf->Cell(70, 8, 'Temperature:', 1, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(60, 8, $data['description'], 1); $pdf->Cell(60, 8, $data['weight'], 1); $pdf->Cell(70, 8, $data['temperature'] . ' °C', 1, 1);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(190, 8, 'Complaint:', 1, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->MultiCell(190, 8, $data['complaint'], 1);

$pdf->Ln(5);

// Table Header
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(20, 8, 'Type', 1);
$pdf->Cell(25, 8, 'Date', 1);
$pdf->Cell(50, 8, 'Name', 1);
$pdf->Cell(35, 8, 'Desc/Qty', 1);
$pdf->Cell(30, 8, 'Remarks', 1);
$pdf->Cell(30, 8, 'Charge', 1, 1);

// Custom row handler
function renderMultiRow($pdf, $row) {
    $lineHeight = 5;

    $w = [20, 25, 50, 35, 30, 30]; // Column widths
    $h = [];

    $colData = [
        $row['type'],
        $row['date'],
        $row['name'],
        $row['desc'],
        $row['remarks'],
        $row['charge']
    ];

    // Estimate max number of lines
    for ($i = 0; $i < count($colData); $i++) {
        $nb = $pdf->GetStringWidth($colData[$i]) / ($w[$i] - 2);
        $h[$i] = ceil($nb) * $lineHeight;
    }

    $maxH = max($h);
    $x = $pdf->GetX();
    $y = $pdf->GetY();

    for ($i = 0; $i < count($colData); $i++) {
        $pdf->SetXY($x, $y);
        $pdf->MultiCell($w[$i], $lineHeight, $colData[$i], 1);
        $x += $w[$i];
        $pdf->SetY($y); // Reset Y to top of row
    }
    $pdf->Ln($maxH);
}

$pdf->SetFont('Arial', '', 10);
if ($data['treatment_name']) {
    renderMultiRow($pdf, [
        'type' => 'Tx',
        'date' => $data['treatment_date'],
        'name' => $data['treatment_name'],
        'desc' => $data['treatment_test'],
        'remarks' => $data['treatment_remarks'],
        'charge' => 'PHP ' . number_format($data['treatment_charge'], 2)
    ]);
}

if ($data['prescription_name']) {
    renderMultiRow($pdf, [
        'type' => 'Rx',
        'date' => $data['prescription_date'],
        'name' => $data['prescription_name'],
        'desc' => $data['prescription_description'],
        'remarks' => $data['prescription_remarks'],
        'charge' => 'PHP ' . number_format($data['prescription_charge'], 2)
    ]);
}

if ($data['others_name']) {
    renderMultiRow($pdf, [
        'type' => 'Other',
        'date' => $data['others_date'],
        'name' => $data['others_name'],
        'desc' => $data['others_quantity'],
        'remarks' => $data['others_remarks'],
        'charge' => 'PHP ' . number_format($data['others_charge'], 2)
    ]);
}

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(160, 8, 'TOTAL:', 1);
$pdf->Cell(30, 8, 'PHP ' . number_format($total, 2), 1, 1);

$pdf->Output('I', 'medical_record_' . $record_id . '.pdf');
?>
