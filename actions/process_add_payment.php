<?php
include('../conn.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $medical_record_id = intval($_POST['medical_record_id']);
    $payment_amount = floatval($_POST['payment_amount']);

    // Get total charges (BILL TOTAL)
    $charges_q = $conn->query("
        SELECT 
            treatment_charge, 
            prescription_charge, 
            others_charge 
        FROM medical_records 
        WHERE medical_record_id = $medical_record_id
    ");

    if (!$charges_q || $charges_q->num_rows === 0) {
        header('Location: ../admin/medical-bills.php?error=Medical record not found.');
        exit;
    }

    $charges = $charges_q->fetch_assoc();
    $bill_total = floatval($charges['treatment_charge']) + floatval($charges['prescription_charge']) + floatval($charges['others_charge']);

    // Get total payment made so far
    $bill_q = $conn->query("SELECT total_amount FROM medical_bill WHERE medical_record_id = $medical_record_id");
    $already_paid = 0;
    if ($bill_q && $bill_q->num_rows > 0) {
        $bill = $bill_q->fetch_assoc();
        $already_paid = floatval($bill['total_amount']);
    }

    // Final validation
    if ($payment_amount > $bill_total) {
        header('Location: ../admin/medical-bills.php?error=Overpayment not allowed. Balance is ₱' . number_format($already_paid, 2));
        exit;
    }

    // Update or insert payment
    if ($already_paid > 0) {
        $conn->query("UPDATE medical_bill SET total_amount = total_amount - $payment_amount WHERE medical_record_id = $medical_record_id");
    } else {
        $today = date('Y-m-d');
        $conn->query("INSERT INTO medical_bill (medical_record_id, owner_id, total_amount, status, billing_date)
                      VALUES ($medical_record_id, NULL, $payment_amount, 'Partial', '$today')");
    }

    $conn->query("INSERT INTO payment_history (medical_record_id, payment_amount) VALUES ($medical_record_id, $payment_amount)");

    header('Location: ../admin/medical-bills.php?success=Payment added successfully.');
    exit;
}
?>
