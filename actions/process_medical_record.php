<?php
include('../conn.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Validate required fields
    if (
        empty($_POST['owner_id'])   ||
        empty($_POST['pet_id'])     ||
        empty($_POST['type'])       ||
        empty($_POST['date_started']) ||
        empty($_POST['date_ended']) ||
        empty($_POST['description']) ||
        empty($_POST['weight'])     ||
        empty($_POST['temperature'])||
        empty($_POST['complaint'])
    ) {
        die("Missing required fields.");
    }

    // 2) Sanitize main inputs
    $owner_id      = intval($_POST['owner_id']);
    $pet_id        = intval($_POST['pet_id']);
    $type          = $_POST['type'];
    $start         = $_POST['date_started'];
    $end           = $_POST['date_ended'];
    $desc          = $_POST['description'];
    $weight        = $_POST['weight'];
    $temp          = $_POST['temperature'];
    $complaint     = $_POST['complaint'];
    $attending_vet = $_POST['attending_vet'] ?? 'Unknown';

    // 3) Grab the "others" fields (they stay in medical_records)
    $other_date     = $_POST['others_date']     ?? null;
    $other_name     = $_POST['others_name']     ?? null;
    $other_qty      = $_POST['others_quantity'] ?? null;
    $other_remarks  = $_POST['others_remarks']  ?? null;
    $other_charge   = $_POST['others_charge']   ?? 0;

    // 4) Generate medical_record_code
    $code_result = $conn->query("SELECT MAX(medical_record_id) AS last_id FROM medical_records");
    $row         = $code_result->fetch_assoc();
    $next_id     = ($row['last_id'] ?? 0) + 1;
    $medical_record_code = 'MR' . str_pad($next_id, 5, '0', STR_PAD_LEFT);

    // 5) Insert main record
    $stmt = $conn->prepare("
        INSERT INTO medical_records (
            pet_id, owner_id, medical_record_code,
            type, date_started, date_ended,
            description, weight, temperature,
            complaint, attending_vet,
            others_date, others_name, others_quantity,
            others_remarks, others_charge
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "iissssssssssssds",
        $pet_id,
        $owner_id,
        $medical_record_code,
        $type,
        $start,
        $end,
        $desc,
        $weight,
        $temp,
        $complaint,
        $attending_vet,
        $other_date,
        $other_name,
        $other_qty,
        $other_remarks,
        $other_charge
    );

    if (! $stmt->execute()) {
        die("Error saving medical record: " . $stmt->error);
    }

    $medical_id = $conn->insert_id;

    // 6) Insert Treatments (if any)
    if (!empty($_POST['treatment_date'])) {
        $stmtT = $conn->prepare("
            INSERT INTO medical_treatments
              (medical_record_id, treatment_date, treatment_name, treatment_test, treatment_remarks, treatment_charge)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        foreach ($_POST['treatment_date'] as $i => $date) {
            $name    = $_POST['treatment_name'][$i]    ?? '';
            $test    = $_POST['treatment_tests'][$i]   ?? '';
            $remarks = $_POST['treatment_remarks'][$i] ?? '';
            $charge  = floatval($_POST['treatment_charge'][$i] ?? 0);

            // skip entries where both date and name are empty
            if (empty($date) && empty($name)) {
                continue;
            }

            $stmtT->bind_param("issssd", $medical_id, $date, $name, $test, $remarks, $charge);
            $stmtT->execute();
        }
    }

    // 7) Insert Prescriptions (if any)
    if (!empty($_POST['prescription_date'])) {
        $stmtP = $conn->prepare("
            INSERT INTO medical_prescriptions
              (medical_record_id, prescription_date, prescription_name, prescription_description, prescription_sig, prescription_remarks, prescription_charge)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        foreach ($_POST['prescription_date'] as $i => $date) {
            $name    = $_POST['prescription_name'][$i]        ?? '';
            $descRx  = $_POST['prescription_description'][$i] ?? '';
            $sig     = $_POST['prescription_sig'][$i]         ?? '';
            $remarks = $_POST['prescription_remarks'][$i]     ?? '';
            $charge  = floatval($_POST['prescription_charge'][$i] ?? 0);

            // skip entries where both date and name are empty
            if (empty($date) && empty($name)) {
                continue;
            }

            $stmtP->bind_param("isssssd", $medical_id, $date, $name, $descRx, $sig, $remarks, $charge);
            $stmtP->execute();
        }
    }

    // 8) (Optional) Create bill by summing all charges
    if (isset($_POST['create_bill'])) {
        $treat_total = array_sum(array_map('floatval', $_POST['treatment_charge'] ?? []));
        $rx_total    = array_sum(array_map('floatval', $_POST['prescription_charge'] ?? []));
        $other_total = floatval($other_charge);
        $total = $treat_total + $rx_total + $other_total;

        $stmt2 = $conn->prepare("
            INSERT INTO medical_bill
              (medical_record_id, owner_id, total_amount, status, billing_date)
            VALUES (?, ?, ?, 'Pending', NOW())
        ");
        $stmt2->bind_param("iid", $medical_id, $owner_id, $total);
        $stmt2->execute();
    }

    // 9) (Optional) Print bill immediately
    if (isset($_POST['print_bill'])) {
        header("Location: ../actions/view_bill.php?record_id=$medical_id");
        exit;
    } else {
        // Redirect to the medical records page
        header("Location: ../admin/medical-records.php?success=Successfully added medical record");
        exit;
    }
}
?>
