<?php
include('../conn.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pet_id']) && isset($_FILES['photo'])) {
    $pet_id = intval($_POST['pet_id']);
    $photo = $_FILES['photo'];

    if ($photo['error'] !== UPLOAD_ERR_OK) {
        header('Location: ../admin/pet-records.php?error=File upload failed.');
        exit();
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
    if (!in_array($photo['type'], $allowedTypes)) {
        header('Location: ../admin/pet-records.php?error=Invalid image type. Only JPG, PNG, and GIF are allowed.');
        exit();
    }

    if ($photo['size'] > 2 * 1024 * 1024) {
        header('Location: ../admin/pet-records.php?error=Image size should not exceed 2MB.');
        exit();
    }

    $upload_dir = '../uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $result = mysqli_query($conn, "SELECT photo FROM pet_records WHERE pet_id = $pet_id LIMIT 1");
    if ($result && mysqli_num_rows($result) > 0) {
        $pet = mysqli_fetch_assoc($result);

        if (!empty($pet['photo']) && file_exists($pet['photo'])) {
            unlink($pet['photo']);
        }

        $filename = basename($photo['name']);
        $newFileName = '../uploads/' . time() . '_' . $filename;
        $uploadPath = $newFileName;

        if (move_uploaded_file($photo['tmp_name'], $uploadPath)) {
            $update = "UPDATE pet_records SET photo = '$newFileName' WHERE pet_id = $pet_id";
            mysqli_query($conn, $update);

            header('Location: ../admin/pet-records.php?success=Photo updated successfully.');
        } else {
            header('Location: ../admin/pet-records.php?error=Failed to save uploaded file.');
        }
    } else {
        header('Location: ../admin/pet-records.php?error=Pet not found.');
    }

    exit();
} else {
    header('Location: ../admin/pet-records.php?error=Invalid request.');
    exit();
}
