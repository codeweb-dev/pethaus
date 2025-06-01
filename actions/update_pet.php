<?php
include('../conn.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pet_id = $_POST['pet_id'];
    $name = $_POST['name'];
    $species = $_POST['species'];
    $breed = $_POST['breed'];
    $color = $_POST['color'];
    $sex = $_POST['sex'];
    $birthdate = $_POST['birthdate'];
    $age = $_POST['age'];
    $markings = $_POST['markings'];

    $query = "UPDATE pet_records SET
        name = '$name',
        species = '$species',
        breed = '$breed',
        color = '$color',
        sex = '$sex',
        birthdate = '$birthdate',
        age = '$age',
        markings = '$markings'
        WHERE pet_id = $pet_id";

    if (mysqli_query($conn, $query)) {
        header("Location: ../admin/pet-records.php?success=Successfully updated pet");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
