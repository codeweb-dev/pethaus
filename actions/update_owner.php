<?php
include('../conn.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $owner_id = $_POST['owner_id'];
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $address = $_POST['address'];
    $mobile_number = $_POST['mobile_number'];
    $messenger_account = $_POST['messenger_account'];

    $query = "UPDATE pet_owner_records SET
        first_name = '$first_name',
        middle_name = '$middle_name',
        last_name = '$last_name',
        address = '$address',
        mobile_number = '$mobile_number',
        messenger_account = '$messenger_account'
        WHERE owner_id = $owner_id";

    if (mysqli_query($conn, $query)) {
        header("Location: ../admin/pet-owner-profiles.php?success=Successfully updated owner");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
