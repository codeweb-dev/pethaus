<?php
include('../conn.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $address = trim($_POST['address']);
    $mobile_number = trim($_POST['mobile_number']);
    $messenger_account = trim($_POST['messenger_account']);

    $stmt = $conn->prepare("INSERT INTO pet_owner_records (first_name, middle_name, last_name, address, mobile_number, messenger_account) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $first_name, $middle_name, $last_name, $address, $mobile_number, $messenger_account);
    $stmt->execute();
    $stmt->close();

    header("Location: ../admin/pet-owner-profiles.php?success=Successfully added new owner.");
    exit();
}
