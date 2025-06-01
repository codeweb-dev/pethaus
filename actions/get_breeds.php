<?php
session_start();
include('../conn.php');
if (isset($_GET['species'])) {
    $species = $_GET['species'];

    $table = '';
    if ($species === 'Dog') {
        $table = 'dogs';
    } elseif ($species === 'Cat') {
        $table = 'cats';
    }

    if ($table) {
        $sql = "SELECT name FROM `$table` ORDER BY name ASC";
        $result = $conn->query($sql);
        $breeds = [];
        while ($row = $result->fetch_assoc()) {
            $breeds[] = $row['name'];
        }
        echo json_encode($breeds);
    } else {
        echo json_encode([]);
    }

    $conn->close();
}
