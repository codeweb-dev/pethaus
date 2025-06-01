<?php
session_start();
include('../conn.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    // Required fields
    $requiredFields = [
        'owner_id',
        'name',
        'species',
        'breed',
        'color',
        'sex',
        'birthdate',
        'age',
        'markings'
    ];

    foreach ($requiredFields as $field) {
        if (empty(trim($_POST[$field]))) {
            $errors[$field] = ucfirst($field) . " is required.";
        }
    }

    // If "Others", ensure 'other_species' is filled
    if ($_POST['species'] === 'Others' && empty(trim($_POST['other_species']))) {
        $errors['other_species'] = "Please specify the species.";
    }

    // Validate photo upload
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        $errors['photo'] = "Photo is required.";
    }

    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['old'] = $_POST;
        header("Location: ../admin/pet-records.php?modal=add");
        exit();
    }

    // If no errors, handle photo upload
    $owner_id = trim($_POST['owner_id']);
    $name = trim($_POST['name']);
    $species = $_POST['species'] === "Others" ? trim($_POST['other_species']) : trim($_POST['species']);
    $breed = trim($_POST['breed']);
    $color = trim($_POST['color']);
    $sex = trim($_POST['sex']);
    $birthdate = trim($_POST['birthdate']);
    $age = trim($_POST['age']);
    $markings = trim($_POST['markings']);

    $upload_dir = '../uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $filename = basename($_FILES['photo']['name']);
    $target_file = $upload_dir . time() . '_' . $filename;

    if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
        $image_path = $target_file;
    } else {
        $_SESSION['form_errors'] = ['photo' => 'Failed to upload photo.'];
        $_SESSION['old'] = $_POST;
        header("Location: ../admin/pet-records.php?modal=add");
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO pet_records (owner_id, name, species, breed, color, sex, birthdate, photo, age, markings) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    $stmt->bind_param("isssssssss", $owner_id, $name, $species, $breed, $color, $sex, $birthdate, $image_path, $age, $markings);

    if ($stmt->execute()) {
        $stmt->close();
        $_SESSION['success'] = "Successfully added new pet.";
        header("Location: ../admin/pet-records.php");
        exit();
    } else {
        $stmt->close();
        $_SESSION['form_errors'] = ['general' => 'Failed to add new pet.'];
        $_SESSION['old'] = $_POST;
        header("Location: ../admin/pet-records.php?modal=add");
        exit();
    }
} else {
    header("Location: ../admin/pet-records.php");
    exit();
}
