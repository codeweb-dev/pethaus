<?php
include('../conn.php');

$medical_record_id = intval($_GET['id'] ?? 0);

$results = [];
$query = $conn->query("
    SELECT payment_amount, payment_date
    FROM payment_history
    WHERE medical_record_id = $medical_record_id
    ORDER BY payment_date DESC
");

while ($row = $query->fetch_assoc()) {
    $results[] = [
        'payment_amount' => $row['payment_amount'],
        'payment_date' => date('F j, Y h:i A', strtotime($row['payment_date']))
    ];
}

header('Content-Type: application/json');
echo json_encode($results);
