<?php
session_start();
include('../conn.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

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

    if ($_POST['species'] === 'Others' && empty(trim($_POST['other_species']))) {
        $errors['other_species'] = "Please specify the species.";
    }

    // Check if either captured_image or uploaded file is provided
    if (
        (empty($_POST['captured_image']) || !preg_match('/^data:image\/(png|jpeg|jpg);base64,/', $_POST['captured_image']))
        && (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK)
    ) {
        $errors['photo'] = "Photo is required.";
    }

    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['old'] = $_POST;
        header("Location: ../admin/pet-records.php?modal=add");
        exit();
    }

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

    // Handle captured image
    if (!empty($_POST['captured_image']) && preg_match('/^data:image\/(png|jpeg|jpg);base64,/', $_POST['captured_image'])) {
        $data_uri = $_POST['captured_image'];
        $encoded_image = explode(",", $data_uri)[1];
        $decoded_image = base64_decode($encoded_image);

        $image_name = 'captured_' . time() . '.jpg';
        $target_file = $upload_dir . $image_name;

        if (file_put_contents($target_file, $decoded_image)) {
            $image_path = $target_file;
        } else {
            $_SESSION['form_errors'] = ['photo' => 'Failed to save captured photo.'];
            $_SESSION['old'] = $_POST;
            header("Location: ../admin/pet-records.php?modal=add");
            exit();
        }
    } else {
        // Fallback to uploaded file
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
    }

    // Insert breed if needed
    if ($species === 'Dog' || $species === 'Cat') {
        $breedTable = $species === 'Dog' ? 'dogs' : 'cats';
        $checkQuery = "SELECT COUNT(*) FROM $breedTable WHERE name = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("s", $breed);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count == 0 && !empty($breed)) {
            $insertBreedQuery = "INSERT INTO $breedTable (name) VALUES (?)";
            $stmt = $conn->prepare($insertBreedQuery);
            $stmt->bind_param("s", $breed);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Insert pet record
    $stmt = $conn->prepare("INSERT INTO pet_records (owner_id, name, species, breed, color, sex, birthdate, photo, age, markings) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    $stmt->bind_param("isssssssss", $owner_id, $name, $species, $breed, $color, $sex, $birthdate, $image_path, $age, $markings);

    if ($stmt->execute()) {
        $inserted_id = $stmt->insert_id;
        $stmt->close();

        // Format the pet_code like 0001, 0002, etc.
        $pet_code = str_pad($inserted_id, 4, '0', STR_PAD_LEFT);

        // Update the pet_code in the record
        $update_stmt = $conn->prepare("UPDATE pet_records SET pet_code = ? WHERE pet_id = ?");
        $update_stmt->bind_param("si", $pet_code, $inserted_id);
        $update_stmt->execute();
        $update_stmt->close();

        $_SESSION['success'] = "Successfully added new pet.";
        header("Location: ../admin/pet-records.php?success=Successfully added new pet.");
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
