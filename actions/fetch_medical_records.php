<?php
include('../conn.php');
$pet_id = intval($_GET['pet_id'] ?? 0);

$query = "
    SELECT 
        medical_record_id AS id,
        type,
        date_started AS start_date,
        date_ended AS end_date,
        description,
        weight,
        temperature,
        complaint
    FROM medical_records
    WHERE pet_id = $pet_id
    ORDER BY date_started DESC
";

$result = $conn->query($query);
$records = [];

while ($row = $result->fetch_assoc()) {
    $row['start_date'] = date('F j, Y', strtotime($row['start_date']));
    $row['end_date'] = $row['end_date'] ? date('F j, Y', strtotime($row['end_date'])) : '-';
    $records[] = $row;
}

header('Content-Type: application/json');
echo json_encode($records);
