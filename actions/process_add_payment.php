<?php
include('../conn.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $medical_record_id = intval($_POST['medical_record_id']);
    $payment_amount = floatval($_POST['payment_amount']);

    // Get total charges (BILL TOTAL)
    $charges_q = $conn->query("
        SELECT 
            IFNULL(treatment_total, 0) AS treatment_charge,
            IFNULL(prescription_total, 0) AS prescription_charge,
            IFNULL(others_charge, 0) AS others_charge
        FROM medical_records mr
        LEFT JOIN (
            SELECT medical_record_id, SUM(treatment_charge) AS treatment_total
            FROM medical_treatments
            GROUP BY medical_record_id
        ) t ON mr.medical_record_id = t.medical_record_id
        LEFT JOIN (
            SELECT medical_record_id, SUM(prescription_charge) AS prescription_total
            FROM medical_prescriptions
            GROUP BY medical_record_id
        ) p ON mr.medical_record_id = p.medical_record_id
        WHERE mr.medical_record_id = $medical_record_id
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

    // Update or insert payment
    if ($already_paid > 0) {
        // Update the total_amount with the new payment
        $conn->query("UPDATE medical_bill SET total_amount = total_amount - $payment_amount WHERE medical_record_id = $medical_record_id");
    } else {
        // Insert a new payment entry if no previous payments exist
        $today = date('Y-m-d');
        $conn->query("INSERT INTO medical_bill (medical_record_id, owner_id, total_amount, status, billing_date)
                      VALUES ($medical_record_id, NULL, $payment_amount, 'Partial', '$today')");
    }

    // Insert payment history
    $conn->query("INSERT INTO payment_history (medical_record_id, payment_amount) VALUES ($medical_record_id, $payment_amount)");

    // Redirect with success
    header('Location: ../admin/medical-bills.php?success=Payment added successfully.');
    exit;
}
?>
