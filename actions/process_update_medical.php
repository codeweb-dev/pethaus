<?php
session_start();
include('../conn.php');

// Check if 'id' parameter is passed
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: medical-records.php');
    exit;
}

$medical_record_id = $_GET['id'];

// Ensure the data is coming from the form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $attending_vet = mysqli_real_escape_string($conn, $_POST['attending_vet']);
    $date_started = $_POST['date_started'];
    $date_ended = $_POST['date_ended'];
    $weight = mysqli_real_escape_string($conn, $_POST['weight']);
    $temperature = mysqli_real_escape_string($conn, $_POST['temperature']);
    $complaint = mysqli_real_escape_string($conn, $_POST['complaint']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    // Update query
    $updateQuery = "
        UPDATE medical_records 
        SET 
            type = '$type',
            attending_vet = '$attending_vet',
            date_started = '$date_started',
            date_ended = '$date_ended',
            weight = '$weight',
            temperature = '$temperature',
            complaint = '$complaint',
            description = '$description'
        WHERE medical_record_id = $medical_record_id
    ";

    // Execute the query
    if (mysqli_query($conn, $updateQuery)) {
        header('Location: ../admin/medical-records.php?success=Successfully updated medical record');  // Redirect back to the medical records list
        exit;
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }
}
?>
