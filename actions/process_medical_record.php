<?php
include('../conn.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    if (
        empty($_POST['owner_id']) || empty($_POST['pet_id']) ||
        empty($_POST['type']) || empty($_POST['date_started']) ||
        empty($_POST['date_ended']) || empty($_POST['description']) ||
        empty($_POST['weight']) || empty($_POST['temperature']) || empty($_POST['complaint'])
    ) {
        die("Missing required fields.");
    }

    // Sanitize input
    $owner_id = intval($_POST['owner_id']);
    $pet_id = intval($_POST['pet_id']);
    $type = $_POST['type'];
    $start = $_POST['date_started'];
    $end = $_POST['date_ended'];
    $desc = $_POST['description'];
    $weight = $_POST['weight'];
    $temp = $_POST['temperature'];
    $complaint = $_POST['complaint'];

    // Optional fields
    $treat_date = $_POST['treatment_date'] ?? null;
    $treat_name = $_POST['treatment_name'] ?? null;
    $treat_test = $_POST['treatment_test'] ?? null;
    $treat_remarks = $_POST['treatment_remarks'] ?? null;
    $treat_charge = $_POST['treatment_charge'] ?? null;

    $rx_date = $_POST['prescription_date'] ?? null;
    $rx_name = $_POST['prescription_name'] ?? null;
    $rx_desc = $_POST['prescription_description'] ?? null;
    $rx_remarks = $_POST['prescription_remarks'] ?? null;
    $rx_charge = $_POST['prescription_charge'] ?? null;

    $other_date = $_POST['others_date'] ?? null;
    $other_name = $_POST['others_name'] ?? null;
    $other_qty = $_POST['others_quantity'] ?? null;
    $other_remarks = $_POST['others_remarks'] ?? null;
    $other_charge = $_POST['others_charge'] ?? null;

    $owner_id = intval($_POST['owner_id'] ?? 0);

    $stmt = $conn->prepare("
        INSERT INTO medical_records (
            pet_id, owner_id, type, date_started, date_ended,
            description, weight, temperature, complaint,
            treatment_date, treatment_name, treatment_test, treatment_remarks, treatment_charge,
            prescription_date, prescription_name, prescription_description, prescription_remarks, prescription_charge,
            others_date, others_name, others_quantity, others_remarks, others_charge
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "iisssssssssssdsssssdsssd",
        $pet_id,
        $owner_id,
        $type,
        $start,
        $end,
        $desc,
        $weight,
        $temp,
        $complaint,
        $treat_date,
        $treat_name,
        $treat_test,
        $treat_remarks,
        $treat_charge,
        $rx_date,
        $rx_name,
        $rx_desc,
        $rx_remarks,
        $rx_charge,
        $other_date,
        $other_name,
        $other_qty,
        $other_remarks,
        $other_charge
    );

    if ($stmt->execute()) {
        $medical_id = $conn->insert_id;

        if (isset($_POST['create_bill'])) {
            $total = floatval($treat_charge) + floatval($rx_charge) + floatval($other_charge);

            $stmt2 = $conn->prepare("
            INSERT INTO medical_bill (medical_record_id, owner_id, total_amount, status, billing_date)
            VALUES (?, ?, ?, 'Pending', NOW())
        ");
            $stmt2->bind_param("iid", $medical_id, $owner_id, $total);
            $stmt2->execute();
        }

        if (isset($_POST['print_bill'])) {
            header("Location: ../actions/view_bill.php?record_id=$medical_id");
            exit;
        }

        header("Location: ../admin/medical-records.php?success=Successfully added medical record");
        exit;
    } else {
        echo "Error saving medical record.";
    }
}
