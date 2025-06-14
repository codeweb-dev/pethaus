<?php
header('Content-Type: application/json');

include('../conn.php');

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

if (isset($_GET['owner_id'])) {
    $ownerId = intval($_GET['owner_id']);

    $stmt = $conn->prepare("SELECT pet_id, name FROM pet_records WHERE owner_id = ?");
    if (!$stmt) {
        echo json_encode(['error' => 'Failed to prepare statement']);
        exit;
    }

    $stmt->bind_param("i", $ownerId);
    $stmt->execute();
    $result = $stmt->get_result();

    $pets = [];
    while ($row = $result->fetch_assoc()) {
        $pets[] = $row;
    }

    echo json_encode($pets);
} else {
    echo json_encode(['error' => 'Missing owner_id']);
}
?>
