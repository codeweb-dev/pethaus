<?php
include('../conn.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pet_id'])) {
    $pet_id = intval($_POST['pet_id']);

    $query = "SELECT photo FROM pet_records WHERE pet_id = $pet_id LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $pet = mysqli_fetch_assoc($result);
        $photoPath = '../' . $pet['photo'];

        if (!empty($pet['photo']) && file_exists($photoPath)) {
            unlink($photoPath);
        }

        $update = "UPDATE pet_records SET photo = NULL WHERE pet_id = $pet_id";
        mysqli_query($conn, $update);

        header('Location: ../admin/pet-records.php?success=Successfully delete photo.');
        exit();
    } else {
        header('Location: ../admin/pet-records.php?error=Pet not found.');
        exit();
    }
} else {
    header('Location: ../admin/pet-records.php?error=Invalid request.');
    exit();
}
