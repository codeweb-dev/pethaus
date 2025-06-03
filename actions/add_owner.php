<?php
session_start();
include('../conn.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    // Required fields
    $requiredFields = [
        'first_name',
        'last_name',
        'address',
        'mobile_number'
    ];

    foreach ($requiredFields as $field) {
        if (empty(trim($_POST[$field]))) {
            $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . " is required.";
        }
    }

    // Trim and store fields
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $address = trim($_POST['address']);
    $mobile_number = trim($_POST['mobile_number']);
    $messenger_account = trim($_POST['messenger_account']);

    // Additional validations
    if (!empty($first_name) && !preg_match("/^[a-zA-Z\s]+$/", $first_name)) {
        $errors['first_name'] = "First name must contain only letters and spaces.";
    }

    if (!empty($middle_name) && !preg_match("/^[a-zA-Z\s]+$/", $middle_name)) {
        $errors['middle_name'] = "Middle name must contain only letters and spaces.";
    }

    if (!empty($last_name) && !preg_match("/^[a-zA-Z\s]+$/", $last_name)) {
        $errors['last_name'] = "Last name must contain only letters and spaces.";
    }

    if (!empty($mobile_number) && !preg_match("/^[0-9]+$/", $mobile_number)) {
        $errors['mobile_number'] = "Mobile number must contain only numbers.";
    }

    // If there are errors, store them and redirect
    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['old'] = $_POST;
        header("Location: ../admin/pet-owner-profiles.php?modal=add");
        exit();
    }

    // Optional fields set to NULL if empty
    $middle_name = $middle_name !== '' ? $middle_name : NULL;
    $messenger_account = $messenger_account !== '' ? $messenger_account : NULL;

    // Prepare and execute the INSERT query
    $stmt = $conn->prepare("INSERT INTO pet_owner_records (first_name, middle_name, last_name, address, mobile_number, messenger_account) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    $stmt->bind_param("ssssss", $first_name, $middle_name, $last_name, $address, $mobile_number, $messenger_account);

    if ($stmt->execute()) {
        $stmt->close();
        $_SESSION['success'] = "Successfully added new pet owner.";
        header("Location: ../admin/pet-owner-profiles.php?success=Successfully added new pet owner.");
        exit();
    } else {
        $stmt->close();
        $_SESSION['form_errors'] = ['general' => 'Failed to add new pet owner. Please try again.'];
        $_SESSION['old'] = $_POST;
        header("Location: ../admin/pet-owner-profiles.php?modal=add");
        exit();
    }
} else {
    header("Location: ../admin/pet-owner-profiles.php");
    exit();
}
